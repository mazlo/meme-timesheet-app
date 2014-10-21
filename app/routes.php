<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get( '/', function()
{
	if ( Auth::check() )
		// user authenticated -> redirect to base url
		return Redirect::to( 'tisheets/today' );
	else 
		return Redirect::to( 'login' );
});

Route::get( '/login', function() { return View::make( 'user.login' ); } );
Route::post( '/login', 'UserController@login' );

Route::get( '/logout', 'UserController@logout' );

Route::get( '/signup', function() { return View::make( 'user.signup' ); } );
Route::post( '/signup', 'UserController@signup' );

Route::any( '/terms-and-conditions', 'BaseController@terms' );

/*
	Authentication neccessary
*/
Route::group( array( 'before' => 'auth' ), function()
{

	Route::get( '/tisheets', function()
	{
		$time = date( 'Y-m-d', time() );
		return Redirect::to( '/tisheets/'. $time );
	});

	Route::get( '/tisheets/{day}', 'TisheetController@index' );

	Route::post( '/tisheets/{day}', 'TisheetController@add' );
	
	Route::put( '/tisheets/{day}/tisheet/{id}', 'TisheetController@update' );
	Route::delete( '/tisheets/{day}/tisheet/{id}', 'TisheetController@delete' );

	Route::put( '/tisheets/{day}/tisheet/{id}/note', 'NoteController@update' );
	Route::delete( '/tisheets/{day}/tisheet/{id}/note', 'NoteController@delete' );

	Route::get( '/tisheets/{day}/summary/today/groupby/contexts', 'SummaryController@summaryForTodayGroupByContexts' );
	Route::get( '/tisheets/{day}/summary/week/groupby/contexts', 'SummaryController@summaryForWeekGroupByContexts' );

	Route::get( '/tisheets/{day}/summary/week/groupby/days/contexts', 'SummaryController@summaryForWeekGroupByDaysAndContexts' );
});
