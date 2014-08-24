<?php
class SongController extends BaseController {

	public function test()
	{

        $songs = Songs::all();
        $songArray=array();
        foreach($songs as $eachSong)
        {
            array_push($songArray,$eachSong->song_id);
            //var_dump($eachSong->song_id);
        }
        //var_dump($songArray);
        $data['songs']=$songArray;
        $data['listtitle']="derp";

		return View::make('songs',compact('data'));
	}

    public function spotifyAuth()
    {
        $response = Unirest::get("https://accounts.spotify.com/authorize", array( "Accept" => "application/json" ),
            array(
                "client_id" => "d27efb143d5d4719959e523a5cbfa3c4",
                "response_type" => "code",
                "redirect_uri"=>'http://spotifytags/auth/spotify/callback',
                "show_dialog"=>"true",
                "scope"=>implode(' ',array('user-read-private', 'user-read-email','user-library-read','user-library-modify'))
            )
        );
        return Redirect::to($response->body->redirect);

    }
    public function spotifyCallback()
    {

        $fields_string="";
        $url = 'https://accounts.spotify.com/api/token';
        $fields = array(
            "client_id" => "d27efb143d5d4719959e523a5cbfa3c4",
            "client_secret"=>"5a37334c1e994ae0ba07f6cac6366233",
            "grant_type" => "authorization_code",
            "redirect_uri"=>'http://spotifytags/auth/spotify/callback',
            "code"=>$_GET['code']
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


        //register/login/save token to DB
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.spotify.com/v1/me");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER,array("Authorization: Bearer ".$callbackResult['access_token']));
        $data = json_decode(curl_exec($ch),true);
        curl_close($ch);
//        var_dump($data);
        Clockwork::info($data);
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


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.spotify.com/v1/users/".Auth::user()->uid."/playlists");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER,array("Authorization: Bearer ".Auth::user()->access_token));
        $data = json_decode(curl_exec($ch),true);
        curl_close($ch);


        $temp=array();
//        var_dump($data);
        //var_dump($data['items']['4']);
        for($x=0; $x<$data['limit']; $x++)
        {
            $playlistName=$data['items'][$x]['name'];
            $id=$data['items'][$x]['id'];
            if(substr($playlistName,0,3)=="st_")
            {
//                echo($playlistName."    ".$id);
//                echo("<br>--------</br>");


                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://api.spotify.com/v1/users/".Auth::user()->uid."/playlists/".$id);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($ch, CURLOPT_HTTPHEADER,array("Authorization: Bearer ".Auth::user()->access_token));
                $playlistData = json_decode(curl_exec($ch),true);
                curl_close($ch);

                for($y=0; $y<$playlistData['tracks']['total']; $y++)
                {
                    //var_dump($playlistData['tracks']['items'][$y]);
                    //var_dump($playlistData['tracks']['items'][$y]['track']['id']);
                    //var_dump($playlistData['tracks']['items'][$y]['track']['name']);
//                    echo('<hr>');
                    $temp[$playlistData['tracks']['items'][$y]['track']['id']]['tags'][]=$playlistName;
                    $temp[$playlistData['tracks']['items'][$y]['track']['id']]['name']=$playlistData['tracks']['items'][$y]['track']['name'];
                }


            }

        }
        //var_dump($temp);
        $songData=$temp;
        //var_dump(array(array('song1', 'id1',array('tag1')),array('song2','id2',array('tag1','tag2'))));
        return View::make('main',compact('songData'));

    }

}
