<?php

class Context extends Eloquent 
{
	public function tisheets()
	{
		return $this->hasMany( 'Tisheet' );
	}
}