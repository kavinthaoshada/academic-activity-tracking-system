<?php

use App\Http\Controllers\Admin\AcademicWeekController;
use App\Http\Controllers\Admin\BatchController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\InvitationController;
use App\Http\Controllers\Admin\ProgrammeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\InvitationAcceptController;
use App\Http\Controllers\Staff\ReportController;
use App\Http\Controllers\Staff\SessionController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

// Public invitation routes (no auth)
Route::get('/invitation/{token}',         [InvitationAcceptController::class, 'show'])->name('invitation.accept');
Route::post('/invitation/{token}/accept', [InvitationAcceptController::class, 'store'])->name('invitation.store');

Route::get('reports/download', [ReportController::class, 'download'])
    ->name('reports.download')
    ->middleware('signed');

// Authenticated routes
Route::middleware(['auth', 'active.user', 'track.login'])->group(function () {

    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

    Route::get('/profile', fn () => view('profile.show'))->name('profile.show');

    Route::post('/notifications/mark-all-read', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'All notifications marked as read.');
    })->name('notifications.markAllRead');

    Route::get('sessions',        [SessionController::class, 'index'])->name('sessions.index');
    Route::get('sessions/create', [SessionController::class, 'create'])->name('sessions.create');
    Route::post('sessions',       [SessionController::class, 'store'])->name('sessions.store');
    Route::get('sessions/{session}', [SessionController::class, 'show'])->name('sessions.show');

    Route::get('/reports',                    [ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/generate',          [ReportController::class, 'generate'])->name('reports.generate');
    Route::post('/reports/download-now',      [ReportController::class, 'downloadNow'])->name('reports.download-now');
    Route::post('/reports/course',            [ReportController::class, 'downloadCourseReport'])->name('reports.course');
    Route::post('/reports/semester',          [ReportController::class, 'downloadSemesterReport'])->name('reports.semester');
    Route::get('/reports/download',           [ReportController::class, 'download'])->name('reports.download');


    // Admin-only
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {

        Route::get('users',                    [UserController::class, 'index'])->name('users.index');
        Route::get('users/{user}',             [UserController::class, 'show'])->name('users.show');
        Route::get('users/{user}/edit',        [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}',             [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}',          [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

        Route::post('users/{user}/assign-courses', [UserController::class, 'assignCourses'])->name('users.assign-courses');

        Route::get('invitations',                          [InvitationController::class, 'index'])->name('invitations.index');
        Route::post('invitations',                         [InvitationController::class, 'store'])->name('invitations.store');
        Route::delete('invitations/{invitation}',          [InvitationController::class, 'destroy'])->name('invitations.destroy');
        Route::post('invitations/{invitation}/resend',     [InvitationController::class, 'resend'])->name('invitations.resend');

        Route::resource('programmes', ProgrammeController::class);

        Route::resource('batches', BatchController::class);

        Route::resource('courses', CourseController::class);

        Route::resource('academic-weeks', AcademicWeekController::class);
        Route::post('academic-weeks/{academic_week}/lock',   [AcademicWeekController::class, 'lock'])->name('academic-weeks.lock');
        Route::post('academic-weeks/{academic_week}/unlock', [AcademicWeekController::class, 'unlock'])->name('academic-weeks.unlock');
    });
});