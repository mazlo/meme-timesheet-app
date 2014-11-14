<?php

class Context extends Eloquent 
{
	public function tisheets()
	{
		return $this->hasMany( 'Tisheet' );
	}

	public function children()
	{
		return $this->belongsToMany( 'Context', 'context_relations', 'context_id', 'subContext_id'  );
	}

}