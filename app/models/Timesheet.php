<?php

class Timesheet extends Eloquent 
{
	public function user()
	{
		return $this->belongsTo( 'User', 'user_id' );
	}

    /**
    *
    */
    public static function getNonEmptyByDay( $day )
    {
        $timesheet = Timesheet::where( 'day', $day )->where( 'user_id', Auth::user()->id )->first();

        if ( isset( $timesheet ) )
            return $timesheet;

        $timesheet = new Timesheet();
        $timesheet->user()->associate( Auth::user() );
        $timesheet->day = $day;

        $timesheet->save();

        return $timesheet;
    }
}