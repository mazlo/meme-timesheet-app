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

	public function note()
	{
		return $this->hasOne( 'Note', 'tisheet_id' );
	}

	public function words()
	{
		return $this
				->belongsToMany( 'Word', 'tisheet_words' )
				->withPivot( 'id', 'context_id' );
	}
}