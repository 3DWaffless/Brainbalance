<?php
// FILE: app/Models/Question.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'topic_id',
        'type',
        'question_type',
        'question_text',
        'question',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'correct_answer',
        'options',
        'correct_answers',
        'matching_pairs',
        'difficulty_level',
        'time_limit_seconds',
        'points',
        'explanation',
        'position',
    ];

    protected $casts = [
        'options' => 'array',
        'correct_answers' => 'array',
        'matching_pairs' => 'array',
        'points' => 'integer',
        'time_limit_seconds' => 'integer',
        'position' => 'integer',
    ];

    public const TYPES = [
        'multiple_choice' => 'Multiple Choice',
        'multiple_select' => 'Multiple Select',
        'true_false'      => 'True / False',
        'matching'        => 'Matching',
        'fill_blank'      => 'Fill-in-the-Blank',
        'short_answer'    => 'Short Answer',
        'essay'           => 'Essay',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function getPromptTextAttribute(): string
    {
        return (string) ($this->question_text ?? $this->question ?? '');
    }

    public function getResolvedTypeAttribute(): string
    {
        return $this->question_type ?: ($this->type ?: 'multiple_choice');
    }

    public function getResolvedOptionsAttribute(): array
    {
        if (is_array($this->options) && count($this->options) > 0) {
            return array_values($this->options);
        }

        if ($this->resolved_type === 'true_false') {
            return ['True', 'False'];
        }

        return array_values(array_filter([
            $this->option_a,
            $this->option_b,
            $this->option_c,
            $this->option_d,
        ], fn ($v) => $v !== null && $v !== ''));
    }

    public function getResolvedCorrectAnswersAttribute(): array
    {
        if (is_array($this->correct_answers) && count($this->correct_answers) > 0) {
            return array_values($this->correct_answers);
        }

        if ($this->correct_answer !== null && $this->correct_answer !== '') {
            $map = ['A' => 0, 'B' => 1, 'C' => 2, 'D' => 3];
            $upper = strtoupper(trim((string) $this->correct_answer));
            if (isset($map[$upper])) {
                return [$map[$upper]];
            }
            return [trim((string) $this->correct_answer)];
        }

        return [];
    }

    /**
     * Grade a student's response across all 7 question types.
     *
     * @param  mixed  $response
     * @return array{is_correct: bool|null, earned_points: float|int, max_points: int, needs_review: bool}
     */
    public function gradeResponse($response): array
    {
        $maxPoints = max(1, (int) ($this->points ?: 1));
        $type = $this->resolved_type;
        $correct = $this->resolved_correct_answers;

        switch ($type) {
            case 'multiple_choice':
            case 'true_false': {
                $expected = $correct[0] ?? null;
                if ($response === null || $response === '' || $expected === null) {
                    return ['is_correct' => false, 'earned_points' => 0, 'max_points' => $maxPoints, 'needs_review' => false];
                }
                $isCorrect = (string) $response === (string) $expected
                    || mb_strtolower(trim((string) $response)) === mb_strtolower(trim((string) $expected));

                return [
                    'is_correct'    => $isCorrect,
                    'earned_points' => $isCorrect ? $maxPoints : 0,
                    'max_points'    => $maxPoints,
                    'needs_review'  => false,
                ];
            }

            case 'multiple_select': {
                $selected = is_array($response) ? array_map('strval', $response) : [];
                $expected = array_map('strval', $correct);
                sort($selected);
                sort($expected);
                $selected = array_values(array_unique($selected));
                $expected = array_values(array_unique($expected));

                $isCorrect = count($expected) > 0 && $selected === $expected;
                return [
                    'is_correct'    => $isCorrect,
                    'earned_points' => $isCorrect ? $maxPoints : 0,
                    'max_points'    => $maxPoints,
                    'needs_review'  => false,
                ];
            }

            case 'matching': {
                $pairs = is_array($this->matching_pairs) ? $this->matching_pairs : [];
                $totalPairs = count($pairs);
                if ($totalPairs === 0 || !is_array($response)) {
                    return ['is_correct' => false, 'earned_points' => 0, 'max_points' => $maxPoints, 'needs_review' => false];
                }

                $matchedCount = 0;
                foreach ($pairs as $idx => $pair) {
                    $expectedRight = mb_strtolower(trim((string) ($pair['right'] ?? '')));
                    $givenRight    = mb_strtolower(trim((string) ($response[$idx] ?? '')));
                    if ($expectedRight !== '' && $expectedRight === $givenRight) {
                        $matchedCount++;
                    }
                }

                $isCorrect = ($matchedCount === $totalPairs);
                $earned = round(($matchedCount / $totalPairs) * $maxPoints, 2);

                return [
                    'is_correct'    => $isCorrect,
                    'earned_points' => $earned,
                    'max_points'    => $maxPoints,
                    'needs_review'  => false,
                ];
            }

            case 'fill_blank': {
                $givenBlanks = is_array($response) ? array_values($response) : [$response];
                if (count($correct) === 0) {
                    return ['is_correct' => false, 'earned_points' => 0, 'max_points' => $maxPoints, 'needs_review' => false];
                }

                $correctCount = 0;
                foreach ($correct as $idx => $acceptedRaw) {
                    $studentVal = mb_strtolower(trim((string) ($givenBlanks[$idx] ?? '')));
                    $acceptedVariants = array_map(
                        fn ($v) => mb_strtolower(trim($v)),
                        explode('|', (string) $acceptedRaw)
                    );
                    if ($studentVal !== '' && in_array($studentVal, $acceptedVariants, true)) {
                        $correctCount++;
                    }
                }

                $isCorrect = ($correctCount === count($correct));
                $earned = round(($correctCount / count($correct)) * $maxPoints, 2);

                return [
                    'is_correct'    => $isCorrect,
                    'earned_points' => $earned,
                    'max_points'    => $maxPoints,
                    'needs_review'  => false,
                ];
            }

            case 'short_answer': {
                $studentText = mb_strtolower(trim((string) $response));
                if (count($correct) === 0) {
                    $hasContent = $studentText !== '';
                    return [
                        'is_correct'    => null,
                        'earned_points' => $hasContent ? $maxPoints : 0,
                        'max_points'    => $maxPoints,
                        'needs_review'  => true,
                    ];
                }

                $acceptedList = array_map(fn ($v) => mb_strtolower(trim((string) $v)), $correct);
                $isCorrect = $studentText !== '' && in_array($studentText, $acceptedList, true);

                return [
                    'is_correct'    => $isCorrect,
                    'earned_points' => $isCorrect ? $maxPoints : 0,
                    'max_points'    => $maxPoints,
                    'needs_review'  => false,
                ];
            }

            case 'essay': {
                $hasContent = trim((string) $response) !== '';
                return [
                    'is_correct'    => null,
                    'earned_points' => $hasContent ? $maxPoints : 0,
                    'max_points'    => $maxPoints,
                    'needs_review'  => true,
                ];
            }
        }

        return ['is_correct' => false, 'earned_points' => 0, 'max_points' => $maxPoints, 'needs_review' => false];
    }
}