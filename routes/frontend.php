<?php

use Illuminate\Support\Facades\Route;
use Workup\MenuBuilder\Http\Controllers\Frontend\MenuController;
use Workup\MenuBuilder\Http\Controllers\Frontend\MenuItemController;

Route::get('/menus', [MenuController::class, 'index']);
Route::get('/menus/{menu}', [MenuController::class, 'show']);
Route::get('/menus/{menu}/{slug}', [MenuItemController::class, 'show']);
