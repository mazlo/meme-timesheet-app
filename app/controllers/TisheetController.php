<?php

class TisheetController extends BaseController 
{

    /**
    *   Retrieves all tisheets from the database.
    */
    public function index( $day = '' )
    {
        if ( empty( $day ) || $day == 'today' )
            $day = date( 'Y-m-d', time() );
        
        $tisheets = Tisheet::where( 'day', $day )
            ->where( 'user_id', Auth::user()->id )
            ->orderBy( 'index_' )
            ->orderBy( 'created_at' )
            ->get();

        $timesheet = Timesheet::where( 'day', $day )
            ->where( 'user_id', Auth::user()->id )
            ->first();

        $timeline = SummaryController::byDayAndPeriodGroupByContext( $day, 'today' )->get();

        $oneDay = 60*60*24;

        return View::make( 'index' )
            ->with( 'tisheets', $tisheets )
            // for yesterday substract 24h of the day given
            ->with( 'yesterday', date( 'Y-m-d', strtotime( $day ) - $oneDay ) )
            ->with( 'today', $day )
            ->with( 'todayForReal', $day === date( 'Y-m-d', time() ) )
            // for tomorrow add 24h of the day given
            ->with( 'tomorrow', date( 'Y-m-d', strtotime( $day ) + $oneDay ) )
            ->with( 'timeline', $timeline )
            ->with( 'timesheet', $timesheet );
    }

    /**
    *
    */
    public function timeline( $day )
    {
        $timeline = SummaryController::byDayAndPeriodGroupByContext( $day, 'today' )->get();

        return View::make( 'ajax.timeline' )->with( 'timeline', $timeline );
    }

    /**
    *
    */
    public function autocomplete( $day )
    {
        $dayAsTime = strtotime( $day );

        // TODO ZL distinct
        $tisheets = Tisheet::where( 'user_id', Auth::user()->id )
            ->where( 'day', '>=', date( 'Y-m-d', strtotime( '-1 month', $dayAsTime ) ) )
            ->where( 'day', '<=', $day )
            ->whereNotNull( 'description' )
            ->orderBy( 'updated_at', 'desc' )
            ->groupBy( 'description' )
            ->get();

        return View::make( 'ajax.tisheets-autocomplete' )
            ->with( 'tisheets', $tisheets );
    }

    /**
    *   Adds a new tisheet to the database.
    */
    public function add( $day )
    {
        $tisheet = new Tisheet();

        $tisheet->user()->associate( Auth::user() );
        $tisheet->day = $day;

		// save tisheet to obtain an id
		$tisheet->save();
		
        if ( Input::has( 'vl' ) )
        {
            $value = Input::get( 'vl' );
            
            TisheetController::syncContexts( $tisheet, $value );
            TisheetController::syncTime( $tisheet, $value );
            TisheetController::syncWords( $tisheet, $value );

            $tisheet->description = $value;
        }
        
        $tisheet->save();

        return Response::json( array( 
            'status' => 'ok', 
            'action' => 'add', 
            'id' => $tisheet->id, 
            'time' => $tisheet->time_start,
            'context' => $tisheet->context ? substr( $tisheet->context->prefLabel, 1 ) : null
        ) );
    }

    /**
    *
    */
    public function update( $day, $id )
    {
        // add 

        if ( $id == 'undefined' )
            return $this->add( $day );

        // update
        
        $tisheet = Tisheet::where( 'id', '=', $id )
            ->where( 'user_id', Auth::user()->id )
            ->first();

        if ( Input::has( 'vl' ) )
        {
            $value = Input::get( 'vl' );
            
			TisheetController::syncContexts( $tisheet, $value );
            TisheetController::syncTime( $tisheet, $value );
            TisheetController::syncWords( $tisheet, $value );

            $tisheet->description = $value;
        }
        
        // update time spent
        else if ( Input::has( 'ts' ) )
        {
            $tisheet->time_spent = Input::get( 'ts' );
            $tisheet->time_start = Input::get( 'tm' );
        }
        
        // update planned flag
        else if ( Input::has( 'pl' ) )
            $tisheet->planned = Input::get( 'pl' ) == 'true' ? true : false;

        // update day of tisheet -> move
        else if ( Input::has( 'mv' ) )
        {
            $dayAsTime = strtotime( $day );
            $tomorrow = date( 'Y-m-d', strtotime( 'tomorrow', $dayAsTime ) );
            
            $tisheet->day = $tomorrow;
        }
        
        // note of tisheet will be updated via NoteController

        $tisheet->save();

        return Response::json( array( 
            'status' => 'ok', 
            'action' => 'update', 
            'id' => $tisheet->id, 
            'time' => $tisheet->time_start,
            'context' => $tisheet->context ? substr( $tisheet->context->prefLabel, 1 ) : null
        ) );
    }

