<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PublisherController;
use App\Http\Controllers\BorrowingController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\FineController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::put('/user', [AuthController::class, 'updateUser'])->middleware('permission:profile.update');
    Route::delete('/user', [AuthController::class, 'deleteUser']);
    Route::post('/user/profile-image', [AuthController::class, 'handleFileImage']);
});

// Author routes
Route::middleware('auth:api')->group(function () {

    Route::get('/authors', [AuthorController::class, 'index']);
    Route::post('/authors', [AuthorController::class, 'store']);
    Route::get('/authors/{id}', [AuthorController::class, 'show']);
    Route::put('/authors/{id}', [AuthorController::class, 'update']);
    Route::delete('/authors/{id}', [AuthorController::class, 'destroy']);
});

// publisher routes
Route::middleware('auth:api')->group(function () {

    Route::get('/publishers', [PublisherController::class, 'index'])->middleware('permission:publishers.view');
    Route::post('/publishers', [PublisherController::class, 'store'])->middleware('permission:publishers.create');
    Route::get('/publishers/{id}', [PublisherController::class, 'show'])->middleware('permission:publishers.view');
    Route::put('/publishers/{id}', [PublisherController::class, 'update'])->middleware('permission:publishers.update');
    Route::delete('/publishers/{id}', [PublisherController::class, 'destroy'])->middleware('permission:publishers.delete');
});

// Category routes
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);

Route::middleware('auth:api')->group(function () {
    Route::post('/categories', [CategoryController::class, 'store'])->middleware('permission:categories.create');
    Route::put('/categories/{id}', [CategoryController::class, 'update'])->middleware('permission:categories.update');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->middleware('permission:categories.delete');
});

// book routes
    Route::get('/books', [BookController::class, 'index']);
    Route::get('/books/{id}', [BookController::class, 'show']);

Route::middleware('auth:api')->group(function () {
    Route::post('/books', [BookController::class, 'store']);
    Route::put('/books/{id}', [BookController::class, 'update']);
    Route::delete('/books/{id}', [BookController::class, 'destroy']);
});

//member route
    Route::get('/members', [MemberController::class, 'index'])->middleware(['auth:api','permission:members.view']);
    Route::put('/members/{id}', [MemberController::class, 'update'])->middleware(['auth:api','permission:members.update']);

//permission route
Route::middleware(['auth:api', 'role:administrator'])->group(function () {

    Route::post('/auth/assign-role', [PermissionController::class, 'assignRole']);
    Route::post('/auth/assign-permission', [PermissionController::class, 'assignPermission']);
    Route::post('/auth/remove-role', [PermissionController::class, 'invokeRole']);
    Route::post('/auth/remove-permission', [PermissionController::class, 'invokePermission']);
    Route::get('/user-role/{id}', [PermissionController::class, 'getUserRole']);
    Route::get('/get-roles', [PermissionController::class, 'getRoles']);
    Route::get('/get-permissions', [PermissionController::class, 'getPermissions']);
    
});

// borrowing route
Route::middleware('auth:api')->group(function(){
    Route::get('/borrowings', [BorrowingController::class, 'index']);
    Route::post('/borrow', [BorrowingController::class, 'borrow']);
    Route::post('/return', [BorrowingController::class, 'returnBook']);
});

// fines route
Route::middleware('auth:api')->group(function () {

    Route::get('/fines', [FineController::class, 'index']);
    Route::post('/fines', [FineController::class, 'store']);
    Route::get('/fines/{id}', [FineController::class, 'show']);
    Route::put('/fines/{id}', [FineController::class, 'update']);
    Route::delete('/fines/{id}', [FineController::class, 'destroy']);
});

// Reservation route
Route::middleware('auth:api')->group(function () {

    Route::get('/reservations', [ReservationController::class, 'index']);
    Route::post('/reservations', [ReservationController::class, 'store']);
    Route::get('/reservations/{id}', [ReservationController::class, 'show']);
    Route::delete('/reservations/{id}', [ReservationController::class, 'destroy']);
});