<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    private const ROLE_CAPABILITIES = [
        'super_admin' => ['*'],
        'editor' => [
            'cases.view', 'cases.create', 'cases.update', 'cases.delete',
            'help_requests.view', 'help_requests.update', 'help_requests.create',
            'volunteers.view', 'volunteers.update',
            'messages.view', 'messages.update',
            'blog.view', 'blog.create', 'blog.update', 'blog.delete',
            'news.view', 'news.create', 'news.update', 'news.delete',
            'faq.view', 'faq.create', 'faq.update', 'faq.delete',
            'treatment_processes.view', 'treatment_processes.create', 'treatment_processes.update', 'treatment_processes.delete',
            'sections.view', 'sections.create', 'sections.update', 'sections.delete',
            'pages.view', 'pages.update',
            'partners.view', 'partners.create', 'partners.update', 'partners.delete',
            'media.create', 'media.delete',
        ],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $capability = $this->resolveCapability($request);

        if ($user->hasRole('super_admin')) {
            return $next($request);
        }

        foreach ($this->resolveUserRoles($user) as $role) {
            if ($this->hasCapability($role, $capability)) {
                return $next($request);
            }
        }

        return response()->json([
            'message' => 'Forbidden',
        ], 403);
    }

    private function resolveUserRoles($user): array
    {
        $roles = $user->roles->pluck('name')->filter()->map(function ($role) {
            return $this->normalizeRoleName((string) $role);
        })->unique()->values()->all();

        if ($user->role) {
            $roles[] = $this->normalizeRoleName($user->role);
        }

        if ($user->is_admin) {
            $roles[] = 'super_admin';
        }

        return array_values(array_unique($roles));
    }

    private function normalizeRoleName(string $role): string
    {
        return $role === 'admin' ? 'super_admin' : $role;
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
            'news' => 'news',
            'faq' => 'faq',
            'treatment-processes' => 'treatment_processes',
            'users' => 'users',
            'pages' => 'pages',
            'about' => 'sections',
            'about-sections' => 'sections',
            'contact-info' => 'sections',
            'sections' => 'sections',
            'reports' => 'reports',
            'settings' => 'settings',
            'partners' => 'partners',
            'media' => 'media',
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
