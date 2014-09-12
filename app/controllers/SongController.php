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
        $params=array(
            "client_id" => "d27efb143d5d4719959e523a5cbfa3c4",
            "response_type" => "code",
            "redirect_uri"=>URL::to('/auth/spotify/callback'),
            "show_dialog"=>"true",
            "scope"=>implode(' ',array('user-read-private', 'user-read-email','user-library-read','user-library-modify','playlist-modify','playlist-modify-public','playlist-modify-private','playlist-read-private'))
        );
        return Redirect::away("https://accounts.spotify.com/authorize?".http_build_query($params) . "\n");
    }
    public function spotifyCallback($code="0",$type="initial")
    {
        $ch = curl_init();
        switch($type){
            case "initial":
                error_log($code);
                if($code=="0")
                {
                    $code=$_GET['code'];
                }

                $fields = array(
                    "client_id" => "d27efb143d5d4719959e523a5cbfa3c4",
                    "client_secret"=>"5a37334c1e994ae0ba07f6cac6366233",
                    "grant_type" => "authorization_code",
                    "redirect_uri"=>URL::to('/auth/spotify/callback'),
                    "code"=>$code
                );
            break;
            case "refresh":
                error_log("going to try to refresh token");
                $fields = array(
                    "refresh_token" => Auth::user()->refresh_token,
                    "grant_type" => "refresh_token"
                );
                curl_setopt($ch, CURLOPT_HTTPHEADER,array("Authorization: Basic ".base64_encode("d27efb143d5d4719959e523a5cbfa3c4:5a37334c1e994ae0ba07f6cac6366233")));
                break;
        }

        //post request
        $fields_string="";
        $url = 'https://accounts.spotify.com/api/token';

        foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        rtrim($fields_string, '&');

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


//        ob_start();
//        print_r($data);
//        $contents = ob_get_contents();
//        ob_end_clean();
//        error_log($contents);


        $user=User::find($data['id']);
        if($user==null)
        {
            error_log("registration not complete");
            $newUser = User::firstOrCreate(
                array(
                    'uid' => $data['id'],
                    'display_name'=>$data['display_name'],
                    'email'=>$data['email']
                )
            );
            $newUser->access_token=$callbackResult['access_token'];
            $newUser->refresh_token=$callbackResult['refresh_token'];
            $newUser->save();
            Auth::login($newUser);
        }
        else
        {
            error_log("going to update and login");
            $user->access_token=$callbackResult['access_token'];
            if(isset($callbackResult['refresh_token'])){$user->refresh_token=$callbackResult['refresh_token'];}
            $user->save();
            Auth::login($user);
        }
        error_log("herg");
        switch($type){
            case "initial":
                error_log("initial");
                return Redirect::action('SongController@getSpotifyProfile');
                break;
            case "refresh":
                error_log("refresh");
                return;
                break;

        }

    }
    public function getSpotifyProfile()
    {

        if(!Auth::check())
        {
            return Redirect::action('SongController@spotifyAuth');
        }
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
        Clockwork::info($data);
        $playlistNames=array();
        for($x=0; $x<$data['limit']; $x++)
        {
            if($data['items'][$x]['owner']['id']==Auth::user()->uid)
            {
            $id=$data['items'][$x]['id'];
                $this->er($id);
            $numTracks= $data['items'][$x]['tracks']['total'];
                $rc->request("https://api.spotify.com/v1/users/".Auth::user()->uid."/playlists/".$id."/tracks?limit=100");
                if($numTracks>100)
                {
                    $next=100;
                    while($next<$numTracks)
                    {
                        $rc->request("https://api.spotify.com/v1/users/".Auth::user()->uid."/playlists/".$id."/tracks?offset=".$next."&limit=100");
                        $next=$next+100;
                        $this->er($next);
                    }
                    //$rc->request("https://api.spotify.com/v1/users/".Auth::user()->uid."/playlists/".$id."/tracks?limit=3?offset=100");
                    $offset=$numTracks-100;


                }
            $playlistNames[$id]=$data['items'][$x]['name'];
            }
        }

        $rc->options = array(CURLOPT_HTTPHEADER => array("Authorization: Bearer ".Auth::user()->access_token), CURLOPT_RETURNTRANSFER => 1);
        $test = $rc->execute();
        Clockwork::info($global_response);

        //$this->er($global_response);
        foreach($global_response as $eachPlaylist)
        {
            $playlistID=(substr($eachPlaylist['href'],(strpos($eachPlaylist['href'], "/playlists/")+11),22));
            $playlistName=$playlistNames[$playlistID];
            if($eachPlaylist['total']<$eachPlaylist['limit'])
            {
                $stopAt=$eachPlaylist['total'];
            }
            else
            {
                $stopAt=$eachPlaylist['limit'];
            }
            for($y=0; $y<$stopAt; $y++)
            {

                $temp[$eachPlaylist['items'][$y]['track']['id']]['tags'][]=array("tagname"=>$playlistName,"playlist_id"=>$id);
                $temp[$eachPlaylist['items'][$y]['track']['id']]['name']=$eachPlaylist['items'][$y]['track']['name'];
                $temp[$eachPlaylist['items'][$y]['track']['id']]['artists']=implode(', ', array_column($eachPlaylist['items'][$y]['track']['artists'], 'name'));
                $taginfo=array('name'=>$playlistName,'playlist_id'=>$playlistID,'user_id'=>Auth::user()->id);
                if(!in_array($taginfo,$tagsAndPlaylistIDs,true))
                {
                    array_push($tagsAndPlaylistIDs,$taginfo);
                }

            }


        }
        $affectedRows = Tags::where('user_id', '=', Auth::user()->id)->delete();
        function compare_name($a, $b)
        {
            return strnatcmp($a['name'], $b['name']);
        }

        // sort alphabetically by name
        usort($tagsAndPlaylistIDs, 'compare_name');

        Tags::insert($tagsAndPlaylistIDs);
        $songData=$temp;
        $data['username']=Auth::id();
        Clockwork::info($tagsAndPlaylistIDs);
        return View::make('main',compact('songData','tagsAndPlaylistIDs','data'));

    }

    function request_callback($response, $info) {
        global $global_response;
        $global_response[]=json_decode($response,true);
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
        $result=json_decode(curl_exec($ch),true);
        curl_close($ch);
        //var_dump($result);
        error_log("adding track ".$track_id." to playlist ".$playlist_id);

        ob_start();
        print_r($result);
        $contents = ob_get_contents();
        ob_end_clean();
        error_log($contents);

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
        $result=json_decode(curl_exec($ch),true);
        curl_close($ch);
        //var_dump($result);
        error_log(Auth::user()->access_token);
        if(isset($result['error'])){error_log("need to update token");
            $this->spotifyCallback(0,"refresh");

            error_log("trying again!");
            $this->removeTrackFromPlaylist($playlist_id,$track_id);}
        error_log("removing track ".$track_id." from playlist ".$playlist_id);
        ob_start();
        print_r($result);
        $contents = ob_get_contents();
        ob_end_clean();
        error_log($contents);

    }
    public function er($data)
    {
        ob_start();
        print_r($data);
        $contents = ob_get_contents();
        ob_end_clean();
        error_log($contents);
    }

}
