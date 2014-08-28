@extends('layout')

@section('content')


<style>
    .twitter-typeahead .tt-query,
    .twitter-typeahead .tt-hint {
        margin-bottom: 0;
    }

    .twitter-typeahead .tt-hint
    {
        display: none;
    }

    .tt-dropdown-menu {
        position: absolute;
        top: 100%;
        left: 0;
        z-index: 1000;
        display: none;
        float: left;
        min-width: 160px;
        padding: 5px 0;
        margin: 2px 0 0;
        list-style: none;
        font-size: 14px;
        background-color: #ffffff;
        border: 1px solid #cccccc;
        border: 1px solid rgba(0, 0, 0, 0.15);
        border-radius: 4px;
        -webkit-box-shadow: 0 6px 12px rgba(0, 0, 0, 0.175);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.175);
        background-clip: padding-box;
    }
    .tt-suggestion > p {
        display: block;
        padding: 3px 20px;
        clear: both;
        font-weight: normal;
        line-height: 1.428571429;
        color: #333333;
        white-space: nowrap;
    }
    .tt-suggestion > p:hover,
    .tt-suggestion > p:focus,
    .tt-suggestion.tt-cursor p {
        color: #ffffff;
        text-decoration: none;
        outline: 0;
        background-color: #428bca;
    }
</style>
<div class="row">
@foreach($tagsAndPlaylistIDs as $eachPlaylistData)
    <div class="col-xs-4">
        <table  class="table table-bordered table-striped">

<tr><td><iframe src="https://embed.spotify.com/?uri=spotify:user:{{$data['username']}}:playlist:{{$eachPlaylistData['playlist_id']}}" width="300" height="80" frameborder="0" allowtransparency="true"></iframe></td>
</tr></tr>
            <tr><td><h3>{{$eachPlaylistData['name']}}</h3></td></tr>

        </table>
    </div>
    @endforeach





</div>


<hr>
<table  class="table table-bordered table-striped">
@foreach ( $songData as $key => $val )
<tr>
<!--<td>{{ $key }}</td>-->
<td>{{$val['name']}}</td>
    <td>{{$val['artists']}}</td>
    <td>
        <?php $tagNames=array();?>
        @foreach($val['tags'] as $eachTag)
        <?php array_push($tagNames,$eachTag['tagname']);?>
        @endforeach
        <input type="text" id="{{$key}}"value="{{implode(',',$tagNames)}}" data-role="tagsinput" />
    </td>
</tr>
@endforeach
</table>

@foreach($tagsAndPlaylistIDs as $eachPlaylistData)
<!--<input type="hidden" id="id_playlist_{{str_replace('%','-',urlencode($eachPlaylistData['name']))}}" data-id="{{$eachPlaylistData['playlist_id']}}">-->
<input2 type="hidden" id="id_playlist_{{bin2hex($eachPlaylistData['name'])}}" data-id="{{$eachPlaylistData['playlist_id']}}">

@endforeach
<script>
    function toHex(str) {
        var hex = '';
        for(var i=0;i<str.length;i++) {
            hex += ''+str.charCodeAt(i).toString(16);
        }
        return hex;
    }
</script>
<script>

    var playlistnames = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('name'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        prefetch: {
            url: '/getTagsJSON',
            filter: function(list) {
                return $.map(list, function(cityname) {
                    return { name: cityname }; });
            }
        }
    });
    playlistnames.clearPrefetchCache();
    playlistnames.initialize();

    $('input').tagsinput({
        typeaheadjs: {
            name: 'playlistnames',
            displayKey: 'name',
            valueKey: 'name',
            source: playlistnames.ttAdapter()
        }
    });


    $('input').on('itemAdded', function(event) {
        var song_id=$(this).attr('id');
        var playlist_id=$('#id_playlist_'+toHex(event.item)).data("id");
        console.log(event.item +" ("+playlist_id+") tag added to song "+song_id);
        form_data = {

        };

        $.ajax(
            {
                type: 'GET',
                url: '/addTrackToPlaylist/'+playlist_id+'/'+song_id,
                data: form_data,
                success:function (data)
                {

                    console.log("Data: " + data['status'] + " " + data['text']);
                }
            }, 'json');
    });
    $('input').on('itemRemoved', function(event) {
        var song_id=$(this).attr('id');
        var playlist_id=$('#id_playlist_'+toHex(event.item)).data("id");
        console.log(event.item +" ("+playlist_id+") tag removed from song "+song_id);


        form_data = {

        };

        $.ajax(
            {
                type: 'GET',
                url: '/removeTrackFromPlaylist/'+playlist_id+'/'+song_id,
                data: form_data,
                success:function (data)
                {

                    console.log("Data: " + data['status'] + " " + data['text']);
                }
            }, 'json');

    });
</script>

@stop