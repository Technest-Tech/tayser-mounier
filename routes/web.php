<?php

use App\Http\Controllers\BookFileController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\LessonFileController;
use App\Http\Controllers\LocaleController;
use App\Livewire\Actions\Logout;
use App\Livewire\Pages\BookCatalog;
use App\Livewire\Pages\BookShow;
use App\Livewire\Pages\CourseCatalog;
use App\Livewire\Pages\CourseShow;
use App\Livewire\Pages\Home;
use App\Livewire\Pages\MyCourses;
use App\Livewire\Pages\Watch;
use Illuminate\Support\Facades\Route;

// Public storefront --------------------------------------------------------
Route::get('/', Home::class)->name('home');
Route::get('/courses', CourseCatalog::class)->name('courses.index');
Route::get('/courses/{course:slug}', CourseShow::class)->name('courses.show');

// Lesson player & files. Public so free-preview lessons are watchable without
// logging in; access to non-preview lessons is enforced inside the Watch
// component and the LessonFileController.
Route::get('/courses/{course:slug}/learn/{lesson?}', Watch::class)->name('courses.watch');
Route::get('/courses/{course:slug}/lessons/{lesson}/audio', [LessonFileController::class, 'audio'])->name('lessons.audio');
Route::get('/courses/{course:slug}/lessons/{lesson}/audio/download', [LessonFileController::class, 'audioDownload'])->name('lessons.audio.download');
Route::get('/courses/{course:slug}/lessons/{lesson}/pdf', [LessonFileController::class, 'pdf'])->name('lessons.pdf');

// Quiz completion certificate. Requires a signed-in student with a recorded
// attempt (enforced in the controller).
Route::get('/courses/{course:slug}/lessons/{lesson}/certificate', [CertificateController::class, 'show'])
    ->middleware('auth')
    ->name('lessons.certificate');

// Books --------------------------------------------------------------------
Route::get('/books', BookCatalog::class)->name('books.index');
Route::get('/books/{book:slug}/preview', [BookFileController::class, 'preview'])->name('books.preview');
Route::get('/books/{book:slug}/download', [BookFileController::class, 'download'])->name('books.download');
Route::get('/books/{book:slug}', BookShow::class)->name('books.show');

// Language switch ----------------------------------------------------------
Route::post('/locale/{locale}', [LocaleController::class, 'update'])->name('locale.update');

// Authenticated student area ----------------------------------------------
Route::middleware(['auth'])->group(function () {
    Route::get('/my-courses', MyCourses::class)->name('my-courses');

    // Post-login landing — students go straight to their courses.
    Route::get('dashboard', fn () => redirect()->route('my-courses'))->name('dashboard');

    Route::view('profile', 'profile')->name('profile');

    Route::post('/logout', function (Logout $logout) {
        $logout();

        return redirect()->route('home');
    })->name('logout');
});

require __DIR__.'/auth.php';
