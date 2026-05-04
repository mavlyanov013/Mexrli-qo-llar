<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    private const ROLE_CAPABILITIES = [
        'super_admin' => ['*'],
        'admin' => [
            'users.view', 'users.create', 'users.update', 'users.delete',
            'cases.view', 'cases.create', 'cases.update', 'cases.delete',
            'help_requests.view', 'help_requests.update',
            'volunteers.view', 'volunteers.update',
            'messages.view', 'messages.update', 'messages.delete',
            'blog.view', 'blog.create', 'blog.update', 'blog.delete',
            'donations.view', 'donations.update', 'donations.delete',
            'payments.view',
            'pages.view', 'pages.create', 'pages.update', 'pages.delete',
            'sections.create', 'sections.update', 'sections.delete',
            'reports.view', 'reports.create', 'reports.update', 'reports.delete',
            'settings.view', 'settings.update',
        ],
        'editor' => [
            'cases.view', 'cases.create', 'cases.update', 'cases.delete',
            'help_requests.view', 'help_requests.update',
            'volunteers.view', 'volunteers.update',
            'messages.view', 'messages.update',
            'blog.view', 'blog.create', 'blog.update', 'blog.delete',
            'donations.view',
        ],
        'finance' => [
            'payments.view',
            'donations.view',
            'users.view',
            'reports.view',
        ],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('api');

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $role = $user->roles()->value('name') ?: $user->role;
        if (!$role && $user->is_admin) {
            $role = 'super_admin';
        }

        if (!$role) {
            return response()->json([
                'message' => 'Forbidden',
            ], 403);
        }

        $capability = $this->resolveCapability($request);
        if (!$this->hasCapability($role, $capability)) {
            return response()->json([
                'message' => 'Forbidden',
            ], 403);
        }

        return $next($request);
    }

    private function resolveCapability(Request $request): string
    {
        $path = trim($request->path(), '/');
        $adminPath = str_replace('api/v1/admin/', '', $path);
        $module = explode('/', $adminPath)[0] ?? 'dashboard';
        $method = strtoupper($request->method());

        $action = match ($method) {
            'GET', 'HEAD' => 'view',
            'POST' => 'create',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'delete',
            default => 'view',
        };

        $moduleMap = [
            'cases' => 'cases',
            'donations' => 'donations',
            'payments' => 'payments',
            'help-requests' => 'help_requests',
            'volunteer-applications' => 'volunteers',
            'contact-messages' => 'messages',
            'blog-posts' => 'blog',
            'users' => 'users',
            'pages' => 'pages',
            'sections' => 'sections',
            'reports' => 'reports',
            'settings' => 'settings',
        ];

        $moduleKey = $moduleMap[$module] ?? 'dashboard';
        return "{$moduleKey}.{$action}";
    }

    private function hasCapability(string $role, string $capability): bool
    {
        $caps = self::ROLE_CAPABILITIES[$role] ?? [];
        return in_array('*', $caps, true) || in_array($capability, $caps, true);
    }
}
