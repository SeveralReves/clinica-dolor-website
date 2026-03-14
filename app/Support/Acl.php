<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Acl
{
    public static function defaultRole(): string
    {
        return config('acl.default_role', 'user');
    }

    public static function superRoles(): array
    {
        return config('acl.super_roles', []);
    }

    public static function roles(): array
    {
        return config('acl.roles', []);
    }

    public static function roleExists(string $role): bool
    {
        return array_key_exists($role, self::roles());
    }

    public static function normalizeRole(?string $role): string
    {
        $role = $role ?: self::defaultRole();
        return self::roleExists($role) ? $role : self::defaultRole();
    }

    public static function isSuper(User $user): bool
    {
        return in_array(self::normalizeRole($user->role), self::superRoles(), true);
    }

    public static function roleDefinition(string $role): array
    {
        return self::roles()[self::normalizeRole($role)] ?? [];
    }

    public static function rolePermissions(string $role): array
    {
        $role = self::normalizeRole($role);

        $roles = self::roles();
        $def = $roles[$role] ?? [];

        $permissions = Arr::wrap($def['permissions'] ?? []);

        // herencia
        $inherits = Arr::wrap($def['inherits'] ?? []);
        foreach ($inherits as $parent) {
            $permissions = array_merge($permissions, self::rolePermissions($parent));
        }

        // unique
        return array_values(array_unique($permissions));
    }

    public static function userPermissions(User $user): array
    {
        $role = self::normalizeRole($user->role);
        return self::rolePermissions($role);
    }

    public static function hasRole(User $user, string|array $roles): bool
    {
        $userRole = self::normalizeRole($user->role);
        $roles = Arr::wrap($roles);

        return in_array($userRole, $roles, true);
    }

    public static function can(User $user, string $permission): bool
    {
        // super role bypass
        if (self::isSuper($user)) return true;

        $permission = trim($permission);

        $perms = self::userPermissions($user);

        // wildcard total
        if (in_array('*', $perms, true)) return true;

        // match directo
        if (in_array($permission, $perms, true)) return true;

        // match comodines (users.*)
        foreach ($perms as $p) {
            if (Str::endsWith($p, '.*')) {
                $prefix = Str::beforeLast($p, '.*');
                if ($permission === $prefix || Str::startsWith($permission, $prefix . '.')) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function any(User $user, array $permissions): bool
    {
        foreach ($permissions as $p) {
            if (self::can($user, $p)) return true;
        }
        return false;
    }

    public static function all(User $user, array $permissions): bool
    {
        foreach ($permissions as $p) {
            if (!self::can($user, $p)) return false;
        }
        return true;
    }
}
