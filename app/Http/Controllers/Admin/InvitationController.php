<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\InviteStaffRequest;
use App\Models\StaffInvitation;
use App\Services\InvitationService;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    public function __construct(private InvitationService $invitationService) {}

    public function index()
    {
        $invitations = StaffInvitation::with(['role', 'invitedBy'])
            ->latest()
            ->paginate(20);

        return view('admin.invitations.index', compact('invitations'));
    }

    public function store(InviteStaffRequest $request)
    {
        $this->invitationService->invite($request->validated(), $request->user());

        return redirect()->route('admin.invitations.index')
            ->with('success', "Invitation sent to {$request->email}.");
    }

    public function resend(StaffInvitation $invitation)
    {
        abort_if($invitation->isAccepted(), 422, 'This invitation has already been accepted.');

        $this->invitationService->invite([
            'email'       => $invitation->email,
            'name'        => $invitation->name,
            'employee_id' => $invitation->employee_id,
            'department'  => $invitation->department,
            'phone'       => $invitation->phone,
            'role'        => $invitation->role->slug,
        ], request()->user());

        return back()->with('success', 'Invitation resent successfully.');
    }
}