<?php

class ColumnItem extends Eloquent 
{
    public function column()
    {
        return $this->belongsTo( 'Column', 'column_id' );
    }

    /**
     *
     */
    public static function getNonEmptyByIdAndColumnId( $iid, $cid )
    {
        $column = Column::where( 'user_id', Auth::user()->id )->where( 'id', $cid )->first();

        // create item if necessary
        if ( $iid == 'undefined' )
        {
            $columnItem = new ColumnItem();
            $columnItem->column()->associate( $column );
        }
        else
            $columnItem = ColumnItem::find( $iid );

        // still empty? cannot be found be iid
        if ( empty( $columnItem ) )
        {
            $columnItem = new ColumnItem();
            $columnItem->column()->associate( $column );
        }

        return $columnItem;
    }
}