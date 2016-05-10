<?php

class ColumnItemController extends BaseController 
{
    /**
     *  @RequestMethod => put /tisheets/{day}/columns/{id}/items
     */
    public function update( $day, $cid )
    {
        // update positions of columns
        if ( Input::has( 'cids' ) )
        {
            $ciids = Input::get( 'cids' );

            foreach( $ciids as $iid => $position )
            {
                // TODO ZL restrict to user
                $columnItem = ColumnItem::where( 'id', $iid )->first();
                
                $columnItem->position = $position;
                $columnItem->save();
            }
        }
    }
}