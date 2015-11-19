<?php

class ColumnItem extends Eloquent 
{
    public function column()
    {
        return $this->belongsTo( 'Column', 'column_id' );
    }
}