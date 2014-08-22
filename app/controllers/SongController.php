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
        $data['songstring']=implode(',', $songArray);
        $data['listtitle']="derp";
		return View::make('songs',compact('data'));
	}
    public function addFromForm()
    {

        $text = trim(Input::get('text'));
        $textAr = explode("\n", $text);
        $textAr = array_filter($textAr, 'trim'); // remove any extra \r characters left behind

        foreach ($textAr as $line)
        {

            $id = substr($line,30);
            $song = Songs::firstOrCreate(array('song_id' => $id, 'tags'=>0));

        }
        return Redirect::action('SongController@test');

        //var_dump(Input::get('text'));
    }

}
