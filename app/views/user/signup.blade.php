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

        {{ Form::text( 'username', '', array( 'placeholder' => 'john.smith', 'class' => 'cc-keep-clear-content-little textfield textfield-narrow' ) ) }}
    
        <h4>E-Mail Address</h4>
        @if( $errors->has( 'email' ) )
            <? $error = $errors->get( 'email' ) ?>
            <span class='notification-negativ'>{{ $error[0] }}</span>
        @endif

        {{ Form::text( 'email', '', array( 'placeholder' => 'john.smith@email.com', 'class' => 'cc-keep-clear-content-little textfield textfield-narrow' ) ) }}

        <h4>Password</h4>
        @if( $errors->has( 'password' ) )
            <? $error = $errors->get( 'password' ) ?>
            <span class='notification-negativ'>{{ $error[0] }}</span>
        @endif

        {{ Form::password( 'password', array( 'placeholder' => '●●●●●●●●', 'class' => 'cc-keep-clear-content-little textfield textfield-narrow' ) ) }}

        <h4>Confirm Password</h4>
        
        {{ Form::password( 'password_confirmation', array( 'class' => 'cc-keep-clear-content-little textfield textfield-narrow' ) ) }}

        <p>
            @if( $errors->has( 'terms' ) )
                <? $error = $errors->get( 'terms' ) ?>
                <span class='notification-negativ'>{{ $error[0] }}</span>
            @endif

            <input type='checkbox' name='terms' id='terms' />
            <label for='terms'>I accept the <a href='{{ url("terms-and-conditions") }}'>terms and conditions</a> of tim.mazlo.de.</label>
        </p>

        <div>
            {{ Form::submit( 'Sign up', array( 'class' => 'button button-submit button-margin' ) ) }}
            <span class='js-ajax-loader cc-element-hidden'><img src='{{ url( "loading.gif" ) }}' /></span>
        </div>
        
    {{ Form::close() }}

<script type='text/javascript'>

    //
    $jQ( function()
    {
        $jQ( 'input[name="username"]' ).focus();
    });

    //
    $jQ( 'form' ).submit( function()
    {
        $jQ( this ).find( 'span.js-ajax-loader' ).toggleClass( 'cc-element-hidden' );
        $jQ( this ).attr( 'disabled', 'disabled' );

        return true;
    });

</script>

@stop
