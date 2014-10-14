@extends ( 'layout' )

@section( 'content' )

    <h2>Sign in</h2>

    <p>Please sign in to gain access. Have you <a href='{{ url( "signup" ) }}'>signed up</a> already?</p>

    @if( $error = $errors->first( 'password' ) )
        <div class='credentials-error'>
            {{ $error }}
        </div>
    @elseif ( Session::has( 'signup_successfull' ) )
        <h4 class='message_success'>{{ Session::get( 'signup_successfull' ) }}</h4>
    @endif

    {{ Form::open( array( 'url' => 'login' )) }}

    <h4>E-Mail</h4>
    {{ Form::text( 'email', '', array( 'placeholder' => 'john@smithy.com', 'class' => 'textfield-narrow' ) ) }}
    
    <h4>Password</h4>
    {{ Form::password( 'password', array( 'placeholder' => '●●●●●●●●', 'class' => 'textfield-narrow' ) ) }}

    <div>
        {{ Form::submit( 'Sign in', array( 'class' => 'button button-submit button-margin' ) ) }}
    </div>

    {{ Form::close() }}

<script type="text/javascript">

    $jQ( function()
    {
        $jQ( 'input[name="email"]' ).focus();
    });

</script>

@stop
