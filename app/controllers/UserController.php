<?php

use Illuminate\Support\MessageBag;

class UserController extends Controller
{

    /**
    *
    */
    public function update()
    {
        $user = Auth::user();

        if ( Input::has( 'gl' ) )
            $user->showStory = Input::get( 'gl' ) == 'true' ? 1 : 0;

        $user->save();
    }

    /**
    *
    */
	public function login()
    {
        $data = array();

        // user authenticated -> do not continue! redirect to base (light security check)
        if ( Auth::check() )
            return Redirect::to( 'tisheets/today' );

        $validator = Validator::make( Input::all(), array(
            'email' => 'required|email',
            'password' => 'required'
        ));

        if ( $validator->passes() )
        {
            $credentials = array(
                'email' => Input::get( 'email' ),
                'password' => Input::get( 'password' )
            );

            // attempt to login user with credentials
            if ( Auth::attempt( $credentials, true ) )
            {
                Auth::user()->touch();
                
                // successful login redirects
                return Redirect::intended( 'tisheets/today' );
            }
        }

        // validation does not pass or wrong credentials

        $data[ 'errors' ] = new MessageBag( array(
            'password' => 'Username or password not valid!'
        ));

        $data[ 'email' ] = Input::get( 'email' );

        return Redirect::to( 'login' )->with( $data )->withInput( Input::all() );
    }

    /**
    *
    */
    public function logout()
	{
	    Auth::logout();
        Session::flush();

	    return Redirect::to( '/' );
	}

    /**
    *
    */
    public function signup()
    {
        $data = array();

        // check if data was postet
        if ( Input::server( 'REQUEST_METHOD') == 'POST' )
        {
            // rules for input fields
            $rules = array(
                'username' => 'required|unique:users',
                'email' => 'required|email|min:8|unique:users',
                'password' => 'required|min:6|confirmed',
                'terms' => 'required'
            );
            
            // messages for validation errors
            $messages = array(
                'required' => 'You forgot to fill this in!',
                'email.min' => 'Please give at least 8 characters!',
                'password.min' => 'Please give at least 6 characters!',
                'email.unique' => 'This email address has already been registered!',
                'password.confirmed' => 'The passwords given do not match!',
                'terms.required' => 'Please check that you have read the terms and conditions'
            );

            $validator = Validator::make( Input::all(), $rules, $messages );

            if ( $validator->fails() )
            {
                return Redirect::to( 'signup' )->withErrors( $validator )->withInput( Input::all() );
            }

            $user = new User();
            $user->username = Input::get( 'username' );
            $user->email = Input::get( 'email' );
            $user->password = Hash::make( Input::get( 'password' ) );
            $user->save();

            // notify my master
            $data = array( 'user' => $user );

            return Redirect::to( 'login' )->with( 'signup_successfull', 'Yeah! Thank\'s for signing up! You can now log in with your username and password.' );
        }

        // someone wants to register
        return View::make( 'user.signup' );
    }
}