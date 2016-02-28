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
        var tisheet = $jQ( 'tr[id="'+ data.tid +'"]' );
        tisheet.find( 'input.js-tisheet-description' ).val( data.value );
    });

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
