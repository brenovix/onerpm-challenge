<?php

use App\Actions\DisplayIndexPage;
use App\Actions\TrackList;
use Illuminate\Support\Facades\Route;

Route::get('/', DisplayIndexPage::class)->name('index');

Route::group(['prefix' => 'api'], function () {
    Route::get('/tracks', TrackList::class);
});
