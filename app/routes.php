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
    return View::make('home');
}));
Route::get('/test','SongController@test');


Route::get('/auth/spotify','SongController@spotifyAuth');
Route::get('/auth/spotify/callback','SongController@spotifyCallback');

Route::get('/profile','SongController@getSpotifyProfile');
Route::get('/getTagsJSON','SongController@getTagsJSON');
Route::get('/addTrackToPlaylist/{playlist_id}/{track_id}','SongController@addTrackToPlaylist');
Route::get('/removeTrackFromPlaylist/{playlist_id}/{track_id}','SongController@removeTrackFromPlaylist');