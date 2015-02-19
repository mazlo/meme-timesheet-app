<?php

class SummaryController extends BaseController 
{
    /**
    *
    */
    public static function byDayAndPeriodGroupByContext( $day, $period )
    {
        $relativeDayAsTime = strtotime( $day );
        
        if ( $period == 'week' )
            $startDate = 'last monday';
        else if ( $period == 'month' )
            $startDate = 'first day of '. date( 'M', $relativeDayAsTime );
        else if ( $period == 'year' )
            $startDate = 'first day of Jan';
        else
            $startDate = 'today';

        return DB::table( 'tisheets AS t' )->join( 'contexts AS c', 'c.id', '=', 't.context_id' )
            ->select( 'c.prefLabel', DB::raw( 'SUM( t.time_spent ) AS total_time_spent' ) )
            ->where( 't.user_id', Auth::user()->id )
            ->where( 't.day', '>=', date( 'Y-m-d', strtotime( $startDate, $relativeDayAsTime ) ) )
            ->where( 't.day', '<=', $day )
            ->groupBy( 'c.prefLabel' );
    }

    /**
    *
    */
    public function groupByContextByDayAndPeriod( $day, $period )
    {
        $sum = SummaryController::byDayAndPeriodGroupByContext( $day, $period )->orderBy( 'total_time_spent', 'desc' )->get();

        return View::make( 'ajax.summary-groupby-context' )
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

        return View::make( 'ajax.summary-groupby-day' )
            ->with( 'summary', $sum )
            ->with( 'today', $day );
	}

    /**
    *   This method handles requests for
    *   -> show summary by contexts -> current $period -> Main Context $context.
    */
    public function byContextGroupByDaysGroupByContexts( $day, $period, $context )
    {
        $relativeDayAsTime = strtotime( $day );

        if ( $period == 'week' )
            $startDate = 'last monday';
        else if ( $period == 'month' )
            $startDate = 'first day of '. date( 'M', $relativeDayAsTime );
        else if ( $period == 'year' )
            $startDate = 'first day of Jan';
        else
            $startDate = 'today';

        // total time spent
        $tts = Input::get( 'tts' );

        $sum = DB::table( 'summary_by_context as s' )
            ->select( 's.day', 's.time_spent', 's.context', 's.subContext' )
            ->where( 's.user_id', Auth::user()->id )
            ->where( 's.context', '#'. $context )
            ->where( 's.day', '>=', date( 'Y-m-d', strtotime( $startDate, $relativeDayAsTime ) ) )
            ->where( 's.day', '<=', $day )
            ->get();

        return View::make( 'ajax.summary-groupby-context-filter-context' )
            ->with( 'summary', $sum )
            ->with( 'tts', $tts )
            ->with( 'context', '#'. $context );
    }
}