<?php

use App\Http\Controllers\AuditTrailController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TodoController;
use Illuminate\Support\Facades\Route;

// public routes
Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::get('/user', [AuthController::class, 'user'])->name('auth.user');

    Route::put('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.update.password');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/todos', [TodoController::class, 'index'])->name('todos.index');
    Route::post('/todos', [TodoController::class, 'store'])->name('todos.store');
    Route::get('/todos/{id}', [TodoController::class, 'show'])->name('todos.show')->whereNumber('id');
    Route::put('/todos/{id}', [TodoController::class, 'update'])->name('todos.update')->whereNumber('id');
    Route::delete('/todos/{id}', [TodoController::class, 'destroy'])->name('todos.destroy')->whereNumber('id');

    /* use whereNumber to constrain the format of the route parameter. This will ensure we get 404 when route param is a string instead of integer */
    Route::get('/audit-trail/{id}', [AuditTrailController::class, 'index'])->name('audit.index')->whereNumber('id');
});
