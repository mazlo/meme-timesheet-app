/**
*
*/
var runStopwatch = function( tisheet, command, startOnly )
{
    tisheet.find( '.js-octicon-stopwatch' ).trigger( 'click', { name: command, startOnly: startOnly } );
}