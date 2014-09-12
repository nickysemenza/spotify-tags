@extends('layout')

@section('content')


<hr>

@if(Auth::check())
yo
@endif

<?php
$x=470;
$next=100;
while($next<$x)
{
    print $next;
    $next=$next+100;
}

?>
should be 100, 200, 300, 400
@stop