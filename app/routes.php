<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', array('as' => 'home', function() {
    return View::make('test');
}));
Route::get('/test','SongController@test');
Route::get('/add','SongController@addFromForm');


Route::get('/auth/spotify','SongController@spotifyAuth');
Route::get('/auth/spotify/callback','SongController@spotifyCallback');
Route::post('/add','SongController@addFromForm');

Route::get('/profile','SongController@getSpotifyProfile');


