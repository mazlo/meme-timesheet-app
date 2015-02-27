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
        
        $tisheet = Tisheet::where( 'day', $day )
            ->where( 'user_id', Auth::user()->id )
            ->orderBy( 'index_' )
            ->orderBy( 'created_at' )
            ->get();

        $timeline = SummaryController::byDayAndPeriodGroupByContext( $day, 'today' )->get();

        $oneDay = 60*60*24;

        return View::make( 'index' )
            ->with( 'tisheets', $tisheet )
            // for yesterday substract 24h of the day given
            ->with( 'yesterday', date( 'Y-m-d', strtotime( $day ) - $oneDay ) )
            ->with( 'today', $day )
            // for tomorrow add 24h of the day given
            ->with( 'tomorrow', date( 'Y-m-d', strtotime( $day ) + $oneDay ) )
            ->with( 'timeline', $timeline );
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
            
            // sync Contexts of Tisheet
            TisheetController::syncContexts( $tisheet, $value );
            TisheetController::syncTime( $tisheet, $value );

            $tisheet->description = $value;
        }
        
        $tisheet->save();

        return Response::json( array( 'status' => 'ok', 'action' => 'add', 'id' => $tisheet->id ) );
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
        
        $tisheet = Tisheet::where( 'id', '=', $id )->where( 'user_id', Auth::user()->id )->first();

        if ( Input::has( 'vl' ) )
        {
            $value = Input::get( 'vl' );
            
            // sync Contexts of Tisheet
            TisheetController::syncContexts( $tisheet, $value );
            TisheetController::syncTime( $tisheet, $value );

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
        
        // note of tisheet will be updated via NoteController

        $tisheet->save();

        return Response::json( array( 'status' => 'ok', 'action' => 'update', 'id' => $tisheet->id, 'tm' => $tisheet->time_start ) );
    }

    /**
    *
    */
    public function updatePositions( $day )
    {
        $tids = Input::get( 'tids' );

        for( $i=0; $i<count( $tids ); $i++ )
        {
            // TODO ZL restrict to user
            $tisheet = Tisheet::where( 'id', $tids[$i] )->first();
            
            $tisheet->index_ = $i;
            $tisheet->save();
        }

        return 'true';
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
			$context = Context::where( 'prefLabel', $word )->first();

			// create new and associate
			if ( empty( $context ) )
			{                
				$context = new Context();
				$context->prefLabel = $word;
				$context->save();
			}

			// 2nd dimension consists of foreign-key ids
			return $context->id;
		},  
			// from an array of Contexts that was parsed from the text
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

    /**
    *
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
	 * Parses the given value for Contexts. Takes the first Context as first 
	 * level Context. Takes the rest as subContexts of the fiven Tisheet.
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
			$tisheet->subContexts()->detach();
			
			return;
		}

		// assign first level Concept to Tisheet
		$mainContext = Context::find( reset( $contexts ) );
		$tisheet->context()->associate( $mainContext );

		// cut off the first element -> becomes the list of subConcepts
		$subContexts = array_slice( $contexts, 1 );
		
		// syncs a list of existing and new subContexts
		TisheetController::syncSubContexts( $tisheet, $subContexts );
	}
	
	/**
	 * Composes an array of ids as a preparation for syncing subContexts.
	 * 
	 * @param type $tisheet
	 * @param type $editedSubContexts
	 */
	public static function syncSubContexts( &$tisheet, &$editedSubContexts )
	{
		$editedSubContexts = array_map( function( $subContext ) use ($tisheet)
		{
			return array( 
				// the key is a composition of all three foreign keys
				'id' => $tisheet->context->id.$subContext.$tisheet->id,
				'context_id' => $tisheet->context_id,
				'subContext_id' => $subContext,
				'tisheet_id' => $tisheet->id
			);
		}, $editedSubContexts );
		
		$tisheet->subContexts()->sync( $editedSubContexts );
	}
}
