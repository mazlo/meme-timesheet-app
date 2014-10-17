<?php

class Note extends Eloquent 
{
	public function tisheet()
	{
		return $this->belongsTo( 'Tisheet' );
	}
}