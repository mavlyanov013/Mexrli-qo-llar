<?php

namespace App\Services\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = User::query()->with('roles')->latest();

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        return $query->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $role = $this->normalizeRole($data['role'] ?? 'editor');

            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'role' => $role,
                'is_admin' => $role === 'super_admin',
            ]);

            $this->syncRole($user, $role);
            return $user->load('roles');
        });
    }

    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $role = $this->normalizeRole($data['role'] ?? $user->role);

            $payload = [
                'name' => $data['name'] ?? $user->name,
                'email' => $data['email'] ?? $user->email,
                'role' => $role,
            ];

            if (!empty($data['password'])) {
                $payload['password'] = bcrypt($data['password']);
            }

            $payload['is_admin'] = $role === 'super_admin';

            $user->update($payload);
            $this->syncRole($user, $role);

            return $user->fresh()->load('roles');
        });
    }

    public function delete(User $user): void
    {
        DB::transaction(function () use ($user) {
            $user->roles()->detach();
            $user->delete();
        });
    }

    private function syncRole(User $user, string $roleName): void
    {
        $role = Role::query()->where('name', $roleName)->first();
        if ($role) {
            $user->roles()->sync([$role->id]);
        }
    }

    private function normalizeRole(string $role): string
    {
        return $role === 'admin' ? 'super_admin' : $role;
    }
}
