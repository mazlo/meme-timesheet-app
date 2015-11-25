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
$jQ( document ).on( 'focusin', 'input.js-column-label', function()
{
    oldColumnLabel = $jQ( this ).val();
});

//
$jQ( document ).on( 'focusin', 'textarea.js-column-item-label', function()
{
    oldColumnItemLabel = $jQ( this ).val();
});

//
$jQ( document ).on( 'focusout', 'input.js-column-label', function()
{
    var label = $jQ(this).val().trim();
    
    if ( label == '' )
        return; // ignore empty values

    if ( oldColumnLabel == label )
        return; // ignore if nothing changed

    var column = $jQ(this).closest( 'li.js-column' );
    var clonedColumn = column.clone();

    // post label and update id
    $jQ.ajax({
        url: getBaseUrl() + $jQ( '#timesheet' ).today() +'/columns/'+ column.attr( 'id' ),
        type: 'put',
        data: { lb: label },
        success: function( data )
        {
            if ( data.status == 'error' )
                return;

            clonedColumn.attr( 'id', data.id );
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
});

// 
$jQ( document ).on( 'focusout', 'textarea.js-column-item-label', function()
{
    var label = $jQ(this).val().trim();

    if ( label == '' )
        return; // ignore empty values

    if ( oldColumnItemLabel == label )
        return; // ignore if nothing changed

    var columnItem = $jQ(this).closest( 'li.js-column-item' );
    var column = columnItem.closest( 'li.js-column' )

    // ignore when parent is not saved yet
    if ( column.hasClass( 'element-invisible' ) )
        return;

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
$jQ( document ).on( 'click', 'li.js-column-item span.octicon-trashcan', function()
{
    // first: mark as red to indicate warning
    if ( $jQ(this).is( ':not( .octicon-red )' ) )
    {
        $jQ(this).toggleClass( 'octicon-red' );
        return;
    }

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