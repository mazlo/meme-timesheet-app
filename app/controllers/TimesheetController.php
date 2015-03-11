<?php

class TimesheetController extends BaseController 
{

    /**
    *
    */
    public function update( $day )
    {
    	// update positions of tisheets
    	if ( Input::has( 'tids' ) )
    	{
	        $tids = Input::get( 'tids' );

	        for( $i=0; $i<count( $tids ); $i++ )
	        {
	            // TODO ZL restrict to user
	            $tisheet = Tisheet::where( 'id', $tids[$i] )->first();
	            
	            $tisheet->index_ = $i;
	            $tisheet->save();
	        }
	    }

        return 'true';
    }
}