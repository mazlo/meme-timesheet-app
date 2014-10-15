@extends( 'layout' )

@section( 'content' )

    <h2>Sign up</h2>

	<p>Create a new user account here. Afterwards you can <a href='{{ url( "login" ) }}'>sign in</a>.</p>

    {{ Form::open() }}

        <h4>Username</h4>
        @if( $errors->has( 'username' ) )
            <? $error = $errors->get( 'username' ) ?>
            <span class='notification-negativ'>{{ $error[0] }}</span>
        @endif

        {{ Form::text( 'username', '', array( 'placeholder' => 'john.smith', 'class' => 'textfield-narrow' ) ) }}
    
        <h4>E-Mail Address</h4>
        @if( $errors->has( 'email' ) )
            <? $error = $errors->get( 'email' ) ?>
            <span class='notification-negativ'>{{ $error[0] }}</span>
        @endif

        {{ Form::text( 'email', '', array( 'placeholder' => 'john.smith@email.com', 'class' => 'textfield-narrow' ) ) }}

        <h4>Password</h4>
        @if( $errors->has( 'password' ) )
            <? $error = $errors->get( 'password' ) ?>
            <span class='notification-negativ'>{{ $error[0] }}</span>
        @endif

        {{ Form::password( 'password', array( 'placeholder' => '●●●●●●●●', 'class' => 'textfield-narrow' ) ) }}

        <h4>Confirm Password</h4>
        
        {{ Form::password( 'password_confirmation', array( 'class' => 'textfield-narrow' ) ) }}

        {{ Form::submit( 'Sign up', array( 'class' => 'button button-submit button-margin' ) ) }}
        
    {{ Form::close() }}

@stop
