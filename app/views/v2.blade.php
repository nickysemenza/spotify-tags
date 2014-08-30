@extends('layout')

@section('content')




<script>

    function getPlaylists(accessToken,user_id) {
        $.ajax({
            url: 'https://api.spotify.com/v1/users/' + user_id + '/playlists',
            headers: {
                'Authorization': 'Bearer ' + accessToken
            },
            success: function(response) {
                console.log(response.items);
                var songNamesAndIDs ={};
                for (var key in response.items) {
                    console.log(key, response.items[key]);
                    songNamesAndIDs[response.items[key].id]=response.items[key].name
                }
                console.log(songNamesAndIDs);
            }
        });
    }
     getPlaylists("{{$data['token']}}","14nicholasse");
    console.log(playlists);
    for(var key in playlists)
    {
        alert("key " + key + " has value " + playlists[key]);
    }



//
//    playlistsListPlaceholder.addEventListener('click', function(e) {
//        var target = e.target;
//        if (target !== null && target.classList.contains('load')) {
//            e.preventDefault();
//            var link = target.getAttribute('data-link');
//
//            $.ajax({
//                url: link,
//                headers: {
//                    'Authorization': 'Bearer ' + token
//                },
//                success: function(response) {
//                    console.log(response);
//                    playlistDetailPlaceholder.innerHTML = playlistDetailTemplate(response);
//                }
//            });
//        }
//    });
</script>





@if(Auth::check())
yo
@endif
@stop