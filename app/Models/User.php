<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Support\Acl;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    public function getRoleAttribute($value)
    {
        return Acl::normalizeRole($value);
    }

   public function hasRole(string|array $roles): bool
    {
        return Acl::hasRole($this, $roles);
    }

    public function hasPermission(string $permission): bool
    {
        return Acl::can($this, $permission);
    }

    public function hasAnyPermission(array $permissions): bool
    {
        return Acl::any($this, $permissions);
    }

    public function hasAllPermissions(array $permissions): bool
    {
        return Acl::all($this, $permissions);
    }
    /* Con esto se puede hacer
        @can('users.update')
            <button>Editar usuario</button>
        @endcan
        o en controllers
        $this->authorize('units.move_state'); // o Gate::authorize(...)
    */

}
