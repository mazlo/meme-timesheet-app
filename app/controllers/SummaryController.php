<?php

class SummaryController extends BaseController 
{

    /**
    *
    */
    public function summaryForTodayGroupByContexts( $day )
    {
        $sum = DB::table( 'tisheets' )
            ->join( 'contexts', 'tisheets.context_id', '=', 'contexts.id' )
            ->select( 'contexts.prefLabel', DB::raw( 'sum( tisheets.time_spent ) as total_time_spent' ) )
            ->where( 'tisheets.user_id', Auth::user()->id )
            ->where( 'tisheets.day', $day )
            ->groupBy( 'contexts.prefLabel' )
            ->get();

        return View::make( 'ajax.summary' )
            ->with( 'summary', $sum )
            ->with( 'today', $day )
            ->with( 'option', 'today' );
    }

    /**
    *
    */
    public function summaryForWeekGroupByContexts( $day )
    {
        $dayAsTime = strtotime( $day );

        $sum = DB::table( 'tisheets' )
            ->join( 'contexts', 'tisheets.context_id', '=', 'contexts.id' )
            ->select( 'contexts.prefLabel', DB::raw( 'sum( tisheets.time_spent ) as total_time_spent' ) )
            ->where( 'tisheets.user_id', Auth::user()->id )
            ->where( 'tisheets.day', '>', date( 'Y-m-d', strtotime( '-1 week', $dayAsTime ) ) )
            ->where( 'tisheets.day', '<=', $day )
            ->groupBy( 'contexts.prefLabel' )
            ->get();

        return View::make( 'ajax.summary' )
            ->with( 'summary', $sum )
            ->with( 'today', $day )
            ->with( 'option', 'week' );
    }

    /**
    *
    */
    public function summaryForWeekGroupByDaysAndContexts( $day )
    {
        $dayAsTime = strtotime( $day );

        // select day, time_spent, c.prefLabel from tisheets t join contexts c on t.context_id=c.id group by day, c.preflabel;
        $sum = DB::table( 'tisheets' )
            ->join( 'contexts', 'tisheets.context_id', '=', 'contexts.id' )
            ->select( 'tisheets.day', DB::raw( 'sum( tisheets.time_spent ) as time_spent' ), 'contexts.prefLabel' )
            ->where( 'tisheets.user_id', Auth::user()->id )
            ->where( 'tisheets.day', '>', date( 'Y-m-d', strtotime( '-1 week', $dayAsTime ) ) )
            ->where( 'tisheets.day', '<=', $day )
            ->groupBy( 'tisheets.day' )
            ->groupBy( 'contexts.prefLabel' )
            ->get();

        return View::make( 'ajax.summary-by-day' )->with( 'summary', $sum );
	}
}