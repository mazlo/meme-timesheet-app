/** EVENTS ON TISHEETS */

/** EVENTS ON TISHEET OCTICONS */

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
    note.find( 'textarea' ).focus();

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

/**
*
*/
$jQ( document ).on( 'click', 'span.js-time-spent-quarter', function()
{
    // reset all coming quarters
    $jQ(this).nextAll( 'span.js-time-spent-quarter' ).removeClass( 'time-spent-quarter-active' );

    // update current
    $jQ(this).addClass( 'time-spent-quarter-active' );
    // update all previous quarters
    $jQ(this).prevAll( 'span.js-time-spent-quarter' ).addClass( 'time-spent-quarter-active' );

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
$jQ( document ).on( 'keydown', '#columns textarea', function()
{
    adjustHeightOfTextarea( this );
});

/** EVENTS ON OCTICONS */

/**
*
*/
$jQ( document ).on( 'mouseout', 'span.octicon-red', function()
{
    $jQ(this).removeClass( 'octicon-red' );
});

