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
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => $data['role'],
                'is_admin' => in_array($data['role'], ['super_admin', 'admin'], true),
            ]);

            $this->syncRole($user, $data['role']);
            return $user->load('roles');
        });
    }

    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $payload = [
                'name' => $data['name'] ?? $user->name,
                'email' => $data['email'] ?? $user->email,
                'role' => $data['role'] ?? $user->role,
            ];

            if (!empty($data['password'])) {
                $payload['password'] = $data['password'];
            }

            $payload['is_admin'] = in_array($payload['role'], ['super_admin', 'admin'], true);

            $user->update($payload);
            $this->syncRole($user, $payload['role']);

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
}
