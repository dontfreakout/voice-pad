<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

// Catch-all route to serve the Nuxt.js application
// Ensure that 'spa' view loads the Nuxt app.
// This might have been named 'welcome.blade.php' or similar during Laravel/Nuxt setup.
Route::get('/{any?}', function () {
    return view('spa'); // Replace 'spa' with your actual Nuxt hosting blade file if different
})->where('any', '.*')->name('nuxt.app');
