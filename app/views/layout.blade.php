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

	$jQ( document ).on( 'keydown', '.description', function( event )
	{
		if ( event.which != 9 )
			return;

		var tr = $jQ( '.item.element-hidden:last' );

		if ( tr.index() - $jQ(this).closest( '.item' ).index() > 1 )
			return;

		var trClone = tr.clone();

		trClone.find( '.js-tisheet-no' ).text( tr.index()+ '.' );
		trClone.insertBefore( tr );
		trClone.removeClass( 'element-hidden' );
	});

	$jQ( document ).on( 'focusout', '.description', function()
	{
		var value = $jQ(this).val();
		var item = $jQ(this).closest( '.item' );

		if ( value == '' )
			return;

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

		var url = '{{ url( "tisheets" ) }}' + '/{{ date( "Y-m-d", time() ) }}' + '/tisheet/'+ item.attr( 'id' );

		$jQ.ajax({
			url: url,
			type: 'put',
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

		// update object
		var item = $jQ(this).closest( '.item' );
		var url = '{{ url( "tisheets" ) }}' + '/{{ date( "Y-m-d", time() ) }}' + '/tisheet/'+ item.attr( 'id' );
		
		$jQ.ajax({
			url: url,
			type: 'put',
			data: {
				ts: count
			},
			success: function( data )
			{
				if ( data == 'true' )
					itemUpdateConfirmation( item );
			}
		});
	});

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
		if ( $jQ( '#summary' ).is( ':not(:visible)' ) )
			return;

		$jQ( '.js-show-summary' ).click();
	};

	//
	$jQ( document ).on( 'click', '.js-show-summary', function()
	{
		var url = '{{ url( "tisheets" ) }}' + '/{{ date( "Y-m-d", time() ) }}' + '/summary'

		$jQ.ajax({
			url: url,
			method: 'get',
			success: function( data )
			{
				$jQ( '#summary' ).html( data );
				$jQ( '#summary' ).show();
			}
		});
	});

</script>

</body>

</html>
