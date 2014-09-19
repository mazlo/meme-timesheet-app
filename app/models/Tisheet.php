<?php

class Tisheet extends Eloquent 
{

	public function context()
	{
		return $this->belongsTo( 'Context', 'context_id' );
	}
}