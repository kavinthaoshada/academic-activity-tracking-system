<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class StaffInvitation extends Model
{
    use Notifiable;

    protected $fillable = [
        'email', 'name', 'employee_id', 'department', 'phone',
        'role_id', 'invited_by', 'token', 'accepted_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'accepted_at' => 'datetime',
            'expires_at'  => 'datetime',
        ];
    }

    public function role()        { return $this->belongsTo(Role::class); }
    public function invitedBy()   { return $this->belongsTo(User::class, 'invited_by'); }

    public function isPending(): bool  { return is_null($this->accepted_at); }
    public function isExpired(): bool  { return $this->expires_at->isPast(); }
    public function isAccepted(): bool { return !is_null($this->accepted_at); }
}
