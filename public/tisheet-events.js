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

/** EVENTS ON OCTICONS */

/**
*
*/
$jQ( document ).on( 'mouseout', 'span.octicon-red', function()
{
    $jQ(this).removeClass( 'octicon-red' );
});