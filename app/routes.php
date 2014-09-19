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
	//if ( Auth::check() )
		return Redirect::to( 'tisheet' );
	//else 
	//	return Redirect::to( 'login' );
});

//Route::get( '/login', function() { return View::make( 'user.login' ); } );
//Route::post( '/login', 'UserController@login' );

//Route::get( '/logout', 'UserController@logout' );

//Route::get( '/signup', function() { return View::make( 'user.signup' ); } );
//Route::post( '/signup', 'UserController@signup' );

/*
	Authentication neccessary
*/
//Route::group( array( 'before' => 'auth' ), function()
//{

	Route::get( '/tisheets', 'TisheetController@index' );

	Route::get( '/tisheets/{day}/summary', 'TisheetController@summary' );

	Route::put( '/tisheets/{day}/tisheet/{id}', 'TisheetController@update' );

	Route::post( '/tisheet', 'PostController@add' );
	Route::put( '/tisheet/{id}', 'PostController@update' );
	Route::delete( '/tisheet/{id}', 'PostController@delete' );

	Route::get( '/tisheet/{id}/next', 'PostController@next' );
	Route::get( '/tisheet/{id}/next/{lastId}', 'PostController@next' );
	Route::get( '/tisheet/{id}/previous', 'PostController@previous' );
	Route::get( '/tisheet/{id}/previous/{lastId}', 'PostController@previous' );

//});
