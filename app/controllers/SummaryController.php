<?php

class SummaryController extends BaseController 
{
    /**
    *
    */
    public function sameAs( $day, $tid )
    {
        $tisheet = Tisheet::where( 'user_id', Auth::user()->id )->where( 'id', $tid )->first();

        if ( empty( $tisheet ) )
            return Response::json( array( 'error' => 'no tisheet with this id '. $tid ) );

        $sameAsTisheets = DB::table( 'tisheets as ts ')
            ->join( 'notes AS ns', 'ts.id', '=', 'ns.tisheet_id' )
            ->select( 'ts.*', 'ns.content' )
            ->where( 'ts.user_id', Auth::user()->id )
            ->where( 'ts.description', $tisheet->description )
            ->whereNotNull( 'ns.content' )
            ->orderBy( 'ts.created_at', 'desc' )
            ->get();

        return View::make( 'ajax.summary-same-as' )
            ->with( 'tisheets', $sameAsTisheets )
            ->with( 'refTisheet', $tisheet );
    }

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

        return DB::table( 'time_spent_in_contexts AS ctx' )
            ->select( 'ctx.context_prefLabel', 'ctx.context_id', DB::raw( 'SUM( ctx.time_spent ) AS total_time_spent' ) )
            ->where( 'ctx.user_id', Auth::user()->id )
            ->where( 'ctx.day', '>=', date( 'Y-m-d', strtotime( $startDate, $relativeDayAsTime ) ) )
            ->where( 'ctx.day', '<=', $day )
            ->groupBy( 'ctx.context_prefLabel' );
    }

    /**
    *   This method handles requests for
    *   -> show summary by contexts -> current $period
    */
    public function groupbyContext( $day, $period )
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
    public function groupbyContextFilterContext( $day, $period, $context )
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

        $sum = DB::table( 'time_spent_in_contexts as s' )
            ->select( 's.day', 's.time_spent', 's.context_prefLabel' )
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