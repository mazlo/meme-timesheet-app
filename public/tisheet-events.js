/** EVENTS ON TISHEETS */

/** EVENTS ON TISHEET OCTICONS */

//
$jQ( document ).on( 'click', '.timesheet-options span.octicon', function()
{
    var octicon = $jQ(this);
    var status = octicon.hasClass( 'octicon-active' ) ? true : false;

    var url = getBaseUrl( '' ) + 'user/profile';

    var icon = octicon.hasClass( 'octicon-server' ) ? 'cl' : 'gl';

    if( icon == '' )
        return;

    $jQ.ajax({
        url: url,
        type: 'put',
        data: {
            lm: icon,
            vl: !status
        },
        success: function()
        {
            var element = octicon.hasClass( 'octicon-server' ) ? '#columns' : '#story';

            if ( status )
            {
                $jQ( element ).hide()
                octicon.removeClass( 'octicon-active' )
            }
            else
            {
                $jQ( element ).show()
                octicon.addClass( 'octicon-active' )

                if ( element == '#columns' )
                	adjustHeightOfEachTextarea( element );
            }
        }
    });
});

//
$jQ( document ).on( 'click', '.tisheet span.octicon-list-unordered', function()
{
    var item = getTisheet( this );

    // do not handle items with no id
    if ( item.id() === 'undefined' )
        return;

    var url = getBaseUrl() + $jQ( '#timesheet' ).today() +'/tisheet/'+ item.id() +'/same-as';

    $jQ.ajax({
        url: url,
        success: function( data )
        {
            $jQ( '#summary-same-as' ).html( data );
        }
    });
});


//
$jQ( document ).on( 'click', 'tr.js-tisheet span.octicon-trashcan', function()
{
    // first: mark as red to indicate warning
    if ( $jQ(this).is( ':not( .octicon-red )' ) )
    {
        $jQ(this).toggleClass( 'octicon-red' );
        return;
    }
    
    var item = getTisheet( this );

    // do not handle items with no id
    if ( item.id() === "undefined" )
        return;

    var url = getBaseUrl() + $jQ( '#timesheet' ).today() +'/tisheet/'+ item.id();

    $jQ.ajax({
        url: url,
        type: 'delete',
        success: function( data )
        {
            if ( data != 'true' )
                return;

            item.remove();

            // place new tisheet
            cloneTisheetIfLastOne();

            // update total time spent for the day
            updateTisheetTimeSpentToday();

            updateTisheetTimeline();
            updateTisheetSummary();
        }
    });
}); 

/**
* persists the status of the info box for the Tisheet
*/
$jQ( document ).on( 'click', '.octicon-info', function()
{
    var item = getTisheet( this );

    // do not handle items with no id
    if ( item.id() === "undefined" )
        return;

    var note = item.find( '.js-tisheet-note' );

    note.toggleClass( 'element-hidden' );

    if ( note.is( ':visible' ) )
    {
        note.find( 'textarea' ).each( function()
        {
            // workaround because function adjustHeightOfTextarea cannot deal with jQuery objects
            adjustHeightOfTextarea( this );
            
            this.focus();
        })
    }

    var url = getBaseUrl() + $jQ( '#timesheet' ).today() + '/tisheet/'+ item.id() +'/note';
    
    $jQ.ajax({
        url: url,
        type: 'put',
        data: { na: note.is( ':visible' ) }
    });
});

//
$jQ( document ).on( 'click', '.js-tisheet-move', function()
{
    var tisheet = getTisheet( this );

    // do not handle items with no id
    if( tisheet.id() === 'undefined' )
        return;

    var url = getBaseUrl() + $jQ( '#timesheet' ).today() +'/tisheet/'+ tisheet.id();

    $jQ.ajax({
        url: url,
        type: 'put',
        data: { mv: 'tomorrow' },
        success: function( data )
        {
            // remove current element
            tisheet.remove();
            // place new tisheet
            cloneTisheetIfLastOne();
        }
    });

    return false;
});

var interval;
var minutesByTisheets = [];

