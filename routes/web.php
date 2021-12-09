<?php

/**
 * use-Statements erlauben es uns Klassen mit ihrem Namespace einmal oben zu definieren. Dadurch ersparen wir uns unten
 * immer den gesamten Namespace anzugeben.
 */

use App\Controllers\AuthController;
use App\Controllers\BookingController;
use App\Controllers\EquipmentController;
use App\Controllers\HomeController;
use App\Controllers\RoomController;
use App\Controllers\RoomFeatureController;
use App\Controllers\CartController;
use App\Controllers\CheckoutController;
use App\Controllers\ProfileController;

/**
 * Die Dateien im /routes Ordner beinhalten ein Mapping von einer URL auf eine eindeutige Controller & Action
 * kombination. Als Konvention definieren wir, dass URL-Parameter mit {xyz} definiert werden müssen, damit das Routing
 * korrekt funktioniert.
 *
 * + /blog/{slug} --> BlogController->show($slug)
 * + /shop/{id} --> ProductController->show($id)
 */

return [
    /**
     * Index Route
     */
    '/' => [HomeController::class, 'index'], // HomeController::class => "App\Controllers\HomeController"

    /**
     * Auth Routes
     */
    '/login' => [AuthController::class, 'loginForm'],
    '/login/do' => [AuthController::class, 'loginDo'],
    '/logout' => [AuthController::class, 'logout'],
    '/sign-up' => [AuthController::class, 'signupForm'],
    '/sign-up/do' => [AuthController::class, 'signupDo'],

    /**
     * Home Route
     */
    '/home' => [HomeController::class, 'home'],

    /**
     * Rooms Routes
     */
    '/rooms' => [RoomController::class, 'index'],
    '/rooms/{id}/show' => [RoomController::class, 'show'],
    '/rooms/{id}' => [RoomController::class, 'edit'],
    '/rooms/{id}/update' => [RoomController::class, 'update'],
    '/rooms/{id}/delete' => [RoomController::class, 'delete'],
    '/rooms/{id}/delete/confirm' => [RoomController::class, 'deleteConfirm'],
    '/rooms/create' => [RoomController::class, 'create'],
    '/rooms/store' => [RoomController::class, 'store'],

    /**
     * RoomFeatures Routes
     */
    '/room-features' => [RoomFeatureController::class, 'index'],
    '/room-features/{id}' => [RoomFeatureController::class, 'edit'],
    '/room-features/{id}/update' => [RoomFeatureController::class, 'update'],
    '/room-features/{id}/delete' => [RoomFeatureController::class, 'delete'],
    '/room-features/{id}/delete/confirm' => [RoomFeatureController::class, 'deleteConfirm'],
    '/room-features/create' => [RoomFeatureController::class, 'create'],
    '/room-features/store' => [RoomFeatureController::class, 'store'],

    /**
     * Equipments Routes
     */
    '/equipments' => [EquipmentController::class, 'index'],
    '/equipments/{id}/show' => [EquipmentController::class, 'show'],
    '/equipments/{id}' => [EquipmentController::class, 'edit'],
    '/equipments/{id}/update' => [EquipmentController::class, 'update'],
    '/equipments/{id}/delete' => [EquipmentController::class, 'delete'],
    '/equipments/{id}/delete/confirm' => [EquipmentController::class, 'deleteConfirm'],
    '/equipments/create' => [EquipmentController::class, 'create'],
    '/equipments/store' => [EquipmentController::class, 'store'],

    /**
     * Cart Routes
     */
    '/cart' => [CartController::class, 'index'],
    '/equipments/{id}/add-to-cart' => [CartController::class, 'add'],
    '/equipments/{id}/remove-from-cart' => [CartController::class, 'remove'],
    '/equipments/{id}/remove-all-from-cart' => [CartController::class, 'removeAll'],

    /**
     * Checkout Routes
     */
    '/checkout/summary' => [CheckoutController::class, 'summary'],
    '/checkout/finish' => [CheckoutController::class, 'finish'],

    /**
     * Booking Routes
     */
    '/rooms/{id}/booking/time' => [BookingController::class, 'selectSlots'],
    '/rooms/{id}/booking/do' => [BookingController::class, 'bookSlots'],

    /**
     * User Profile Routes
     */
    '/profile' => [ProfileController::class, 'profile'],
    '/profile/update' => [ProfileController::class, 'update'],

    // ...
];
