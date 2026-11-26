<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'member_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function hasRole(string $role): bool
    {
        $roleId = config("document_signing.roles.{$role}");

        return $roleId !== null
            && (int) $this->role_id === (int) $roleId;
    }

    public function isAdministrator(): bool
    {
        return $this->hasRole('admin');
    }

    public function isSecretary(): bool
    {
        return $this->hasRole('secretary');
    }

    public function isPresidingOfficer(): bool
    {
        return $this->hasRole('presiding_officer');
    }

    public function isCouncilMember(): bool
    {
        return $this->hasRole('member');
    }
}
