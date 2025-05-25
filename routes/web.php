<?php

declare(strict_types=1);

use App\Livewire\CategoryShow;
use App\Livewire\Home;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class)->name('home');
Route::get('/category/{category:slug}', CategoryShow::class)->name('category.show');
