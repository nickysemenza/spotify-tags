@extends('layout')

@section('content')


<iframe src="https://embed.spotify.com/?uri=spotify:trackset:{{$data['listtitle']}}:{{$data['songstring']}}" width="300" height="380" frameborder="0" allowtransparency="true"></iframe>

@stop