//
$jQ( document ).on( 'click', '.js-octicon-stopwatch', function( event, action )
{
    startOnly = ( action != undefined && action.startOnly != undefined ? action.startOnly : false );

    var requestedStopwatch = $jQ(this);
    var tisheet = requestedStopwatch.closest( 'tr.js-tisheet' );

    var requestedStopwatchId = getTisheetId( requestedStopwatch );

    // change status of running stopwatch

    var runningStopwatch = getRunningStopwatch();
    var runningStopwatchId = undefined;

    if ( runningStopwatch != undefined )
    {
        runningStopwatchId = runningStopwatch.id();

        // but only if it's not the current stopwatch
        if ( runningStopwatchId != requestedStopwatchId )
            toggleStopwatchStatus( runningStopwatch, false );
    }

    // change status of stopwatch now

    if ( requestedStopwatchId == 'undefined' )
        // register for post update description field
        descriptionChangeListener.push( { callback: toggleStopwatchStatus, startOnly: startOnly } );
    else
        // change status of pressed stopwatch now
        toggleStopwatchStatus( tisheet, startOnly );

    var statusNew = requestedStopwatch.hasClass( 'octicon-playback-play' ) ? 'stopped' : 'running';

    app.BrainSocket.message( 'tisheet.stopwatch.update.event',
    {
        'lead': getSessionToken(),
        'tido': runningStopwatchId != requestedStopwatchId ? runningStopwatchId : undefined,
        'tidr': requestedStopwatchId,
        'value': statusNew
    });
});

// starts or stops the stopwatch for the given tisheet
var toggleStopwatchStatus = function ( tisheet, startOnly )
{
    var stopwatch = tisheet.find( 'span.js-octicon-stopwatch' );
    
    if ( stopwatch.hasClass( 'octicon-playback-pause' ) && !startOnly )
        startStopwatch( tisheet, stopwatch );
    else if ( !stopwatch.hasClass( 'octicon-playback-pause' ) )
        stopStopwatch( tisheet, stopwatch );
};

/**
 * 
 */
var startStopwatch = function( tisheet, stopwatch )
{
    var tisheet = stopwatch.closest( 'tr.js-tisheet' );

    // completes the quarter if it's done more than the half
    if ( minutesByTisheets[ tisheet.id() ] > 7 )
        triggerQuarterTimeSpentClick( tisheet );

    minutesByTisheets[ tisheet.id() ] = 0;

    // reset stopwatch
    clearInterval( interval );

    stopwatch.toggleClass( 'octicon-playback-play octicon-playback-pause element-visible' );
}

/**
 * 
 */
