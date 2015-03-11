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

	    // update today's topic
	    else if ( Input::has( 'gl' ) )
	    {
	    	$timesheet = Timesheet::where( 'day', $day )->where( 'user_id', Auth::user()->id )->first();

	    	if ( empty( $timesheet ) )
	    	{
	    		$timesheet = new Timesheet();
	    		$timesheet->user()->associate( Auth::user() );

	    		$timesheet->day = $day;
	    	}

	    	$timesheet->topic = Input::get( 'gl' );

	    	$timesheet->save();
	    }

        return 'true';
    }

    /**
    *
    */
    public function delete( $day )
    {
    	// TODO ZL

    	return 'true';
    }

}