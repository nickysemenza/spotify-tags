<?php

class SongController extends BaseController {

	public function test()
	{

        $songs = Songs::all();
        $songArray=array();
        foreach($songs as $eachSong)
        {
            array_push($songArray,$eachSong->song_id);
            var_dump($eachSong->song_id);
        }
        var_dump($songArray);
        $data['songstring']=implode(',', $songArray);
        $data['listtitle']="derp";
		return View::make('songs',compact('data'));
	}

}
