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

    if ( !commandValid( command ) )
    {
        // display error message
        showTisheetErrorMessage( tisheet, 'unknown command. use one of: now, took, spent, since' );
        // display description
        tisheet.find( '.js-tisheet-description' ).val( obj.desc );
        
        return;
    }

    if ( command === 'now' || command === 'go' || command === 'run' )
        runStopwatch( tisheet, command, true );

    else if ( isPastOrFutureCommand( command ) )
        updateQuarterOfTime( tisheet, obj );

    else if ( command === 'since' && obj.callback.param != undefined )
    {
        updateQuarterOfTime( tisheet, obj );
        runStopwatch( tisheet, command, true );
    }

    tisheet.find( '.js-tisheet-description' ).val( obj.desc );

    app.BrainSocket.message( 'tisheet.update.event',
    {   
        'tid': tisheet.id(),
        'lead': getSessionToken(),
        'value': obj.desc
    });
}

// HELPER FUNCTIONS

/**
*
*/
var cloneTisheet = function( elementToClone, latestElement, focus )
{
    var clonedElement = elementToClone.clone();

    clonedElement.insertBefore( elementToClone );
    clonedElement.removeClass( 'js-tisheet-clonable cc-element-hidden' );
    
    if ( latestElement !== undefined )
        // invoke manually to prevent asynchronous side effects
        latestElement.blur();

    if ( focus === undefined || focus )
        clonedElement.find( 'input.tisheet-description' ).focus();

    // add autocomplete functionality
    clonedElement.find( 'input.tisheet-description' ).autocomplete(
    {
        source: autocompleteItems,
        minLength: 2,
        delay: 100
    });

    return clonedElement;
}

/**
 */
var cloneTisheetIfLastOne = function( elementToClone, latestElement )
{
    var tisheetToClone = $jQ( 'div.js-tisheet-clonable' );
 
    if ( tisheetToClone.index() == 1 )
        // clone empty element
        cloneTisheet( tisheetToClone, undefined );
 }

/**
 * Creates a blank tisheet, by getting the hidden div.js-tisheet-clonable 
 * and invoking function cloneTisheet().
 *
 */
var blankTisheet = function( initialData )
{
    var tisheetToClone = $jQ( 'div.js-tisheet-clonable' );
    var tisheet = undefined;

    // do not clone if there are no tisheets yet!
    // >= because there might be an empty one at the end ..
    if ( tisheetToClone.index() >= 2 ) 
    {
        tisheet = tisheetToClone.prev();

        // value and id, further down the code, are only set, if this first tisheet has no value
        if ( tisheet.find( 'input.js-tisheet-description' ).val() != '' )
            tisheet = undefined;
    }

    if ( tisheet == undefined )
        tisheet = cloneTisheet( tisheetToClone );

    if ( initialData == undefined )
        return tisheet;

    tisheet.attr( 'id', initialData.tid );
    tisheet.find( 'input.js-tisheet-description' ).val( initialData.value );

    return tisheet;
}

// HELPER FUNCTIONS FOR STOPWATCHES

/** 
 * starts or stops the stopwatch for the given tisheet
 * 
 * @tisheet
 * @startOnly
 * @triggerEvent
 */
var toggleStopwatchStatus = function ( tisheet, startOnly, triggerEvent )
{
    var stopwatch = tisheet.find( 'span.js-octicon-stopwatch' );
    
    if ( stopwatch.isRunning() && !startOnly )
        stopStopwatch( tisheet, stopwatch, triggerEvent );
    else if ( !stopwatch.isRunning() )
        startStopwatch( tisheet, stopwatch, triggerEvent );
};

/**
 * 
 */
var stopStopwatch = function( tisheet, stopwatch, triggerEvent )
{
    if ( tisheet == undefined )
        tisheet = stopwatch.closest( 'div.js-tisheet' );

    // completes the quarter if it's done more than the half
    if ( minutesByTisheets[ tisheet.id() ] > 7 )
        triggerQuarterTimeSpentClick( tisheet );

    minutesByTisheets[ tisheet.id() ] = 0;

    // reset stopwatch
    clearInterval( interval );

    stopwatch.toggleClass( 'octicon-playback-play octicon-playback-pause cc-element-visible' );

    if ( !triggerEvent )
        return;

    app.BrainSocket.message( 'tisheet.stopwatch.update.event',
    {
        'tid': tisheet.id(),
        'lead': getSessionToken(),
        'trigger': !triggerEvent
    });
}

/**
 * 
 */
