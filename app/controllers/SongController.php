<?php
$global_response=array();
class SongController extends BaseController {

	public function test()
	{

        $data='';
		return View::make('test',compact('data'));
	}

    public function spotifyAuth()
    {
        $response = Unirest::get("https://accounts.spotify.com/authorize", array( "Accept" => "application/json" ),
            array(
                "client_id" => "d27efb143d5d4719959e523a5cbfa3c4",
                "response_type" => "code",
                "redirect_uri"=>URL::to('/auth/spotify/callback'),
                "show_dialog"=>"true",
                "scope"=>implode(' ',array('user-read-private', 'user-read-email','user-library-read','user-library-modify','playlist-modify','playlist-modify-public','playlist-modify-private'))
            )
        );
        return Redirect::to($response->body->redirect);

    }
    public function spotifyCallback($code="0")
    {
        error_log($code);
        if($code=="0")
        {
            $code=$_GET['code'];
        }
        //post request
        $fields_string="";
        $url = 'https://accounts.spotify.com/api/token';
        $fields = array(
            "client_id" => "d27efb143d5d4719959e523a5cbfa3c4",
            "client_secret"=>"5a37334c1e994ae0ba07f6cac6366233",
            "grant_type" => "authorization_code",
            "redirect_uri"=>URL::to('/auth/spotify/callback'),
            "code"=>$code
        );
        foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        rtrim($fields_string, '&');
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, count($fields));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);


//execute post
        $result = curl_exec($ch);
        $callbackResult=json_decode($result,true);

        curl_close($ch);

        //var_dump($callbackResult); exit;
        //register/login/save token to DB
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.spotify.com/v1/me");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER,array("Authorization: Bearer ".$callbackResult['access_token'],"Content-Type: application/json"));
        $data = json_decode(curl_exec($ch),true);
        curl_close($ch);
        //var_dump($data); exit;
        $user=User::find($data['id']);
        if($user==null)
        {
            echo("registration not complete");
            $user = User::firstOrCreate(
                array(
                    'uid' => $data['id'],
                    'display_name'=>$data['display_name'],
                    'email'=>$data['email']
                )
            );
        }
        $user->access_token=$callbackResult['access_token'];
        $user->refresh_token=$callbackResult['refresh_token'];
        $user->save();
        Auth::login($user);

        return Redirect::action('SongController@getSpotifyProfile');


    }
    public function getSpotifyProfile()
    {

        global $global_response;

        //get request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.spotify.com/v1/users/".Auth::user()->uid."/playlists");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER,array("Authorization: Bearer ".Auth::user()->access_token));
        $data = json_decode(curl_exec($ch),true);
        curl_close($ch);

        if(isset($data['error']['message']))
        {
            $this->spotifyCallback(Auth::user()->refresh_token);
        }
        $rc = new RollingCurl(array($this, "request_callback"));
        $rc->window_size = 200;

        $temp=array();
        $tagsAndPlaylistIDs=array();
        for($x=0; $x<$data['limit']; $x++)
        {
            $id=$data['items'][$x]['id'];
            $rc->request("https://api.spotify.com/v1/users/".Auth::user()->uid."/playlists/".$id);
        }

        $rc->options = array(CURLOPT_HTTPHEADER => array("Authorization: Bearer ".Auth::user()->access_token), CURLOPT_RETURNTRANSFER => 1);
        $test = $rc->execute();

        foreach($global_response as $eachPlaylist)
        {

            $playlistName=$eachPlaylist['name'];
            if($eachPlaylist['tracks']['total']<$eachPlaylist['tracks']['limit'])
            {
                $stopAt=$eachPlaylist['tracks']['total'];
            }
            else
            {
                $stopAt=$eachPlaylist['tracks']['limit'];
            }
            for($y=0; $y<$stopAt; $y++)
            {

                $temp[$eachPlaylist['tracks']['items'][$y]['track']['id']]['tags'][]=array("tagname"=>$playlistName,"playlist_id"=>$id);
                $temp[$eachPlaylist['tracks']['items'][$y]['track']['id']]['name']=$eachPlaylist['tracks']['items'][$y]['track']['name'];
                $temp[$eachPlaylist['tracks']['items'][$y]['track']['id']]['artists']=implode(', ', array_column($eachPlaylist['tracks']['items'][$y]['track']['artists'], 'name'));
                $taginfo=array('name'=>$playlistName,'playlist_id'=>$eachPlaylist['id'],'user_id'=>Auth::user()->id);
                if(!in_array($taginfo,$tagsAndPlaylistIDs,true))
                {
                    array_push($tagsAndPlaylistIDs,$taginfo);
                }

            }


        }



        $affectedRows = Tags::where('user_id', '=', Auth::user()->id)->delete();
        Tags::insert($tagsAndPlaylistIDs);
        $songData=$temp;
        $data['username']=Auth::id();
        return View::make('main',compact('songData','tagsAndPlaylistIDs','data'));

    }

    function request_callback($response, $info) {
        global $global_response;
        //var_dump(json_decode($response,true));
//        //print_r($info);
//        echo("<br>");
        //array_push($global_response,json_decode($response,true));
        $global_response[]=json_decode($response,true);
//        var_dump($global_response);
        //return $response;
    }
    public function getTagsJSON()
    {
        $tags = Tags::where('user_id', '=', Auth::user()->id)->get();
        $arr=array();
        foreach ($tags as $eachTag)
        {
            array_push($arr,$eachTag->name);
        }
        return(json_encode($arr));
    }
    public function addTrackToPlaylist($playlist_id,$track_id)
    {
        $fields_string="";
        $url = 'https://api.spotify.com/v1/users/'.Auth::id().'/playlists/'.$playlist_id.'/tracks';
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        //curl_setopt($ch,CURLOPT_POSTFIELDS, "urls=spotify:track:".$track_id);
        curl_setopt($ch,CURLOPT_POSTFIELDS,'["spotify:track:'.$track_id.'"]');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER,array("Authorization: Bearer ".Auth::user()->access_token));
        $result=curl_exec($ch);
        curl_close($ch);
        //var_dump($result);
        error_log("adding track ".$track_id." to playlist ".$playlist_id);

    }
    public function removeTrackFromPlaylist($playlist_id,$track_id)
    {
        $json='{ "tracks": [{ "uri": "spotify:track:'.$track_id.'" }] }';
        $url = 'https://api.spotify.com/v1/users/'.Auth::id().'/playlists/'.$playlist_id.'/tracks';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER,array("Authorization: Bearer ".Auth::user()->access_token));
        $result = curl_exec($ch);
        curl_close($ch);
        //var_dump($result);
        error_log("removing track ".$track_id." from playlist ".$playlist_id);
    }
    public function jsNative()
    {
        $data['token']=Auth::user()->access_token;
        return View::make('v2',compact('data'));

    }

}
