<?php

class SummaryController extends BaseController 
{

    /**
    *
    */
    public function groupByContextByDayAndPeriod( $day, $period )
    {
        $dayAsTime = strtotime( $day );

        if ( $period == 'week' )
            $periodConverted = 'last monday';
        else if ( $period == 'month' )
            $periodConverted = '-1 month';
        else
            $periodConverted = 'today';

        $sum = DB::table( 'summary_by_context as s' )
            ->select( 's.context as prefLabel', DB::raw( 'sum( s.time_spent ) as total_time_spent' ) )
            ->where( 's.user_id', Auth::user()->id )
            ->where( 's.day', '>=', date( 'Y-m-d', strtotime( $periodConverted, $dayAsTime ) ) )
            ->where( 's.day', '<=', $day )
            ->groupBy( 's.context' )
            ->orderBy( 'total_time_spent', 'desc' )
            ->get();

        return View::make( 'ajax.summary' )
            ->with( 'summary', $sum )
            ->with( 'today', $day )
            ->with( 'option', $period );
    }

    /**
    *
    */
    public function forWeekGroupByDaysAndContexts( $day )
    {
        $dayAsTime = strtotime( $day );

        $sum = DB::table( 'tisheets' )
            ->join( 'contexts', 'tisheets.context_id', '=', 'contexts.id' )
            ->select( 'tisheets.day', DB::raw( 'sum( tisheets.time_spent ) as time_spent' ), 'contexts.prefLabel' )
            ->where( 'tisheets.user_id', Auth::user()->id )
            ->where( 'tisheets.day', '>=', date( 'Y-m-d', strtotime( 'last monday', $dayAsTime ) ) )
            ->where( 'tisheets.day', '<=', $day )
            ->groupBy( 'tisheets.day' )
            ->groupBy( 'contexts.prefLabel' )
            ->get();

        return View::make( 'ajax.summary-by-day' )
            ->with( 'summary', $sum )
            ->with( 'today', $day );
	}

    /**
    *
    */
    public function forWeekGroupByDaysAndContextsByContext( $day, $context )
    {
        $dayAsTime = strtotime( $day );

        // total time spent
        $tts = Input::get( 'tts' );

        $sum = DB::table( 'summary_by_context as s' )
            ->select( 's.day', 's.time_spent', 's.context', 's.subContext' )
            ->where( 's.user_id', Auth::user()->id )
            ->where( 's.context', '#'. $context )
            ->where( 's.day', '>=', date( 'Y-m-d', strtotime( 'last monday', $dayAsTime ) ) )
            ->where( 's.day', '<=', $day )
            ->get();

        return View::make( 'ajax.summary-by-day-and-context' )
            ->with( 'summary', $sum )
            ->with( 'tts', $tts )
            ->with( 'context', '#'. $context );
    }
}