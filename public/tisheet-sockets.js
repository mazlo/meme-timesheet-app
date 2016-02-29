var initWebsocketConnection = function()
{
    window.app = {};

    app.BrainSocket = new BrainSocket(
            new WebSocket( 'ws://localhost:8080' ),
            new BrainSocketPubSub()
    );

    // SOCKET ACTION HANDLERS

    // updates the value of tisheet with specific id
    app.BrainSocket.Event.listen( 'tisheet.update.event', function( msg )
    {
        if ( !validEvent( msg ) )
            return;

        var data = msg.client.data;

        // get the tisheet with given id and update value
        var tisheet = $jQ( '#'+ data.id );
        tisheet.find( 'input.js-tisheet-description' ).val( data.value );
    });

    // updates a tisheet time spent quarter
    app.BrainSocket.Event.listen( 'tisheet.time.update.event', function( msg )
    {
        if ( !validEvent( msg ) )
            return;

        var data = msg.client.data;

        // get tisheet with given id, get time quarter and activate it
        var tisheet = $jQ( '#'+ data.tid );
        var quarter = tisheet.find( 'span.js-time-spent-quarter' ).eq( data.value - 1 );
        
        markTimeSpentQuarterAsActive( quarter );

        updateTisheetTimeline();
        updateTisheetSummary();
    });

    // 
    app.BrainSocket.Event.listen( 'tisheet.note.update.event', function( msg )
    {
        // - check if is visible 
        // -- if yes: update in that case
        // -- if no: mark info-icon

        if ( !validEvent( msg ) )
            return;

        var data = msg.client.data;

        // get tisheet with given id, get time quarter and activate it
        var tisheet = $jQ( '#'+ data.id );
        var note = tisheet.find( 'div.js-tisheet-note' );
        
        // update info icon
        if ( data.value == '' )
            tisheet.find( 'span.octicon-info' ).removeClass( 'element-visible' );
        else 
            tisheet.find( 'span.octicon-info' ).addClass( 'element-visible' );
        
        // update textarea value
        note.find( 'textarea' ).val( data.value );
    });

    //
    app.BrainSocket.Event.listen( 'tisheet.stopwatch.update.event', function( msg )
    {
        if ( !validEvent( msg ) )
            return;

        var data = msg.client.data;

        // 
        
        var requestedStopwatchId = data.tidr;
        var requestedStopwatch = $jQ( '#'+ requestedStopwatchId ).find( 'span.js-octicon-stopwatch' );
        
        if ( data.value == 'running' )
        {
            // do not use toggleClass, which leads to inconsistent behaviour
            requestedStopwatch.addClass( 'octicon-playback-pause element-visible' );
            requestedStopwatch.removeClass( 'octicon-playback-play' );
        }
        else /* stopped */ {
            // do not use toggleClass, which leads to inconsistent behaviour
            requestedStopwatch.addClass( 'octicon-playback-play' );
            requestedStopwatch.removeClass( 'octicon-playback-pause element-visible' );
        }

        // stop other running stopwatch if it is given
        if ( data.tido != undefined )
        {
            var runningStopwatch = $jQ( '#'+ data.tido ).find( 'span.js-octicon-stopwatch' );
            // do not use toggleClass, which leads to inconsistent behaviour
            runningStopwatch.addClass( 'octicon-playback-play' );
            runningStopwatch.removeClass( 'octicon-playback-pause element-visible' );
        }

    });

    /*
    * Planned events:
    * - lock tisheet if user is editing
    * - ...
    */

    app.BrainSocket.Event.listen('app.success',function(msg)
    {
        console.log(msg);
    });

    app.BrainSocket.Event.listen('app.error',function(msg)
    {
        console.log(msg);
    });
}

/**
*
*/
var validEvent = function( msg )
{
    if ( !msg.client.data )
        return false;

    return true;
}
