<?php

class ColumnController extends BaseController 
{

    public function columns()
    {
        $columns = Column::where( 'user_id', Auth::user()->id )->get();

        return View::make( 'ajax.columns' )
            ->with( 'columns', $columns );
    }

    /**
    *   Inserts or updates a column
    */
    public function insertOrUpdate( $day, $cid )
    {
        
    }

    /**
    *   Inserts of updates an item for the given column-id
    */
    public function insertOrUpdateItem( $day, $cid, $iid )
    {
        
    }
}