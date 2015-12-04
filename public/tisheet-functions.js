// AJAX SUCCESS HANDLERS

/**
*
*/
var descriptionFocusoutSuccessCallbackHandler = function( tisheet, obj )
{
    if ( obj.callback == undefined || obj.callback == '' )
        // '==' also matches null
        return;

    var command = obj.callback.command;

    if ( command === 'now' || command === 'go' || command === 'run' )
        runStopwatch( tisheet, command, true );

    else if ( command === 'spent' || command === 'took' || command === 'planned' )
        updateQuarterOfTime( tisheet, obj );

    else if ( command === 'since' && obj.callback.param != undefined )
    {
        updateQuarterOfTime( tisheet, obj );
        runStopwatch( tisheet, command, true );
    }

    tisheet.find( '.js-tisheet-description' ).val( obj.desc );
}

// HELPER FUNCTIONS

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

    if ( param == undefined )
    {
        showTisheetErrorMessage( tisheet, '/took requires an argument. Use it like this: /took 1h or /took 20min' );
        return;
    }

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

    // update time, when the tisheet has begun
    var time = new Date( Date.now() - (60000*param) ).toTimeString().slice( 0,5 )
    tisheet.find( 'span.js-tisheet-time-start' ).text( time );

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

/**
*
*/
var makeColumnsSortable = function()
{
    $jQ( '#columns' ).sortable(
    { 
        cursor: 'move',
        items: $jQ( 'li.js-column' ).not( '.js-column-empty' ),
        update: function( e, ui )
        {
            var cids = {};
            var items = $jQ(this).find( 'li.js-column' ).not( '.js-column-empty' );

            var position = 1;
            
            // collect all ids in the correct order and btw. reset position value
            items.each( function()
            {
                cids[$jQ(this).id()] = position++;
            });

            var url = getBaseUrl() + $jQ( '#timesheet' ).today() + '/columns';

            // update backend
            $jQ.ajax({
                url: url,
                type: 'put',
                data: { cids: cids }
            });
        }
    });
}

/**
*
*/
var toggleLoadingIcon = function( baseElement )
{
    if ( baseElement === undefined )
        return;

    $jQ( baseElement +' span.js-ajax-loader' ).toggleClass( 'element-hidden' );
    
}

/**
 * 
 */
var showTisheetErrorMessage = function( tisheet, message )
{
    var errorSpan = tisheet.find( 'span.js-tisheet-error' );
    errorSpan.toggleClass( 'element-invisible' );
    errorSpan.text( message );
    errorSpan.click( function()
    {
        $jQ(this).toggleClass( 'element-invisible' );
    });
}
