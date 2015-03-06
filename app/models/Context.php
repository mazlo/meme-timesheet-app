<?php

class Context extends Eloquent 
{
	public function user()
	{
		return $this->belongsTo( 'User', 'user_id' );
	}
	
	public function tisheets()
	{
		return $this->hasMany( 'Tisheet' );
	}

	public function children()
	{
		return $this->belongsToMany( 'Context', 'context_relations', 'context_id', 'subContext_id'  )
					->withPivot( 'id', 'tisheet_id' );
	}

}