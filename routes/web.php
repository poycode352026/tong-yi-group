<?php

use App\Http\Controllers\Admin\AccessSettingsController;
use App\Http\Controllers\Admin\LearningResourceController;
use App\Http\Controllers\Admin\ModuleCoverageController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\Admin\SessionMaterialController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Home'))->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::post('/admin/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');
Route::get('/admin', AdminDashboardController::class)->middleware(['auth', 'can:access-admin'])->name('admin.dashboard');

Route::middleware(['auth', 'can:access-admin'])->prefix('admin/programs')->name('admin.programs.')->group(function () {
    Route::get('/', [ProgramController::class, 'index'])->middleware('can:programs.view')->name('index');
    Route::get('/create', [ProgramController::class, 'create'])->middleware('can:programs.manage')->name('create');
    Route::post('/', [ProgramController::class, 'store'])->name('store');
    Route::get('/{program}/edit', [ProgramController::class, 'edit'])->middleware('can:programs.manage')->name('edit');
    Route::put('/{program}', [ProgramController::class, 'update'])->name('update');
});

Route::middleware(['auth', 'can:access-admin', 'can:manage-access'])->prefix('admin/settings')->name('admin.settings.')->group(function () {
    Route::get('/users', [AccessSettingsController::class, 'users'])->name('users');
    Route::put('/users/{user}', [AccessSettingsController::class, 'updateUser'])->name('users.update');
    Route::get('/menus', [AccessSettingsController::class, 'menus'])->name('menus');
    Route::put('/menus', [AccessSettingsController::class, 'updateMenus'])->name('menus.update');
});

Route::middleware(['auth', 'can:access-admin'])->prefix('admin/learning')->name('admin.learning.')->group(function () {
    Route::get('/catalog', [LearningResourceController::class, 'catalog'])->middleware('can:programs.manage')->name('catalog');
    Route::get('/versions/{version}/attachments/{attachment}/file', [LearningResourceController::class, 'attachmentFile'])->name('attachment.file');
    Route::get('/versions/{version}/attachments/{attachment}/preview', [LearningResourceController::class, 'attachmentFile'])->name('attachment.preview');
    Route::post('/session-materials', [SessionMaterialController::class, 'store'])->name('session-materials.store');
    Route::get('/versions/{version}/questions/{question}/{kind}', [LearningResourceController::class, 'questionMedia'])->name('question.media');
    Route::get('/versions/{version}/preview', [LearningResourceController::class, 'preview'])->name('preview');
    Route::get('/versions/{version}/file', [LearningResourceController::class, 'file'])->name('file');
    Route::get('/versions/{version}', [LearningResourceController::class, 'version'])->middleware('can:learning.manage')->name('version');
    Route::middleware('can:learning.manage')->prefix('materials/coverage')->name('coverage.')->group(function () {
        Route::get('/', [ModuleCoverageController::class, 'index'])->name('index');
        Route::post('/', [ModuleCoverageController::class, 'store'])->name('store');
        Route::post('/{resource}', [ModuleCoverageController::class, 'update'])->name('update');
        Route::post('/{resource}/archive', [ModuleCoverageController::class, 'archive'])->name('archive');
        Route::post('/{resource}/restore', [ModuleCoverageController::class, 'restore'])->name('restore');
    });
    Route::middleware('can:learning.manage')->whereIn('kind', ['frameworks', 'materials', 'assessments'])->group(function () {
        Route::get('/{kind}', [LearningResourceController::class, 'index'])->name('index');
        Route::get('/{kind}/create', [LearningResourceController::class, 'create'])->name('create');
        Route::post('/{kind}', [LearningResourceController::class, 'store'])->name('store');
        Route::get('/{kind}/{resource}/edit', [LearningResourceController::class, 'edit'])->name('edit');
        Route::post('/{kind}/{resource}', [LearningResourceController::class, 'update'])->name('update');
    });
});

Route::middleware(['auth', 'can:access-admin', 'can:manage-access'])->prefix('admin/system')->name('admin.system.')->group(function () {
    Route::get('/activity', [SystemController::class, 'activity'])->name('activity');
    Route::get('/activity/{log}', [SystemController::class, 'detail'])->whereNumber('log')->name('activity.detail');
    Route::get('/archives', [SystemController::class, 'archives'])->name('archives');
    Route::post('/archives/{program}/restore', [SystemController::class, 'restore'])->name('archives.restore');
});
