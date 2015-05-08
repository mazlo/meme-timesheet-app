<?php

class Word extends Eloquent 
{
	public function user()
	{
		return $this->belongsTo( 'User', 'user_id' );
	}

	public function tisheets()
	{
		return $this
				->belongsToMany( 'Tisheet', 'tisheet_words'  )
				->withPivot( 'id', 'context_id' );
	}

}