var startStopwatch = function( tisheet, stopwatch, triggerEvent )
{
    if ( tisheet == undefined )
        tisheet = stopwatch.closest( 'div.js-tisheet' );
    
    // start stopwatch with handler
    interval = setInterval( function()
    {
        checkTriggerQuarterTimeSpent( tisheet );
    }, 1000*60 );

    if ( minutesByTisheets[ tisheet.id() ] == undefined ) 
        minutesByTisheets[ tisheet.id() ] = 1;

    // update tisheet start field only once
    var time = tisheet.find( 'span.js-tisheet-time-start' );
    if ( time.text() == '' )
        time.text( new Date().toTimeString().substring(0,5) );

    stopwatch.toggleClass( 'octicon-playback-play octicon-playback-pause cc-element-visible' );

    if ( !triggerEvent )
        return;

    app.BrainSocket.message( 'tisheet.stopwatch.update.event',
    {
        'tid': tisheet.id(),
        'lead': getSessionToken(),
        'trigger': !triggerEvent
    });
}

// check whether a quarter of an hour has passed
var checkTriggerQuarterTimeSpent = function( tisheet )
{   
    var minutesCounter = minutesByTisheets[ tisheet.id() ] + 1;

    if ( minutesCounter <= 15 )
    {
        minutesByTisheets[ tisheet.id() ] = minutesCounter;
        return;
    }

    var nextQuarter = triggerQuarterTimeSpentClick( tisheet );

    // if the end was reached reset the interval and stopwatch icon
    if ( nextQuarter == undefined )
    {
        clearInterval( interval );

        toggleStopwatchStatus( tisheet );

        // TODO ZL write email or something

        return;
    }

    minutesByTisheets[ tisheet.id() ] = 0;
};

/**
 * updates the next time spent quarter
 */
var triggerQuarterTimeSpentClick = function( tisheet )
{
    // find the next not active quarter
    var nextQuarter = tisheet.find( 'span.js-time-spent-quarter' ).filter( function()
    {
        if ( !$jQ(this).hasClass( 'js-time-spent-quarter' ) )
            return false;

        if ( $jQ(this).hasClass( 'time-spent-quarter-active' ) )
            return false;

        return true;
    }).first();

    // if we've reached the end return undefined
    if ( nextQuarter.length == 0 )
        return undefined;

    nextQuarter.click();

    return nextQuarter;
};

/**
*
*/
var runStopwatch = function( tisheet, command, startOnly )
{
    tisheet.find( '.js-octicon-stopwatch' ).trigger( 'click', { name: command, startOnly: startOnly } );
}

/**
*   Sends an http put-request with time spent and when it has started.
*/
var updateTisheetTimeSpentQuarter = function( tisheet )
{
    // update object
    var url = getBaseUrl() + $jQ( '#timesheet' ).today() + '/tisheet/'+ tisheet.id();
    var count = tisheet.find( 'span.time-spent-quarter-active' ).length;
    var time = tisheet.find( 'span.js-tisheet-time-start' ).text();

    $jQ.ajax({
        url: url,
        type: 'put',
        data: {
            ts: count,
            tm: time
        },
        success: function( data )
        {
            updateTisheetTimeline();
            updateTisheetSummary();

            app.BrainSocket.message( 'tisheet.time.update.event',
            {
                'value': count,
                'tid': tisheet.id()
            });
        }
    });
};

