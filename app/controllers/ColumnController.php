<?php

class ColumnController extends BaseController 
{

    public function columns()
    {
        $columns = Column::where( 'user_id', Auth::user()->id )->orderBy( 'position' )->get();

        return View::make( 'ajax.columns' )
            ->with( 'columns', $columns );
    }

    /**
    *   @RequestMethod => PUT
    */
    public function update( $day )
    {
        // update positions of columns
        if ( Input::has( 'cids' ) )
        {
            $cids = Input::get( 'cids' );

            foreach( $cids as $id => $position )
            {
                // TODO ZL restrict to user
                $column = Column::where( 'id', $id )->first();
                
                $column->position = $position;
                $column->save();
            }
        }
    }

    /**
    *   Inserts or updates a column
    */
    public function insertOrUpdate( $day, $cid )
    {
        if ( $cid == 'undefined' )
        {
            $column = new Column();
            $column->user()->associate( Auth::user() );
        }
        else
            $column = Column::where( 'user_id', Auth::user()->id )->where( 'id', $cid )->first();

        if ( empty( $column ) )
        {
            $column = new Column();
            $column->user()->associate( Auth::user() );
        } else {
            $column->label = Input::get( 'lb' );
            $column->position = Input::get( 'ps' );
        }

        $column->save();

        return Response::json( array( 
            'status' => 'ok', 
            'action' => 'add', 
            'id' => $column->id
        ) );
    }

    /**
    *   Inserts of updates an item for the given column-id
    */
    public function insertOrUpdateItem( $day, $cid, $iid )
    {
        // get associated column
        $column = Column::where( 'user_id', Auth::user()->id )->where( 'id', $cid )->first();

        // create item if necessary
        if ( $iid == 'undefined' )
        {
            $columnItem = new ColumnItem();
            $columnItem->column()->associate( $column );
        }
        else
            $columnItem = ColumnItem::find( $iid );

        // still empty?
        if ( empty( $columnItem ) )
        {
            $columnItem = new ColumnItem();
            $columnItem->column()->associate( $column );
        }
        else 
            $columnItem->label = Input::get( 'lb' );

        $columnItem->save();

        return Response::json( array( 
            'status' => 'ok', 
            'action' => 'add', 
            'id' => $columnItem->id
        ) );
    }

    /**
    *   Deletes a Column and all ColumnItems associated with that Column
    */
    public function delete( $day, $cid )
    {
        // get associated column
        $column = Column::where( 'user_id', Auth::user()->id )->where( 'id', $cid )->first();

        if ( empty( $column ) )
            return Response::json( array(
                'status' => 'error',
                'action' => 'delete',
                'message' => 'no column associated with given id '. $cid
            ) );

        // delete all related ColumnItems
        foreach( $column->items as $item )
        {
            $item->delete();
        }

        // delete the Column
        $column->delete();

        return Response::json( array(
            'status' => 'ok',
            'action' => 'delete'
        ) );
    }

    /**
    *   Deletes an item with the given columnItem-id
    */
    public function deleteItem( $day, $cid, $iid )
    {
        $columnItem = ColumnItem::find( $iid );

        $columnItem->delete();

        return Response::json( array( 
            'status' => 'ok'
        ) );
    }
}