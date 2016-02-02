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
	Route::put( '/user/profile', 'UserController@update' );

	Route::get( '/tisheets', function()
	{
		$time = date( 'Y-m-d', time() );
		return Redirect::to( '/tisheets/'. $time );
	});

	Route::get( '/tisheets/{day}', 'TisheetController@index' );	
	Route::post( '/tisheets/{day}', 'TisheetController@add' );

	// update tisheets positions or timesheet goal
	Route::put( '/tisheets/{day}', 'TimesheetController@update' );
	Route::delete( '/tisheets/{day}', 'TimesheetController@delete' );

	Route::get( '/tisheets/{day}/autocomplete', 'TisheetController@autocomplete' );
	Route::get( '/tisheets/{day}/timeline', 'TisheetController@timeline' );

	Route::get( '/tisheets/{day}/columns', 'ColumnController@columns' );
	Route::put( '/tisheets/{day}/columns', 'ColumnController@update' );
	
	Route::put( '/tisheets/{day}/columns/{id}', 'ColumnController@insertOrUpdate' );
	Route::delete( '/tisheets/{day}/columns/{id}', 'ColumnController@delete' );
	
	Route::put( '/tisheets/{day}/columns/{id}/item/{cid}', 'ColumnController@insertOrUpdateItem' );
	Route::delete( '/tisheets/{day}/columns/{id}/item/{cid}', 'ColumnController@deleteItem' );

	Route::put( '/tisheets/{day}/tisheet/{id}', 'TisheetController@update' );
	Route::delete( '/tisheets/{day}/tisheet/{id}', 'TisheetController@delete' );

	Route::put( '/tisheets/{day}/tisheet/{id}/note', 'NoteController@update' );
	Route::delete( '/tisheets/{day}/tisheet/{id}/note', 'NoteController@delete' );

	Route::get( '/tisheets/{day}/tisheet/{id}/same-as', 'SummaryController@sameAs' );

	Route::get( '/tisheets/{day}/summary/{period}/groupby/contexts', 'SummaryController@groupByContextByDayAndPeriod' );
	Route::get( '/tisheets/{day}/summary/{period}/groupby/days/contexts/{context}', 'SummaryController@byContextGroupByDaysGroupByContexts' );

	Route::get( '/tisheets/{day}/summary/week/groupby/days/contexts', 'SummaryController@forWeekGroupByDaysAndContexts' );

	// admin functions
	Route::get( '/admin/tisheets/words/sync', 'AdminController@syncWords' );
});