/**
*
*/
var updateTisheetTimeSpentToday = function()
{
    count = $jQ( '#timesheet' ).find( 'span.time-spent-quarter-active' ).length;
    $jQ( 'span.js-time-spent-today' ).text( count/4 + 'h');
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
    
    else if ( param.indexOf( 'm' ) > 0 )
    {
        param = param.split( 'm' )[0];

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

    if ( isPastOrFutureCommand( obj.callback.command ) )
        return;

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
var markTimeSpentQuarterAsActive = function( span )
{
    // reset all coming quarters
    $jQ( span ).nextAll( 'span.js-time-spent-quarter' ).removeClass( 'time-spent-quarter-active' );

    // update current
    $jQ( span ).addClass( 'time-spent-quarter-active' );
    // update all previous quarters
    $jQ( span ).prevAll( 'span.js-time-spent-quarter' ).addClass( 'time-spent-quarter-active' );
}

/**
 *
 * @param {type} command
 * @returns {undefined} 
 */
var isPastOrFutureCommand = function( command )
{
    if ( isPastCommand( command ) )
        return true;
    
    if ( isFutureCommand( command ) )
        return true;
    
    return false;
}

/**
 * 
 * @param {type} command
 * @returns {Boolean} 
 */
var isPastCommand = function( command )
{
    if ( command === 'took' || command === 'spent' )
        return true;
    
    return false;
}

/**
 * 
 * @param {type} command
 * @returns {Boolean} 
 */
var isFutureCommand = function( command )
{
    if ( command === 'planned' )
        return true;
    
    return false;
}

var meetsKeydownExitCriteria = function( event, target )
{
    if ( meetsKeydownWrongKeyExitCriteria( event ) )
    {
        return true;
    }

    if ( meetsKeydownWrongFieldExitCriteria( target ) )
    {
        target.blur();
        return true;
    }

    return false;
}

/**
 *
 */
var meetsKeydownWrongKeyExitCriteria = function( event )
{
    if ( !enterKeyCode( event ) && !escapeKeyCode( event ) )
        return true;

    return false;
}

/**
 *
 */
var meetsKeydownWrongFieldExitCriteria = function( target )
{
    if ( target.hasClass( 'js-column-label-input' ) )
        return true; 

    if ( target.hasClass( 'js-column-item-label' ) )
        return true;

    return false;
}

/**
 *
 */
var enterKeyCode = function( event )
{
    if ( event.keyCode == 13 )
        return true;

    return false;
}

/**
 *
 */
var escapeKeyCode = function( event )
{
    if ( event.keyCode == 27 )
        return true;

    return false;
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
    $jQ( '#columns ul' ).sortable(
    { 
        cursor: 'move',
        items: '> li:not( .js-column-empty, .js-column-item-empty )',
        update: function( e, ui )
        {
            if ( ui.item.hasClass( 'js-column' ) )
            {
                var elementClass = 'js-column'
                var url = getBaseUrl() + $jQ( '#timesheet' ).today() + '/columns'
            }
            else if ( ui.item.hasClass( 'js-column-item' ) )
            {
                var elementClass = 'js-column-item'
                var url = getBaseUrl() + $jQ( '#timesheet' ).today() + '/columns/'+ $jQ(this).closest( 'li.js-column' ).attr( 'id' ) + '/items'
            }
            else
                return;

            var cids = {};
            var items = $jQ(this).find( 'li.'+ elementClass ).not( '.js-column-empty, .js-column-item-empty' );

            var position = 1;
            
            // collect all ids in the correct order and btw. reset position value
            items.each( function()
            {
                cids[$jQ(this).id()] = position++;
            });


            // update backend
            $jQ.ajax({
                url: url,
                type: 'put',
                data: { cids: cids }
            });
        },
        stop: function( e, ui )
        {
            if ( ui.item.hasClass( 'js-column' ) && ui.item.parent().hasClass( 'js-column-items' ) )
                $jQ(this).sortable( 'cancel' )
        },
        connectWith: '#columns ul li ul'
    });
}

/**
*
*/
var toggleLoadingIcon = function( baseElement )
{
    if ( baseElement === undefined )
        return;

    $jQ( baseElement +' span.js-ajax-loader' ).toggleClass( 'cc-element-hidden' );
    
}

/**
 * 
 * @param {type} command
 * @returns {Boolean}
 */
var commandValid = function( command )
{
    if ( command === null || command === undefined )
        return true;

    if ([ 'run', 'now', 'go' ]
        .concat([ 'took', 'spent', 'takes', 'planned' ])
        .concat([ 'since' ]).indexOf( command ) > -1 )
        return true;
    
    return false;
}

/**
 * 
 * @param {type} tisheet
 * @param {type} message
 * @returns {undefined}
 */
var showTisheetErrorMessage = function( tisheet, message )
{
    // first hide all errors
    hideTisheetErrorMessages();
    
    // now get the specific one
    var errorSpan = tisheet.find( 'span.js-tisheet-error' );
    
    // show it
    if ( errorSpan.hasClass( 'element-invisible' ) )
        errorSpan.toggleClass( 'element-invisible' );
    
    errorSpan.html( '<span class="octicon octicon-alert octicon-no-padding-left octicon-padding-right"></span>'+ message );
    
    // close on click
    errorSpan.click( function()
    {
        $jQ(this).addClass( 'element-invisible' );
    });
}

/**
 * 
 * @returns {undefined}
 */
var hideTisheetErrorMessages = function()
{
    $jQ( '#timesheet span.js-tisheet-error' ).addClass( 'element-invisible' );
}

/**
 * 
 * @param {type} textarea
 * @returns {adjustHeightOfTextarea}
 */
var adjustHeightOfTextarea = function( textarea )
{
    textarea.style.height = "1px";
    textarea.style.height = (textarea.scrollHeight) + "px"; 
}

/**
 * adjust height of all visible textareas on load
 */
var adjustHeightOfEachTextarea = function( rootElement )
{
    if ( rootElement == undefined )
        rootElement = '#columns';

    $jQ( rootElement +' textarea:visible' ).each( function()
    {
        adjustHeightOfTextarea( this );
    });
}
