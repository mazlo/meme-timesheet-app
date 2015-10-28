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
var updateQuarterOfTime = function ( tisheet, obj, recentTisheet ) 
{
    var param = obj.callback.param;

    // if value is given in minutes
    if ( param.indexOf( 'min' ) > 0 )
    {
        param = param.split( 'min' )[0];

        if ( param > 240 )
            param = 226;    // roundToQuarterOfHour divides by 15 and applies Math.ceil()
    }
    
    // else it is expected to be given in hours
    else
    {
        if ( param.indexOf( 'h' ) > 0 )
            param = param.split( 'h' )[0];

        if ( param <= 4 )
            param = param*60;
        else
            param = 226;    // roundToQuarterOfHour divides by 15 and applies Math.ceil()
    }
    
    // update current time spent
    tisheet.find( 'span.js-time-spent-quarter:eq('+ ( roundToQuarterOfHour( param )-1 ) + ')' ).click();

    // update recent tisheet that was running, i.e. substract used time
    
    var recentTisheet = getRunningStopwatch();

    if ( recentTisheet == undefined )
        return; // no tisheet running

    if ( recentTisheet.id() == tisheet.id() )
        return; // recent tisheet same as this one

    var recentTook = recentTisheet.find( 'span.time-spent-quarter-active' ).length;

    if ( recentTook <= 0 )
        return;

    if ( ( recentTook - roundToQuarterOfHour( param ) ) < 0 )
        recentTook = 0;
    else
        recentTook = ( recentTook - roundToQuarterOfHour( param ) );

    recentTisheet.find( 'span.js-time-spent-quarter:eq('+ ( recentTook-1 ) +')' ).click();
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
        return rounded;
}

/**
*
*/
var getRunningStopwatch = function () 
{
    var runningStopwatch = $jQ( '#timesheet' ).find( 'span.octicon-playback-pause' );

    if( runningStopwatch.length == 0 )
        return undefined;

    return getTisheet( runningStopwatch );
}