var stopStopwatch = function( tisheet, stopwatch )
{
    var tisheet = stopwatch.closest( 'tr.js-tisheet' );
    
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

    stopwatch.toggleClass( 'octicon-playback-play octicon-playback-pause element-visible' );
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
*
*/
$jQ( document ).on( 'click', 'span.js-time-spent-quarter', function()
{
    // mark time spent quarter as active
    markTimeSpentQuarterAsActive( this );

    // update ui
    var count = $jQ(this).parent().find( 'span.time-spent-quarter-active' ).length;
    $jQ(this).closest( 'tr.js-tisheet' ).find( 'span.js-tisheet-time-spent' ).text( count/4 + 'h');

    // register feature for post update

    var item = $jQ(this).closest( 'tr.js-tisheet' );
    
    if ( item.attr( 'id' ) == 'undefined' )
        // tisheet without an id -> update when tisheet was saved
        descriptionChangeListener.push( { callback: updateTisheetTimeSpentQuarter, startOnly: false } );
    else
        updateTisheetTimeSpentQuarter( item );

    // update total time spent for the day -> static
    updateTisheetTimeSpentToday();
});

/** EVENTS ON COLUMNS */

/**
*
*/
$jQ( document ).on( 'focusin', 'div.js-column-label input', function()
{
    oldColumnLabel = $jQ( this ).val();
});

//
$jQ( document ).on( 'focusin', 'textarea.js-column-item-label', function()
{
    oldColumnItemLabel = $jQ( this ).val();
});

//
$jQ( document ).on( 'focusout', 'div.js-column-label input', function()
{
    var label = $jQ(this).val().trim();
    
    if ( label == '' )
        return; // ignore empty values

    if ( oldColumnLabel == label )
        return; // ignore if nothing changed

    toggleLoadingIcon( '#columns' );
    
    var column = $jQ(this).closest( 'li.js-column' );
    var clonedColumn = column.clone();
    
    // post label and update id
    $jQ.ajax({
        url: getBaseUrl() + $jQ( '#timesheet' ).today() +'/columns/'+ column.attr( 'id' ),
        type: 'put',
        data: { lb: label, ps: column.index()+1 },
        success: function( data )
        {
            if ( data.status == 'error' )
                return;

            clonedColumn.attr( 'id', data.id );

            toggleLoadingIcon( '#columns' );
        }
    });

    // ignore cloning when we are not in an empty column
    if ( column.is( ':not( .js-column-empty )' ) )
        return;

    // clone 
    column.find( 'input' ).val( '' );
    column.removeAttr( 'id' );

    clonedColumn.removeClass( 'js-column-empty element-invisible' );
    clonedColumn.insertBefore( column );

    makeColumnsSortable();
});

// 
$jQ( document ).on( 'focusout', 'textarea.js-column-item-label', function()
{
    var label = $jQ(this).val().trim();

    if ( label === '' )
        return; // ignore empty values

    if ( oldColumnItemLabel === label )
        return; // ignore if nothing changed

    var columnItem = $jQ(this).closest( 'li.js-column-item' );
    var column = columnItem.closest( 'li.js-column' )

    // ignore when parent is not saved yet
    if ( column.hasClass( 'element-invisible' ) )
        return;

    toggleLoadingIcon( '#columns' );

    var clonedItem = columnItem.clone();

    // post label and update id
    $jQ.ajax({
        url: getBaseUrl() + $jQ( '#timesheet' ).today() +'/columns/'+ column.attr( 'id' ) +'/item/'+ columnItem.attr( 'id' ),
        type: 'put',
        data: { lb: label },
        success: function( data )
        {
            if ( data.status == 'error' )
                return;

            clonedItem.attr( 'id', data.id );

            toggleLoadingIcon( '#columns' );
        }
    });

    // ignore cloning when we are not in an empty column-item
    if ( columnItem.is( ':not( .js-column-item-empty )' ) )
        return;

    columnItem.find( 'textarea' ).val( '' );
    columnItem.find( 'textarea' ).css( 'height', '25px' );
    columnItem.removeAttr( 'id' );

    clonedItem.find( 'textarea' ).val( label );
    clonedItem.removeClass( 'js-column-item-empty element-invisible' );
    clonedItem.insertBefore( columnItem );
});

//
$jQ( document ).on( 'click', 'div.js-column-label span.octicon-trashcan', function()
{
    // first: mark as red to indicate warning
    if ( $jQ(this).is( ':not( .octicon-red )' ) )
    {
        $jQ(this).toggleClass( 'octicon-red' );
        return;
    }

    toggleLoadingIcon( '#columns' );

    var column = $jQ(this).closest( 'li.js-column' );

    column.hide();

    $jQ.ajax({
        url: getBaseUrl() + $jQ( '#timesheet' ).today() +'/columns/'+ column.attr( 'id' ),
        type: 'delete',
        success: function( data )
        {
            if ( data.status != 'ok' )
                return;

            column.remove();

            toggleLoadingIcon( '#columns' );
        }
    });
});

//
$jQ( document ).on( 'click', 'li.js-column-item span.octicon-trashcan', function()
{
    // first: mark as red to indicate warning
    if ( $jQ(this).is( ':not( .octicon-red )' ) )
    {
        $jQ(this).toggleClass( 'octicon-red' );
        return;
    }

    toggleLoadingIcon( '#columns' );

    var columnItem = $jQ(this).closest( 'li.js-column-item' );
    var column = columnItem.closest( 'li.js-column' );

    columnItem.hide();

    $jQ.ajax({
        url: getBaseUrl() + $jQ( '#timesheet' ).today() +'/columns/'+ column.attr( 'id' ) +'/item/'+ columnItem.attr( 'id' ),
        type: 'delete',
        success: function( data )
        {
            if ( data.status != 'ok' )
                return;

            columnItem.remove();

            toggleLoadingIcon( '#columns' );
        }
    });
});

//
$jQ( document ).on( 'keydown', '#columns textarea, div.js-tisheet-note textarea', function()
{
    adjustHeightOfTextarea( this );
});

//
$jQ( document ).on( 'click', 'li.js-column div.column-item-color-palette span', function()
{
    var color = $jQ(this).attr( 'color' );
    var column = $jQ(this).closest( '.js-column' );

    var url = getBaseUrl() + $jQ( '#timesheet' ).today() +'/columns/'+ column.attr( 'id' );
    $jQ.ajax({
        url: url,
        type: 'put',
        data: { cl : color },
        success: function( data )
        {
            column.css( 'background-color', color );
            column.find( '.js-column-label-input' ).css( 'background-color', color );
        }
    });
});

/** EVENTS ON OCTICONS */

/**
*
*/
$jQ( document ).on( 'mouseout', 'span.octicon-red', function()
{
    $jQ(this).removeClass( 'octicon-red' );
});

