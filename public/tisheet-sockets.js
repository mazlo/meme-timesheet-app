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

        // indicates whether the update came from THIS client, 
        // which means that we do not need to trigger it again
        if ( iAmLead( data ) )
            return;

        // get the tisheet with given id and update value
        var tisheet = $jQ( '#'+ data.tid );
        tisheet.find( 'input.js-tisheet-description' ).val( data.value );
    });

    // updates a tisheet time spent quarter
    app.BrainSocket.Event.listen( 'tisheet.time.update.event', function( msg )
    {
        if ( !validEvent( msg ) )
            return;

        var data = msg.client.data;

        // tisheet with given id
        var tisheet = $jQ( '#'+ data.tid );
        // time quarter to be activated
        var quarter = tisheet.find( 'span.js-time-spent-quarter' ).eq( data.value - 1 );
        
        markTimeSpentQuarterAsActive( quarter );

        updateTisheetTimeline();
        updateTisheetSummary();
    });

    // 
    app.BrainSocket.Event.listen( 'tisheet.note.update.event', function( msg )
    {
        if ( !validEvent( msg ) )
            return;

        var data = msg.client.data;

        var tisheet = $jQ( '#'+ data.tid );
        var note = tisheet.find( 'div.js-tisheet-note' );
        
        if ( data.value == '' )
            // disable octicon-info icon
            tisheet.find( 'span.octicon-info' ).removeClass( 'element-visible' );
        else 
            // enable octicon-info icon
            tisheet.find( 'span.octicon-info' ).addClass( 'element-visible' );
        
        // update textarea value, no matter is octicon-icon is visible
        note.find( 'textarea' ).val( data.value );
    });

    // 
    app.BrainSocket.Event.listen( 'tisheet.stopwatch.update.event', function( msg )
    {
        if ( !validEvent( msg ) )
            return;

        var data = msg.client.data;

        // indicates whether the stopwatch was started from THIS client, 
        // which means that we do not need to trigger it again
        iAmLead( data );

        // simulate click

        // I am not the lead, thus the click on the stopwatch has to be simulated. 
        // In case the other client disconnects, we can handle the stop by ourself
        var requestedStopwatch = $jQ( '#'+ data.tid ).find( 'span.js-octicon-stopwatch' );
        requestedStopwatch.trigger( 'click', { startOnly: false, triggerEvent: false } );
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

/**
 */
var iAmLead = function ( data )
{
    if ( data == undefined )
        return false;

    if ( data.lead == undefined )
        return false;

    var iAmLead = data.lead === getSessionToken();

    if ( iAmLead )
        return true;

    return false;
}
