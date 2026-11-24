<?php
// FILE: app/Http/Middleware/LogStudentActivity.php

namespace App\Http\Middleware;

use App\Models\StudentActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogStudentActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = $request->user();

        if (!$user || $user->role !== 'student') {
            return $response;
        }

        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $path = '/' . ltrim($request->path(), '/');

            if (str_contains($path, 'badges/mark-read') || str_contains($path, 'logout')) {
                return $response;
            }

            [$category, $action, $description] = $this->describeRequest($request->method(), $path);

            StudentActivityLog::record($user, $action, $category, $description, [
                'method' => $request->method(),
                'path'   => $path,
                'status' => $response->getStatusCode(),
            ]);
        }

        return $response;
    }

    protected function describeRequest(string $method, string $path): array
    {
        if (str_contains($path, 'quiz')) {
            return ['quiz', 'Quiz Action', "Submitted quiz interaction on {$path}"];
        }

        if (str_contains($path, 'class') || str_contains($path, 'join')) {
            return ['classroom', 'Classroom Action', "Updated classroom enrollment on {$path}"];
        }

        if (str_contains($path, 'profile') || str_contains($path, 'settings') || str_contains($path, 'password')) {
            return ['profile', 'Profile Updated', "Updated account or profile settings ({$method} {$path})"];
        }

        if (str_contains($path, 'assignment')) {
            return ['assignment', 'Assignment Action', "Submitted assignment activity on {$path}"];
        }

        return ['activity', 'Student Action', "Performed {$method} action on {$path}"];
    }
}