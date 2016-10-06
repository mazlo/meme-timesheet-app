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
    *   This method handles requests for
    *   -> show summary by contexts -> current $period
    */
    public function groupby_context( $day, $period )
    {
        $sum = SummaryController::byDayAndPeriodGroupByContext( $day, $period )->orderBy( 'total_time_spent', 'desc' )->get();

        return View::make( 'ajax.summary.groupby-context' )
            ->with( 'summary', $sum )
            ->with( 'today', $day )
            ->with( 'option', $period );
    }

    /**
     *
     */
    public static function byDayAndPeriodGroupByContext( $day, $period )
    {
        $relative_day_as_time = strtotime( $day );

        $date_literal = TisheetUtils::determine_date_literal( $period, $relative_day_as_time );

        return DB::table( 'time_spent_in_contexts AS s' )
            ->select( 's.context_prefLabel', 's.context_id', DB::raw( 'SUM( s.time_spent ) AS total_time_spent' ) )
            ->where( 's.user_id', Auth::user()->id )
            ->where( 's.day', '>=', date( 'Y-m-d', strtotime( $date_literal, $relative_day_as_time ) ) )
            ->where( 's.day', '<=', $day )
            ->groupBy( 's.context_prefLabel' );
    }

    /**
    *
    */
    public function forWeekGroupByDaysAndContexts( $day )
    {
        $day_as_time = strtotime( $day );

        $sum = DB::table( 'tisheets' )
            ->join( 'contexts', 'tisheets.context_id', '=', 'contexts.id' )
            ->select( 'tisheets.day', DB::raw( 'sum( tisheets.time_spent ) as time_spent' ), 'contexts.prefLabel' )
            ->where( 'tisheets.user_id', Auth::user()->id )
            ->where( 'tisheets.day', '>=', date( 'Y-m-d', strtotime( 'last monday', $day_as_time ) ) )
            ->where( 'tisheets.day', '<=', $day )
            ->groupBy( 'tisheets.day' )
            ->groupBy( 'contexts.prefLabel' )
            ->get();

        return View::make( 'ajax.summary.groupby-day' )
            ->with( 'summary', $sum )
            ->with( 'today', $day );
	}

    /**
    *   This method handles requests for
    *   -> show summary by contexts -> current $period -> Main Context $context.
    */
    public function groupby_context_filter_context( $day, $period, $cid )
    {
        $relative_day_as_time = strtotime( $day );

        $date_literal = TisheetUtils::determine_date_literal( $period, $relative_day_as_time );

        // total time spent
        $tts = Input::get( 'tts' );

        // query time spent per context
        $sum = DB::table( 'time_spent_in_contexts AS s' )
            ->select( 's.day', 's.time_spent', 's.context_id', 's.context_prefLabel', 's.description' )
            ->where( 's.user_id', Auth::user()->id )
            ->where( 's.context_id', $cid )
            ->where( 's.day', '>=', date( 'Y-m-d', strtotime( $date_literal, $relative_day_as_time ) ) )
            ->where( 's.day', '<=', $day )
            ->orderBy( 's.day', 'desc' )
            ->get();

        $context_id = count( $sum ) > 0 ? $sum[0]->context_id : 'id';
        $context_prefLabel = count( $sum ) > 0 ? $sum[0]->context_prefLabel : 'no context';

        // get words mentioned in contexts
        $words = array_reduce( $sum, function( $words, $current_tisheet )
        {
            if ( empty( $words ) )
                $words = array();

            $tisheetWords = TisheetUtils::filter_controls( explode( ' ', $current_tisheet->description ) );

            foreach ( $tisheetWords as $word )
            {
                $words[$word] = $word;
            }

            return $words;
        });

        return View::make( 'ajax.summary.groupby-context-filter-context' )
            ->with( 'today', $day )
            ->with( 'option', $period )
            ->with( 'summary', $sum )
            ->with( 'words', $words )
            ->with( 'tts', $tts )
            ->with( 'context', $context_prefLabel )
            ->with( 'context_id', $context_id );
    }

    /**
     * This method handles requests for
     * -> show summary by context -> current $period -> $context -> list of words
     *  
     */
    public function groupby_context_filter_context_filter_word( $day, $period, $cid )
    {
        $relative_day_as_time = strtotime( $day );

       $date_literal = TisheetUtils::determine_date_literal( $period, $relative_day_as_time );

        // total time spent
        $tts = Input::get( 'tts' );
        $andOperator = Input::get( 'and' ) == 'and' ? true : false;

        // query time spent per context
        $sum = DB::table( 'time_spent_in_contexts AS s' )
            ->select( 's.day', 's.time_spent', 's.context_id', 's.context_prefLabel', 's.description' )
            ->where( 's.user_id', Auth::user()->id )
            ->where( 's.context_id', $cid )
            ->where( 's.day', '>=', date( 'Y-m-d', strtotime( $date_literal, $relative_day_as_time ) ) )
            ->where( 's.day', '<=', $day )
            ->orderBy( 's.day', 'desc' )
            ->get();

        $context_id = count( $sum ) > 0 ? $sum[0]->context_id : 'id';
        $context_prefLabel = count( $sum ) > 0 ? $sum[0]->context_prefLabel : 'no context';

        $filtered_sum = SummaryController::filter_selected_words( Input::get( 'ws' ), $sum, $andOperator );

        return View::make( 'ajax.summary.groupby-context-filter-words' )
            ->with( 'today', $day )
            ->with( 'option', $period )
            ->with( 'summary', $filtered_sum )
            ->with( 'tts', $tts )
            ->with( 'context', $context_prefLabel )
            ->with( 'context_id', $context_id );
    }

    /**
     * Filters words selected by the user from the obtained query results.
     *
     * @param type $words
     * @param type $sum
     * @param type $andOperator
     * @return type
     */
    public static function filter_selected_words( $words, &$sum, $andOperator = true )
    {
        $wordsToFilter = explode( ',', $words );

        if ( empty( $words ) || count( $wordsToFilter ) == 0 )
            return $sum;

        // for each tisheet in the sum, filter all words with respect to the operator (and,or)
        return array_filter( $sum, function( $elem ) use ( $wordsToFilter, $andOperator )
        {
            // ignore time-operator
            $wordsInTisheet = array_filter( explode( ' ', $elem->description ), function( $wordInTisheet )
            {
                if ( preg_match( '/@[0-9:]+/', $wordInTisheet ) )
                    return false;
                
                return true;
            } );

            // indicates whether this tisheet should be taken into the result
            $criteriaMet = TisheetUtils::filter_words( $wordsInTisheet, $wordsToFilter, $andOperator );

            if ( $criteriaMet )
                return true;

            return false;
        });
    }
}