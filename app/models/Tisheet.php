<?php

class Tisheet extends Eloquent 
{

	public function context()
	{
		return $this->belongsTo( 'Context', 'context_id' );
	}

	public function user()
	{
		return $this->belongsTo( 'User', 'user_id' );
	}
}