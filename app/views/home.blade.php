@extends('layout')

@section('content')

<!-- Main jumbotron for a primary marketing message or call to action -->
<div class="jumbotron">
    <div class="container">
        <h1>Spotify Tags</h1>
        <p>Easily manage categorization of your spotify library across multiple playlists.</p>
        <p><a class="btn btn-primary btn-lg" href="{{action('SongController@getSpotifyProfile')}}" role="button">Sign Up &raquo;</a></p>
    </div>
</div>
<div class="container">
    <!-- Example row of columns -->
    <div class="row">
        <div class="col-md-4">
            <h2>Organized View</h2>
            <p>Instead of a list of songs for each playlist, you can see all of your songs together, and then which playlist(s) they are on.</p>
            <p><a class="btn btn-default" href="#" role="button">View details &raquo;</a></p>
        </div>
        <div class="col-md-4">
            <h2>Instant Sync</h2>
            <p>Any changes you make will be immediately reflected in your spotify client.</p>
            <p><a class="btn btn-default" href="#" role="button">View details &raquo;</a></p>
        </div>
        <div class="col-md-4">
            <h2>Autocomplete</h2>
            <p>Just start typing a name of a playlist to add a song to, and all options will appear.</p>
            <p><a class="btn btn-default" href="#" role="button">View details &raquo;</a></p>
        </div>
    </div>

    <hr>
</div> <!-- /container -->

@stop