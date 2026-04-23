<?php

namespace App\Services;

use App\Jobs\SendInvitationEmail;
use App\Models\Role;
use App\Models\StaffInvitation;
use App\Models\User;
use Illuminate\Support\Str;

class InvitationService
{
    public function invite(array $data, User $invitedBy): StaffInvitation
    {
        StaffInvitation::where('email', $data['email'])
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->delete();

        $invitation = StaffInvitation::create([
            'email'       => $data['email'],
            'name'        => $data['name'],
            'employee_id' => $data['employee_id'] ?? null,
            'department'  => $data['department'] ?? null,
            'phone'       => $data['phone'] ?? null,
            'role_id'     => Role::where('slug', $data['role'] ?? 'staff')->first()->id,
            'invited_by'  => $invitedBy->id,
            'token'       => Str::random(64),
            'expires_at'  => now()->addDays(7),
        ]);

        SendInvitationEmail::dispatch($invitation);

        return $invitation;
    }

    public function accept(StaffInvitation $invitation, string $password): User
    {
        throw_if($invitation->isExpired(),  \Exception::class, 'This invitation has expired.');
        throw_if($invitation->isAccepted(), \Exception::class, 'This invitation has already been used.');

        $user = User::create([
            'name'        => $invitation->name,
            'email'       => $invitation->email,
            'password'    => $password,
            'role_id'     => $invitation->role_id,
            'employee_id' => $invitation->employee_id,
            'department'  => $invitation->department,
            'phone'       => $invitation->phone,
            'is_active'   => true,
        ]);

        $invitation->update(['accepted_at' => now()]);

        return $user;
    }
}