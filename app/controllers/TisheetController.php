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

        $oneDay = 60*60*24;

        return View::make( 'index' )
            ->with( 'tisheets', $tisheet )
            // for yesterday substract 24h of the day given
            ->with( 'yesterday', date( 'Y-m-d', strtotime( $day ) - $oneDay ) )
            ->with( 'today', $day )
            // for tomorrow add 24h of the day given
            ->with( 'tomorrow', date( 'Y-m-d', strtotime( $day ) + $oneDay ) );
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
            
            // parse the text for Contexts
            // and assign them to current Tisheet
            TisheetController::syncContexts( $tisheet, TisheetController::parseContexts( $value ) );

            $startTime = Input::get( 'st' );

            // check if start time is already available
            if ( !empty( $startTime ) )
                $tisheet->start_time = $startTime;

            $tisheet->description = $value;
        }
        
        $tisheet->save();

        return $tisheet->id;
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
            
            // parse the text for Contexts
            // and assign them to current Tisheet
            TisheetController::syncContexts( $tisheet, TisheetController::parseContexts( $value ) );

            $startTime = Input::get( 'st' );
            
            // check if start time is already available
            if ( !empty( $startTime ) )
                $tisheet->start_time = $startTime;

            $tisheet->description = $value;
        }
        
        // update time spent
        else if ( Input::has( 'ts' ) )
            $tisheet->time_spent = Input::get( 'ts' );
        
        // update planned flag
        else if ( Input::has( 'pl' ) )
            $tisheet->planned = Input::get( 'pl' ) == 'true' ? true : false;
        
        // note of tisheet will be updated via NoteController

        $tisheet->save();

        return 'true';
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
        
        $tisheet->delete();

        return 'true';
    }

	/**
	 * the return value of this function is an array of Context-ids
	 * in preparation for the association of Contexts to sub-Contexts
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
			return array( 'id' => '', 'context_id' => '', 'subContext_id' => $context->id, 'tisheet_id' => '' );
		},  
			// from an array of Contexts that was parsed from the text
			array_filter( explode( ' ', $value ), function( $word )
			{
                if( empty( $word ) )
                    return false;

				if( $word{0} == '#' )
					return true;

				return false;
			})
		);
	}

	/**
	 * supplies the array of Contexts with new keys, from 0..length. 
	 * takes the first Context as main-Context and the rest as sub-Contexts.
	 *
	 */
	public static function syncContexts( &$tisheet, $contexts ) 
	{
		// reset association to a Context if it's empty
		if ( count( $contexts ) == 0 ) 
		{
			$tisheet->context_id = null;
			return;
		}

		// assign first level Concept to Tisheet
		$mainContext = Context::find( reset( $contexts )['subContext_id'] );
		$tisheet->context()->associate( $mainContext );

		// cut off the first element -> becomes the list of subConcepts
		$subContexts = array_slice( $contexts, 1 );
		
		// walks the array and builds up the pivot table
		array_walk( $subContexts, function( &$pivot, $key, $data ) 
		{
            $pivot['context_id'] = $data['context']->id;
            $pivot['tisheet_id'] = $data['tisheet']->id;
		}, array( 'tisheet' => $tisheet, 'context' => $mainContext ) );
		
		// syncs a list of existing and new subContexts
		TisheetController::syncSubContexts( $subContexts, $tisheet->context->children );
		
		$tisheet->context->children()->sync( $subContexts );
	}
	
	//
	public static function syncSubContexts( &$newSubContexts, &$existingSubContexts )
	{
		TisheetController::replaceKeysWithMultiKeysFromPivot( $newSubContexts );
		
		// compose 
		foreach( $existingSubContexts as $subContext )
		{
			$key = $subContext->pivot->context_id.$subContext->pivot->subContext_id.$subContext->pivot->tisheet_id;
			
			if ( array_key_exists( $key, $newSubContexts ) )
			{
				array_set( $newSubContexts, $key.'.id', $subContext->pivot->id );
			}
			else
			{
				$newSubContexts[$key] = array(
					'id' => $subContext->pivot->id,
					'context_id' => $subContext->pivot->context_id,
					'subContext_id' => $subContext->pivot->subContext_id,
					'tisheet_id' => $subContext->pivot->tisheet_id
				);
			}
		}
	}
	
	//
	public static function replaceKeysWithMultiKeysFromPivot( &$subContexts ) 
	{
		foreach( $subContexts as $key => $pivot )
		{
			$subContexts[$pivot['context_id'].$pivot['subContext_id'].$pivot['tisheet_id']] = $pivot;
			unset( $subContexts[$key] );
		}
	}
}
