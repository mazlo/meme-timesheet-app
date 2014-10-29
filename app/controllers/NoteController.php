<?php

class NoteController extends BaseController 
{

	/**
	*
	*/
	public function update( $day, $tid )
	{
		$tisheet = Tisheet::find( $tid );

		// check if operating user ids match
		if ( $tisheet->user->id != Auth::user()->id )
			return 'false';

        $note = $tisheet->note;

        if ( empty( $tisheet->note ) )
            $note = new Note();
        
        $note->content = Input::get( 'nt' );

        $tisheet->note()->save( $note );

        return 'true';
	}

	/**
	*
	*/
	public function delete( $day, $tid )
	{
		$tisheet = Tisheet::find( $tid );

		// check if operating user ids match
		if ( $tisheet->user->id != Auth::user()->id )
			return 'false';

        $tisheet->note->delete();

        return 'true';
	}
}