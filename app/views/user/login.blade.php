@extends ( 'layout' )

@section( 'header' )
    <div class='w3-col l2 m4 s2 w3-right-align w3-text-light-grey'>
        <a href='{{ url( "logout" ) }}'><span class='octicon octicon-sign-in' style='font-size: 18px'></span></a>
    </div>
@stop

@section( 'content' )

    <h2>Please sign in</h2>

    <p>Have you <a href='{{ url( "signup" ) }}'>signed up</a> already?</p>

    @if( $error = $errors->first( 'password' ) )
        <span class='notification-negativ'>{{ $error }}</span>
    @elseif ( Session::has( 'signup_successfull' ) )
        <span class='notification-positiv'>{{ Session::get( 'signup_successfull' ) }}</span>
    @endif

    {{ Form::open( array( 'url' => 'login' )) }}

    <h4>E-Mail</h4>
    {{ Form::text( 'email', '', array( 'placeholder' => 'john@smithy.com', 'class' => 'cc-keep-clear-content-little textfield textfield-narrow' ) ) }}
    
    <h4>Password</h4>
    {{ Form::password( 'password', array( 'placeholder' => '●●●●●●●●', 'class' => 'cc-keep-clear-content-little textfield textfield-narrow' ) ) }}

    <div>
        {{ Form::submit( 'Sign in', array( 'class' => 'button button-submit button-margin' ) ) }}
        <span class='js-ajax-loader cc-element-hidden'><img src='{{ url( "loading.gif" ) }}' /></span>
    </div>

    {{ Form::close() }}

<script type="text/javascript">

    //
    $jQ( function()
    {
        $jQ( 'input[name="email"]' ).focus();
    });

    //
    $jQ( 'form' ).submit( function()
    {
        $jQ( this ).find( 'span.js-ajax-loader' ).toggle( 'cc-element-hidden' );
        $jQ( this ).attr( 'disabled', 'disabled' );

        return true;
    });

</script>

@stop

@section( 'footer' )
    @include( 'footer' )
@stop
