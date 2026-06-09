<?php

use App\Http\Controllers\LocaleController;
use App\Livewire\Actions\Logout;
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

// Language switch ----------------------------------------------------------
Route::post('/locale/{locale}', [LocaleController::class, 'update'])->name('locale.update');

// Authenticated student area ----------------------------------------------
Route::middleware(['auth'])->group(function () {
    Route::get('/my-courses', MyCourses::class)->name('my-courses');
    Route::get('/courses/{course:slug}/learn/{lesson?}', Watch::class)->name('courses.watch');

    Route::view('profile', 'profile')->name('profile');

    Route::post('/logout', function (Logout $logout) {
        $logout();

        return redirect()->route('home');
    })->name('logout');
});

require __DIR__.'/auth.php';
