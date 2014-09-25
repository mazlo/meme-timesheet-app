<!doctype html>
<html lang='de'>
<head>
    <meta charset='UTF-8'>
    <title>Timesheet</title>

    <link rel='stylesheet' type='text/css' href='{{ url( "main.css" ) }}' />
	<link rel='stylesheet' type='text/css' href='{{ url( "jquery-ui.css" ) }}' }} />
	<link rel='stylesheet' type='text/css' href='{{ url( "octicons/octicons.css" ) }}' }} />
	
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

	// add new line to table or focus next textfield of next line
	$jQ( document ).keydown( function( event )
	{
		if ( event.keyCode != 13 )
			return;

		var item = $jQ( event.target ).closest( '.item' );

		// the line to clone
		var tr = $jQ( '.item.element-hidden:last' );
		
		if ( item.hasClass( 'item' ) )
		{
			// event was fired in textarea
			
			if ( $jQ( event.target ).attr( 'value' ) == '' )
				// ignore when fired from empty textfield
				return;

			if ( tr.index() - item.index() > 1 )
			{
				// focus next textfield when fired NOT from the last textfield
				item.next().find( '.description' ).focus();
				return;
			}
		} 

		var trClone = tr.clone();

		trClone.find( '.js-tisheet-no' ).text( tr.index()+ '.' );
		trClone.insertBefore( tr );
		trClone.removeClass( 'element-hidden' );
		trClone.find( '.description' ).focus();
	});

	$jQ( document ).on( 'focusin', '.description', function()
	{
		oldDescription = $jQ( this ).val();
	});

	//
	$jQ( document ).on( 'focusout', '.description', function()
	{
		var value = $jQ(this).val();
		var item = $jQ(this).closest( '.item' );

		if ( oldDescription == value )
			return;	// ignore if nothing changed

		if ( value.trim() == '' )
			return;	// ignore empty values

		// iterate words of the textfield
		var contexts;
		var words = value.split( ' ' );

		// and collect all contexts, starting with an @
		words.forEach( function( word )
		{
			if ( word.indexOf( '@' ) != 0 )
				return;

			contexts = word;
			return;
		});

		var hasId = item.attr( 'id' ) != 'undefined' ? true : false;

		var url = '{{ url( "tisheets" ) }}/' + $jQ( '#timesheet' ).attr( 'day' ) + ( hasId ? '/tisheet/'+ item.attr( 'id' ) : '' );
		var type = hasId ? 'put' : 'post';

		$jQ.ajax({
			url: url,
			type: type,
			data: {
				vl: value,
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
	});

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

	$jQ( document ).on( 'click', '.js-tisheet-delete', function()
	{
		var item = $jQ(this).closest( '.item' );
		var url = '{{ url( "tisheets" ) }}/' + $jQ( '#timesheet' ).attr( 'day' ) +'/tisheet/'+ item.attr( 'id' );

		$jQ.ajax({
			url: url,
			type: 'delete',
			success: function( data )
			{
				if ( data != 'true' )
					return;

				item.remove();
			}
		});
	});

	//
	$jQ( document ).on( 'click', '.js-show-summary', function()
	{
		var url = '{{ url( "tisheets" ) }}' + '/{{ date( "Y-m-d", time() ) }}' + '/summary'

		$jQ.ajax({
			url: url,
			type: 'get',
			success: function( data )
			{
				$jQ( '#summary' ).html( data );
				$jQ( '#summary' ).show();
			}
		});
	});

	//
	$jQ( document ).on( 'click', '.js-tisheet-today', function() 
	{
		var url = '{{ url( "tisheets" ) }}' + '/{{ date( "Y-m-d", time() ) }}';
		window.location = url;
	});

</script>

</body>

</html>
