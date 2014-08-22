@extends('layout')

@section('content')


<iframe src="https://embed.spotify.com/?uri=spotify:trackset:{{$data['listtitle']}}:{{$data['songstring']}}" width="300" height="380" frameborder="0" allowtransparency="true"></iframe>


{{Form::open(array('action' => 'SongController@addFromForm', 'method' => 'get'))}}
{{Form::textarea('text','',array('class' => 'form-control'))}}
{{Form::submit('Click Me!',array('class' => 'btn btn-default btn-submit'))}}
{{ Form::close() }}



@stop