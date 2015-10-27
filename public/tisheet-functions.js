/**
*
*/
var runStopwatch = function( tisheet, command, startOnly )
{
    tisheet.find( '.js-octicon-stopwatch' ).trigger( 'click', { name: command, startOnly: startOnly } );
}

/**
*
*/
var updateQuarterOfTime = function ( tisheet, obj ) 
{
    var param = obj.callback.param;

    // if value is given in minutes
    if ( param.indexOf( 'min' ) > 0 )
    {
        param = param.split( 'min' )[0];
        if ( param <= 240 )
            tisheet.find( 'span.js-time-spent-quarter:eq('+ roundToQuarterOfHour( param ) + ')' ).click();
        else 
            tisheet.find( 'span.js-time-spent-quarter:eq(15)' ).click();
    }
    
    // else it is expected to be given in hours
    else
    {
        if ( param.indexOf( 'h' ) > 0 )
            param = param.split( 'h' )[0];

        if ( param <= 4 )
            tisheet.find( 'span.js-time-spent-quarter:eq('+ roundToQuarterOfHour( param*60 ) + ')' ).click();
        else
            tisheet.find( 'span.js-time-spent-quarter:eq(15)' ).click();
    }
}

/**
*
*/
var roundToQuarterOfHour = function( minutes )
{
    var rounded = Math.ceil( minutes/15 );

    if ( rounded == 0 )
        return 0;
    else
        return rounded -1;
}