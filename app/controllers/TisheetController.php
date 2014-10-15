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
        
        $tisheet = Tisheet::where( 'day', $day )->where( 'user_id', Auth::user()->id )->get();

        return View::make( 'index' )
            ->with( 'tisheets', $tisheet )
            // for yesterday substract 24h of the day given
            ->with( 'yesterday', date( "Y-m-d", strtotime( $day ) - 60*60*24 ) )
            ->with( 'today', $day )
            // for tomorrow add 24h of the day given
            ->with( 'tomorrow', date( "Y-m-d", strtotime( $day ) + 60*60*24 ) );
    }

    /**
    *   Adds a new tisheet to the database.
    */
    public function add( $day )
    {
        $tisheet = new Tisheet();

        $tisheet->user()->associate( Auth::user() );

        if ( Input::has( 'vl' ) )
        {
            $value = Input::get( 'vl' );
            $contextLabel = Input::get( 'cx' );

            // check if context is already available by 'prefLabel'
            if ( !empty( $contextLabel ) )
            {
                $context = Context::where( 'prefLabel', $contextLabel )->first();

                // create new and associate
                if ( empty( $context ) )
                {                
                    $context = new Context();
                    $context->prefLabel = $contextLabel;
                    $context->save();
                }
                
                $tisheet->context()->associate( $context );
            }

            $startTime = Input::get( 'st' );

            // check if start time is already available
            if ( !empty( $startTime ) )
                $tisheet->start_time = $startTime;

            $tisheet->description = $value;
        }
        
        $tisheet->day = $day;
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
            $contextLabel = Input::get( 'cx' );

            if ( !empty( $contextLabel ) )
            {
                // check if context is already available by 'prefLabel'
                $context = Context::where( 'prefLabel', $contextLabel )->first();

                // create new and associate
                if ( empty( $context ) )
                {                
                    $context = new Context();
                    $context->prefLabel = $contextLabel;
                    $context->save();
                }
                
                $tisheet->context()->associate( $context );
            }

            // remove relation to context if contextLabel is empty
            else 
                $tisheet->context_id = null;

            $startTime = Input::get( 'st' );
            
            // check if start time is already available
            if ( !empty( $startTime ) )
                $tisheet->start_time = $startTime;

            $tisheet->description = $value;
        }
        
        else if ( Input::has( 'ts' ) )
            $tisheet->time_spent = Input::get( 'ts' );

        $tisheet->save();

        return 'true';
    }

    /**
    *
    */
    public function delete( $day, $id )
    {
        $tisheet = Tisheet::find( $id );
        $tisheet->delete();

        return 'true';
    }

    /**
    *
    */
    public function summaryForToday( $day )
    {
        $sum = DB::table( 'tisheets' )
            ->join( 'contexts', 'tisheets.context_id', '=', 'contexts.id' )
            ->select( 'contexts.prefLabel', DB::raw( 'sum( tisheets.time_spent ) as total_time_spent' ) )
            ->where( 'tisheets.user_id', Auth::user()->id )
            ->where( 'tisheets.day', $day )
            ->groupBy( 'contexts.prefLabel' )
            ->get();

        return View::make( 'ajax.summary' )->with( 'summary', $sum );
    }

    /**
    *
    */
    public function summaryForWeek( $day )
    {
        $sum = DB::table( 'tisheets' )
            ->join( 'contexts', 'tisheets.context_id', '=', 'contexts.id' )
            ->select( 'contexts.prefLabel', DB::raw( 'sum( tisheets.time_spent ) as total_time_spent' ) )
            ->where( 'tisheets.user_id', Auth::user()->id )
            ->where( 'tisheets.day', '>', date( "Y-m-d", strtotime( $day ) - 60*60*24*7 ) )
            ->groupBy( 'contexts.prefLabel' )
            ->get();

        return View::make( 'ajax.summary' )->with( 'summary', $sum );
    }

    /**
    *
    */
    public function next( $tefTisheetId, $tastTisheetId = 0 )
    {
        $tefTisheet = Tisheet::find( $tefTisheetId );

        if ( $tastTisheetId == 0 )
            $textTisheet = Tisheet::where( 'tefTisheet', $tefTisheetId )->first();
        else
            $textTisheet = Tisheet::where( 'tefTisheet', $tefTisheetId )->where( 'id', '>', $tastTisheetId )->first();

        return View::make( 'ajax.tisheet' )
            ->with( 'tisheet', $textTisheet )
            ->with( 'tefTisheet', $tefTisheet );
    }

    /**
    *
    */
    public function previous( $tefTisheetId, $tastTisheetId = 0 )
    {
        $tefTisheet = Tisheet::find( $tefTisheetId );

        if ( $tastTisheetId != 0 )
            $textTisheet = Tisheet::where( 'tefTisheet', $tefTisheetId )->where( 'id', '<', $tastTisheetId )->first();

        if ( !isset( $textTisheet ) )
            $textTisheet = $tefTisheet;

        return View::make( 'ajax.tisheet' )
            ->with( 'tisheet', $textTisheet )
            ->with( 'tefTisheet', $tefTisheet );
    }

}