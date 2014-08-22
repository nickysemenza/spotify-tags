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
        echo($response->raw_body);

       // header('Location: ' . );
//        exit;

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
    $result=json_decode($result,true);
        Session::put('access_token', $result['access_token']);

        curl_close($ch);

        echo("<br>-----<br>");
        echo(Session::get('access_token'));

    }
    public function getSpotifyProfile()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.spotify.com/v1/me");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER,array("Authorization: Bearer ".Session::get('access_token')));
        $data = json_decode(curl_exec($ch),true);
        curl_close($ch);
        var_dump($data);
    }

}