    /**
    *
    */
    public function delete( $day, $id )
    {
        $tisheet = Tisheet::find( $id );
        
        if ( $tisheet->note )
            $tisheet->note->delete();
        
		$tisheet->subContexts()->detach();
        $tisheet->delete();

        return 'true';
    }

	/**
	 * the return value of this function is an array of Context-ids
	 * in preparation for the association of Contexts to sub-Contexts
	 * 
	 * @param type $value
	 * @return type
	 */
	public static function parseContexts( $value )
	{
		return array_map( function( $word )
		{
			// return an array of Context-ids
			$context = Context::where( 'prefLabel', $word )
                ->where( 'user_id', Auth::user()->id )
                ->first();

			// create new and associate
			if ( empty( $context ) )
			{                
				$context = new Context();
				$context->prefLabel = $word;
                $context->user()->associate( Auth::user() );
				$context->save();
			}

			// 2nd dimension consists of foreign-key ids
			return $context->id;
		},  
			// form an array of Contexts that was parsed from the text
			array_filter( explode( ' ', $value ), function( $word )
			{
                if( empty( $word ) || strlen( $word ) == 1 )
                    return false;

				if( $word{0} == '#' )
					return true;

				return false;
			})
		);
	}

    public static function parseWords( $value )
    {
        return array_map( function( $value )
        {
            $word = Word::where( 'value', $value )
                ->where( 'user_id', Auth::user()->id )
                ->first();

            if ( empty( $word ) )
            {
                $word = new Word();
                $word->value = $value;
                $word->user()->associate( Auth::user() );
                $word->save();
            }

            return $word->id;
        },  
            array_filter( explode( ' ', $value ), function( $word )
            {
                if ( empty( $word ) || strlen( $word ) == 1 )
                    return false;

                if ( $word{0} == '#' )
                    return false;

                // TODO normalize sentence, filter filling words like 'in', 'from', 'with', etc.

                return true;
            })
        );
    }

    /**
     * Parses the given value for Words. Each Word will be associated with the
	 * given Tisheet and Context then.
     * 
     * @param type $tisheet
     * @param type $value
     * @return type
     */
    public static function syncWords( &$tisheet, $value ) 
    {
        $words = TisheetController::parseWords( $value );

        $wordsToSync = array_map( function( $word ) use ($tisheet)
        {
			$context = empty( $tisheet->context_id ) ? '0' : $tisheet->context_id;
            
			return array( 
				'id' => $tisheet->id . $word,
				'context_id' => $context
            );
        }, $words );
        
        $tisheet->words()->sync( array_combine( $words, $wordsToSync ) );
    }

    /**
     * Parses the given value for Time-statements. Time-statements are identified
	 * by the @ symbol.
	 *
	 * @param type $tisheet
	 * @param type $value
	 * @return type
    */
    public static function syncTime( &$tisheet, $value )
    {
        $time = array_filter( explode( ' ', $value ), function( $word )
        {
            if( empty( $word ) || strlen( $word ) == 1 )
                return false;

            if( $word{0} == '@' )
                return true;

            return false;
        });

        $timeStart = reset( $time );

        if ( $timeStart )
            $tisheet->time_start = substr( $timeStart, 1 );
    }

	/**
	 * Parses the given value for Contexts. Contexts are identified by the # symbol.
	 * Takes the first Context as first level Context.
	 * 
	 * @param type $tisheet
	 * @param type $value
	 * @return type
	 */
	public static function syncContexts( &$tisheet, $value ) 
	{
		$contexts = TisheetController::parseContexts( $value );
		
		// reset first level Concept if it's empty
		if ( count( $contexts ) == 0 ) 
		{
			$tisheet->context_id = null;
			
			return;
		}

		// assign first level Concept to Tisheet
		$mainContext = Context::find( reset( $contexts ) );
		$tisheet->context()->associate( $mainContext );
	}

}
