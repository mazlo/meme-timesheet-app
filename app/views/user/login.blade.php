@extends ( 'layout' )

@section( 'content' )

    <h2>Sign in <span style='font-size: 13px'>to gain access to</span> ya timesheet</h2>

    <p>Have you <a href='{{ url( "signup" ) }}'>signed up</a> already?</p>

    @if( $error = $errors->first( 'password' ) )
        <span class='notification-negativ'>{{ $error }}</span>
    @elseif ( Session::has( 'signup_successfull' ) )
        <span class='notification-positiv'>{{ Session::get( 'signup_successfull' ) }}</span>
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
