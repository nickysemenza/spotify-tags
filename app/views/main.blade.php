@extends('layout')

@section('content')


var_dump($songData)}}


<table  class="table table-bordered table-striped">
@foreach ( $songData as $key => $val )
<tr>
<td>{{ $key }}</td>
<td>{{$val['name']}}</td>
    <td>
@foreach($val['tags'] as $eachTag)
        <span class="label label-primary">{{substr($eachTag,3)}}</span>
    @endforeach
    </td>
</tr>
@endforeach
</table>

@stop