<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\BadgeNotification;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\StudentBadge;
use Illuminate\Support\Facades\Cache;

class BadgeService
{
    public function evaluateQuizBadges(Quiz $quiz, int $currentStudentId): array
    {
        $attempts = QuizAttempt::where('quiz_id', $quiz->id)->get();
        if ($attempts->isEmpty()) {
            return [];
        }

        $allBadges = Cache::get('all_badges_collection');
        if (!$allBadges || $allBadges->isEmpty()) {
            $allBadges = Badge::all();
            if ($allBadges->isNotEmpty()) {
                Cache::put('all_badges_collection', $allBadges, now()->addHours(12));
            }
        }

        $badgesByKey = $allBadges->keyBy(fn ($b) => "{$b->criteria_type}:{$b->criteria_value}");
        $badgesById  = $allBadges->keyBy('id');

        $bestByStudent = $attempts
            ->groupBy('student_id')
            ->map(fn ($group) => $group->sortBy([
                ['score', 'desc'],
                ['time_taken_seconds', 'asc'],
            ])->first())
            ->values();

        $validAttempts = $bestByStudent->filter(fn ($a) => $a->score > 0)->values();
        if ($validAttempts->isEmpty()) {
            return [];
        }

        $eligibleByBadgeId = [];

        // 1. Podium Ranks (1st, 2nd, 3rd Place)
        $distinctScores = $validAttempts->pluck('score')->unique()->sortDesc()->values();

        foreach ([1, 2, 3] as $rank) {
            $badge = $badgesByKey->get("quiz_rank:{$rank}");
            if (!$badge) {
                continue;
            }

            $scoreIndex = $rank - 1;
            if (isset($distinctScores[$scoreIndex])) {
                $targetScore = $distinctScores[$scoreIndex];
                $eligibleByBadgeId[$badge->id] = $validAttempts
                    ->where('score', $targetScore)
                    ->pluck('student_id')
                    ->map(fn ($id) => (int) $id)
                    ->all();
            } else {
                $eligibleByBadgeId[$badge->id] = [];
            }
        }

        // 2. Fastest Answerer per quiz
        $fastestBadge = $badgesByKey->get('fastest_time:1');
        if ($fastestBadge) {
            $timedAttempts = $validAttempts->filter(fn ($a) => ($a->time_taken_seconds ?? 0) > 0);
            if ($timedAttempts->isNotEmpty()) {
                $fastestTime = $timedAttempts->min('time_taken_seconds');
                $eligibleByBadgeId[$fastestBadge->id] = $timedAttempts
                    ->where('time_taken_seconds', $fastestTime)
                    ->pluck('student_id')
                    ->map(fn ($id) => (int) $id)
                    ->all();
            } else {
                $eligibleByBadgeId[$fastestBadge->id] = [];
            }
        }

        $alertsForCurrentStudent = [];

        foreach ($eligibleByBadgeId as $badgeId => $winnerIds) {
            $badgeModel = $badgesById->get($badgeId);
            if (!$badgeModel) {
                continue;
            }

            $overtakenRecords = StudentBadge::where('quiz_id', $quiz->id)
                ->where('badge_id', $badgeId)
                ->whereNotIn('student_id', $winnerIds)
                ->get();

            foreach ($overtakenRecords as $lostRecord) {
                $message = "Oh no! Your \"{$badgeModel->name}\" badge for \"{$quiz->title}\" was taken away!";

                $notif = BadgeNotification::create([
                    'student_id' => $lostRecord->student_id,
                    'badge_id'   => $badgeId,
                    'quiz_id'    => $quiz->id,
                    'type'       => 'revoked',
                    'message'    => $message,
                    'is_read'    => ($lostRecord->student_id === $currentStudentId),
                ]);

                if ($lostRecord->student_id === $currentStudentId) {
                    $alertsForCurrentStudent[] = [
                        'id'          => $notif->id,
                        'type'        => 'revoked',
                        'name'        => $badgeModel->name,
                        'message'     => $message,
                        'description' => $badgeModel->description,
                        'image'       => $badgeModel->image_url,
                        'quiz'        => $quiz->title,
                    ];
                }

                $lostRecord->delete();
            }

            foreach ($winnerIds as $winnerId) {
                $award = StudentBadge::firstOrCreate(
                    [
                        'student_id' => $winnerId,
                        'badge_id'   => $badgeId,
                        'quiz_id'    => $quiz->id,
                    ],
                    [
                        'class_id'  => $quiz->class_id,
                        'earned_at' => now(),
                        'is_read'   => false,
                    ]
                );

                if ($award->wasRecentlyCreated) {
                    $message = "You earned the \"{$badgeModel->name}\" badge on \"{$quiz->title}\"!";

                    $notif = BadgeNotification::create([
                        'student_id' => $winnerId,
                        'badge_id'   => $badgeId,
                        'quiz_id'    => $quiz->id,
                        'type'       => 'earned',
                        'message'    => $message,
                        'is_read'    => ($winnerId === $currentStudentId),
                    ]);

                    if ($winnerId === $currentStudentId) {
                        $alertsForCurrentStudent[] = [
                            'id'          => $notif->id,
                            'type'        => 'earned',
                            'name'        => $badgeModel->name,
                            'message'     => $message,
                            'description' => $badgeModel->description,
                            'image'       => $badgeModel->image_url,
                            'quiz'        => $quiz->title,
                        ];
                    }
                }
            }
        }

        return $alertsForCurrentStudent;
    }
}