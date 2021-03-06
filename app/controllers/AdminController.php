<?php

class AdminController extends BaseController 
{

	public function syncWords() 
	{
		// we sync all, not just for a user
		$tisheets = Tisheet::all();

		foreach ( $tisheets as $tisheet ) 
		{
			$description = $tisheet->description;

			// sync Contexts of Tisheet
			
			TisheetController::syncWords( $tisheet, $description );
		}
	}

}
