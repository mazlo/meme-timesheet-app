<?php

class Column extends Eloquent 
{

    public function items()
    {
        return $this->hasMany( 'ColumnItem', 'column_id' );
    }

    public function user()
    {
        return $this->belongsTo( 'User' );
    }
}