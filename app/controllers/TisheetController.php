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
		$words = explode( ' ', $value );

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

			return $context->id;
		},  
			// from an array of Contexts that was parsed from the text
			array_filter( $words, function( $word )
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
	public static function syncContexts( $tisheet, $contexts ) 
	{
		// reset association to a Context
		if ( count( $contexts ) == 0 ) 
		{
			$tisheet->context_id = null;
			return;
		}

		$idxContexts = array_combine( range( 0, count( $contexts ) - 1 ), $contexts );

		$mainContext = Context::find( $idxContexts[0] );
		$tisheet->context()->associate( $mainContext );

		// cut off the first element
		$subContexts = array_slice( $idxContexts, 1 );

		// sync sub contexts
		$tisheet->context->children()->sync( $subContexts, false );
	}
}
