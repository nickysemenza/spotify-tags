@extends('layout')

@section('content')


<div class="persistent-bar">
    <div class="spotify-mediabar-wrapper">
        <div id="spotify-mediabar" class="spotify-mediabar spotify-height-animation spotify-new-design spotify-show spotify-stick" data-require="spotify/spotifymediabar" style="height: 57px;"><iframe src="https://embed.spotify.com/mediabar/?uri=" kwframeid="4" class=" spotify-show"></iframe></div>
    </div>
    <a href="#" class="spotify-mediabar-feedback" data-require="labfeedbackbutton" data-lab-name="spotifyplayback">
        <span class="beta-badge">Beta</span> Feedback
    </a>
</div>

<link rel="stylesheet" type="text/css" href="//embed.spotify.com/static/css/mediabar/mediabar-lastfm.css">

<script src="//embed.spotify.com/static/js/mediabar-lastfm.js"></script>

yo

    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script>
        $.ajax({
            url: "https://qsqlwzbzox.spotilocal.com:4370/remote/status.json?csrf=5042fd6d539eaa16eea01358fda51330&oauth=NAowChgKB1Nwb3RpZnkSABoGmAEByAEBJaFH61MSFJkM6-pHBOjtbV1zeGeFeiqTRNmK&returnon=login%2Clogout%2Cplay%2Cpause%2Cerror%2Cap&returnafter=60&ref=http%3A%2F%2Fwww.last.fm%2Fmusic%2FTobu&cors=",
            headers: {"Origin": "https://embed.spotify.com'"}
        });
    </script>



@stop