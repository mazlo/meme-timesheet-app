<?php

class TisheetController extends BaseController 
{

    /**
    *   Retrieves all tisheets from the database.
    */
    public function index()
    {
        $tisheet = Tisheet::where( 'day', date( 'Y-m-d', time() ) )->get();

        return View::make( 'index' )->with( 'tisheets', $tisheet );
    }

    /**
    *   Adds a new tisheet to the database.
    */
    public function add( $day )
    {
        $tisheet = new Tisheet();

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

            $tisheet->description = $value;
        }
        
        $tisheet = date( 'Y-m-d', time() );
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
        
        $tisheet = Tisheet::where( 'id', '=', $id )->first();

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
    public function summary( $day )
    {
        // select c.prefLabel, sum(t.time_spent) from tisheets t join contexts c on t.context_id=c.id group by c.prefLabel;

        $sum = DB::table( 'tisheets' )
            ->join( 'contexts', 'tisheets.context_id', '=', 'contexts.id' )
            ->select( 'contexts.prefLabel', DB::raw( 'sum( tisheets.time_spent ) as total_time_spent' ) )
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