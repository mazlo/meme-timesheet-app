<?php

class AdminController extends BaseController 
{

	public function syncContexts() 
	{
		// we sync all, not just for a user
		$tisheets = Tisheet::all();

		foreach ( $tisheets as $tisheet ) 
		{
			$description = $tisheet->description;

			// parse description for Contexts
			$contexts = TisheetController::parseContexts( $description );

			// and assign them to current Tisheet
			TisheetController::syncContexts( $tisheet, $contexts );

			$tisheet->save();
		}
	}

}
