<?php

class ColumnController extends BaseController 
{

    public function columns()
    {
        $columns = Column::where( 'user_id', Auth::user()->id )->get();

        return View::make( 'ajax.columns' )
            ->with( 'columns', $columns );
    }
}