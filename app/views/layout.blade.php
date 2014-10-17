<!doctype html>
<html lang='de'>
<head>
    <meta charset='UTF-8'>
    <title>Timesheet</title>

	<!-- browser icon -->
	<link rel="shortcut icon" href='{{ url( "favicon.ico" ) }}' />

    <link rel='stylesheet' type='text/css' href='{{ url( "main.css" ) }}' />
	<link rel='stylesheet' type='text/css' href='{{ url( "jquery-ui.css" ) }}' }} />
	<link rel='stylesheet' type='text/css' href='{{ url( "octicons.css" ) }}' }} />
	
	<script type='text/javascript' src='{{ url( "jquery-1.8.2.js" ) }}'></script>
	<script type='text/javascript' src='{{ url( "jquery-ui-1.9.2.custom.js" ) }}'></script>

	<script type='text/javascript'>
		<!-- this is to prevent conflicts with prototype and jquerytools -->
		$jQ = jQuery.noConflict();
	</script>
</head>

<body>

<div id='wrapper'>

	<div id='header'>

		@yield( 'header' )

	</div>

	<div id='content'>

		@yield( 'content' )

	</div>

	<div id='footer'>

		@yield( 'footer' )
		
	</div>
	
</div> 
{{-- end wrapper --}}

<script type='text/javascript'>

	callbacksTisheet = [];

	$jQ( function()
	{
		updateTisheetTotalTimeSpent();
	});

	// add new line to table or focus next textfield of next line
	$jQ( document ).keydown( function( event )
	{
		if ( event.keyCode != 13 )
			return;

		var item = $jQ( event.target ).closest( '.item' );

		// the line to clone
		var tr = $jQ( 'tr.js-item-clonable' );

		if ( item.hasClass( 'item' ) )
		{
			// event was fired in textarea
			
			if ( $jQ( event.target ).attr( 'value' ) == '' )
				// ignore when fired from empty textfield
				return;

			if ( tr.index() - item.index() > 1 )
			{
				// focus next textfield when fired NOT from the last textfield
				item.next().find( 'input.tisheet-description' ).focus();
				return;
			}
		} 
		
		else if ( tr.index() == 2 ) 
		{
			// event was fired from document

			// focus first textfield if it is empty
			var textfield = tr.prev().find( 'input.tisheet-description' );
			
			if ( textfield.val() == '' )
			{
				textfield.focus();
				return;
			}
		}

		var trClone = tr.clone();

		trClone.insertBefore( tr );
		trClone.find( '.js-tisheet-no' ).text( trClone.index()+ '.' );
		trClone.removeClass( 'js-item-clonable element-hidden' );
		trClone.find( 'input.tisheet-description' ).focus();
	});

	$jQ( document ).on( 'focusin', 'input.tisheet-description', function()
	{
		oldDescription = $jQ( this ).val();
	});

	$jQ( document ).on( 'focusin', 'textarea.tisheet-note', function()
	{
		oldNote = $jQ( this ).val();
	});

	//
	$jQ( document ).on( 'focusout', 'input.tisheet-description', function()
	{
		var value = $jQ(this).val();
		var item = $jQ(this).closest( '.item' );

		if ( oldDescription == value )
			return;	// ignore if nothing changed

		if ( value.trim() == '' )
			return;	// ignore empty values

		// iterate words of the textfield
		var startTime;
		var contexts;
		var words = value.split( ' ' );

		// and collect all startTime, starting with an @
		words.forEach( function( word )
		{
			if ( word.indexOf( '@' ) == 0 )
			{
				startTime = word;
				return;
			}
			else if ( word.indexOf( '#' ) == 0 )
			{
				contexts = word;
				return;
			}
		});

		var hasId = item.attr( 'id' ) != 'undefined' ? true : false;

		var url = '{{ url( "tisheets" ) }}/' + $jQ( '#timesheet' ).attr( 'day' ) + ( hasId ? '/tisheet/'+ item.attr( 'id' ) : '' );
		var type = hasId ? 'put' : 'post';

		$jQ.ajax({
			url: url,
			type: type,
			data: {
				vl: value,
				st: startTime,
				cx: contexts
			},
			success: function( data )
			{
				if ( data == 'true' )
					itemUpdateConfirmation( item );

				else if ( data > 0 )
					item.attr( 'id', data );

				firePostUpdateActions( item );
			}
		});
	});

	//
	$jQ( document ).on( 'focusout', 'textarea.tisheet-note', function()
	{
		var value = $jQ(this).val();
		var item = $jQ(this).closest( '.item' );

		if ( oldNote == value )
			return;	// ignore if nothing changed

		var type = value.trim() == '' ? 'delete' : 'put';

		var url = '{{ url( "tisheets" ) }}/' + $jQ( '#timesheet' ).attr( 'day' ) + '/tisheet/'+ item.attr( 'id' ) +'/note';

		$jQ.ajax({
			url: url,
			type: type,
			data: {
				nt: value.trim()
			},
			success: function( data )
			{
				if ( data == 'true' )
					itemUpdateConfirmation( item );

				else if ( data > 0 )
					item.attr( 'id', data );
			}
		});
	});

	// 
	$jQ( document ).on( 'change', '.js-tisheet-planned', function()
	{
		var item = $jQ(this).closest( '.item' );

		if ( item.attr( 'id' ) == 'undefined' )
			callbacksTisheet.push( updateTisheetIsPlanned );
		else
			updateTisheetIsPlanned( item );
	});

	$jQ( document ).on( 'click', '.time-spent-quarter', function()
	{
		// reset all coming quarters
		$jQ(this).nextAll( '.time-spent-quarter' ).removeClass( 'time-spent-quarter-active' );

		// update current
		$jQ(this).addClass( 'time-spent-quarter-active' );
		// update all previous quarters
		$jQ(this).prevAll( '.time-spent-quarter' ).addClass( 'time-spent-quarter-active' );

		// update ui
		var count = $jQ(this).parent().find( '.time-spent-quarter-active' ).length;
		$jQ(this).closest( '.item' ).find( '.tisheet-col-total' ).text( count/4 + 'h');

		var item = $jQ(this).closest( '.item' );
		
		if ( item.attr( 'id' ) == 'undefined' )
			callbacksTisheet.push( updateTisheetTimeSpent );
		else
			updateTisheetTimeSpent( item );

		// update total time spent for the day
		updateTisheetTotalTimeSpent();
	});

	//
	var updateTisheetIsPlanned = function( item )
	{
		// update object
		var url = '{{ url( "tisheets" ) }}/' + $jQ( '#timesheet' ).attr( 'day' ) + '/tisheet/'+ item.attr( 'id' );
		var planned = item.find( '.js-tisheet-planned' ).is( ':checked' );

		$jQ.ajax({
			url: url,
			type: 'put',
			data: {
				pl: planned
			}
		});
	};

	//
	var updateTisheetTimeSpent = function( item )
	{
		// update object
		var url = '{{ url( "tisheets" ) }}/' + $jQ( '#timesheet' ).attr( 'day' ) + '/tisheet/'+ item.attr( 'id' );
		var count = item.find( '.time-spent-quarter-active' ).length;

		$jQ.ajax({
			url: url,
			type: 'put',
			data: {
				ts: count
			}
		});
	};

	//
	var updateTisheetTotalTimeSpent = function()
	{
		count = $jQ( '#timesheet' ).find( '.time-spent-quarter-active' ).length;
		$jQ( '.js-tisheet-today-total' ).text( count/4 + 'h');
	}

	//
	var itemUpdateConfirmation = function( item )
	{
		if ( item == undefined )
			return;

		var check = item.find( '.js-tisheet-check' );
		check.show();
		check.fadeOut( 2000 );
	};

	// 
	var firePostUpdateActions = function( item )
	{
		// invoke callbacks
		for ( var i=0; i<callbacksTisheet.length; i++ )
			callbacksTisheet.pop()(item);

		// 
		if ( $jQ( '#summary' ).is( ':not(:visible)' ) )
			return;

		$jQ( '.js-show-summary' ).click();
	};

	//
	$jQ( document ).on( 'click', '.octicon-trashcan', function()
	{
		var item = $jQ(this).closest( '.item' );

		// do not delete items with no id
		if ( item.attr( 'id' ) == 'undefined' )
			return;

		var url = '{{ url( "tisheets" ) }}/' + $jQ( '#timesheet' ).attr( 'day' ) +'/tisheet/'+ item.attr( 'id' );

		$jQ.ajax({
			url: url,
			type: 'delete',
			success: function( data )
			{
				if ( data != 'true' )
					return;

				item.remove();

				// update total time spent for the day
				updateTisheetTotalTimeSpent();
			}
		});
	});

	//
	$jQ( document ).on( 'click', '.octicon-info', function()
	{
		var item = $jQ(this).closest( '.item' );
		var note = item.find( '.js-tisheet-note' );

		note.toggleClass( 'element-hidden' );
		note.find( 'textarea' ).focus();
	});

	//
	$jQ( document ).on( 'click', '.js-show-summary', function()
	{
		$jQ( '#summaryWrapper' ).show();

		var url = $jQ(this).attr( 'href' );

		$jQ.ajax({
			url: url,
			type: 'get',
			success: function( data )
			{
				$jQ( '#summary' ).html( data );
			}
		});

		return false;
	});

	//
	$jQ( document ).on( 'click', '.js-get-summary', function()
	{
		var url = $jQ(this).attr( 'href' );

		$jQ.ajax({
			url: url,
			type: 'get',
			success: function( data )
			{
				$jQ( '#summary' ).html( data );
			}
		});

		return false;
	});

	//
	$jQ( document ).on( 'click', '.js-button', function()
	{
		$jQ( this ).closest( '.js-button-group' ).find( '.js-button' ).each( function()
		{
			// remove js-button-active class for all elements

			if( !$jQ( this ).hasClass( 'js-button-active' ) )
				return;

			$jQ( this ).toggleClass( 'js-button-active' );
		});

		// active js-button-active class for current element
		$jQ( this ).toggleClass( 'js-button-active' );
	});

	//
	$jQ( document ).on( 'hover', '.js-enable-trashcan', function() 
	{
		$jQ(this).find( '.octicon-trashcan, .octicon-info' ).toggleClass( 'element-invisible' );
	});

</script>

</body>

</html>
