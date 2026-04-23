<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\StaffInvitation;
use App\Services\InvitationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvitationAcceptController extends Controller
{
    public function __construct(private InvitationService $invitationService) {}

    public function show(string $token)
    {
        $invitation = StaffInvitation::where('token', $token)->firstOrFail();

        if ($invitation->isExpired())  return view('auth.invitation-expired', compact('invitation'));
        if ($invitation->isAccepted()) return redirect()->route('login')->with('info', 'This invitation has already been used. Please log in.');

        return view('auth.accept-invitation', compact('invitation'));
    }

    public function store(Request $request, string $token)
    {
        $invitation = StaffInvitation::where('token', $token)->firstOrFail();

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $this->invitationService->accept($invitation, $request->password);

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Welcome! Your account has been set up successfully.');
    }
}