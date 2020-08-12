<?php

Route::prefix('larapal')->namespace('hypnodev\\Larapal\\Http\\Controllers')->group(function () {
    Route::get('/return', 'LarapalController@paid');
    Route::get('/cancel', 'LarapalController@cancel');
});
