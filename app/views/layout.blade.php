<!doctype html>
<html lang='de'>
<head>
    <meta charset='UTF-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Timesheet</title>

	<!-- browser icon -->
	<link rel="shortcut icon" href='{{ url( "favicon.ico" ) }}' />

	<link rel='stylesheet' type='text/css' href='{{ url( "jquery-ui.css" ) }}' />
	<link rel='stylesheet' type='text/css' href='{{ url( "octicons.css" ) }}' />
	<link rel='stylesheet' type='text/css' href='{{ url( "ionicons.css" ) }}' />
	<link rel='stylesheet' type='text/css' href='{{ url( "w3.css" ) }}' />
    <link rel='stylesheet' type='text/css' href='{{ url( "main.css" ) }}' />
    <link rel='stylesheet' type='text/css' href='{{ url( "datepicker.css" ) }}' />
	
	<script type='text/javascript' src='{{ url( "jquery-1.8.2.js" ) }}'></script>
	<script type='text/javascript' src='{{ url( "jquery-ui-1.9.2.custom.js" ) }}'></script>

	<script type='text/javascript' src='{{ url( "jquery.datepicker.min.js" ) }}'></script>
	<script type="text/javascript" src='{{ url( "brain-socket.min.js" ) }}'></script>

	<script type='text/javascript'>
		<!-- this is to prevent conflicts with prototype and jquerytools -->
		$jQ = jQuery.noConflict();
	</script>

	<!-- js-functions for tisheets -->
	<script type='text/javascript' src='{{ url( "tisheet-functions.js" ) }}'></script>
	<script type='text/javascript' src='{{ url( "tisheet-events.js" ) }}'></script>
	<script type='text/javascript' src='{{ url( "tisheet-sockets.js" ) }}'></script>
</head>

<body>

<div id='wrapper'>

	<div id='header' class='w3-container w3-padding-hor-12'>

		<div class='w3-col l10 m8 s10  w3-text-light-grey'>
			<span class='ion-ios-stopwatch-outline' style='font-size: 32px'></span>
			@if ( Auth::check() )
			<h2 class='w3-show-inline-block'><span class='w3-hide-medium w3-hide-small'>ya timesheet for</span> <span class='w3-hide-small'>@if( $today == $todayForReal ) today - @endif</span> <span class='w3-hide-small'>{{ date( 'l', $todayAsTime ) }},</span> {{ date( 'dS M.', $todayAsTime ) }}</h2>
			@else
			<h2 class='w3-show-inline-block'>Welcome<span class='w3-hide-small'> to ya timesheet</span></h2>
			@endif
		</div>

		@yield( 'header' )

	</div>

	<div id='content' class='w3-container'>

		@yield( 'content' )

	</div>

	<div id='footer' class='w3-container'>

		@yield( 'footer' )
		
	</div>
	
</div> 
{{-- end wrapper --}}

<script type='text/javascript'>

@if( Auth::check() ) {{-- add javascript functionality only when user is logged in --}}

	descriptionChangeListener = [];
	autocompleteItems = [];

	$jQ( function()
	{
		// extend jquery functionality
		$jQ.fn.extend(
		{
			id: function()
			{
				// used on <div> with id-attribute
				if ( this.prop( 'tagName' ) === 'DIV' && this.attr( 'id' ) !== undefined )
					return this.attr( 'id' );

				// used on <li> with id-attribute
				if ( this.prop( 'tagName' ) === 'LI' && this.attr( 'id' ) !== undefined )
					return this.attr( 'id' );

				return 'undefined';
			},

			// used on <div.timesheet> with day-attribute
			today: function()
			{
				if ( this.prop( 'tagName' ) === 'DIV' && this.attr( 'id' ) !== undefined && this.attr( 'id' ) == 'timesheet' )
					return this.attr( 'day' );

				return 'undefined';
			},

			// used on a <span.js-octicon-stopwatch>
			isRunning: function()
			{
				if ( this.prop( 'tagName' ) === 'SPAN' && $jQ(this).hasClass( 'js-octicon-stopwatch' ) )
				{
					if ( $jQ(this).hasClass( 'octicon-playback-play' ) )
						return false;	// is not running

					else if ( $jQ(this).hasClass( 'octicon-playback-pause' ) )
						return true;	// is running
				}
			}
		});

		// updates total hours spent for the day
		updateTisheetTimeSpentToday();

		// 
		addAutocompleteOnTisheetDescription();

		// 
		initWebsocketConnection();

		$jQ( '#timesheet tbody' ).sortable(
		{ 
			cursor: 'move',
			items: $jQ( '#timesheet div.js-tisheet' ).not( '.js-tisheet-clonable, .timesheet-footer' ),
			helper: function( e, element ) 
			{
				// Return a helper with preserved width of cells
				element.children().each( function() 
				{
					$jQ(this).width( $jQ(this).width() );
				});

				return element;
			},
			update: function( e, ui )
			{
				var tids = [];
				var items = $jQ(this).find( 'div.js-tisheet' ).not( '.js-tisheet-clonable, .timesheet-footer' );

				// collect all ids in the correct order and btw. reset position value
				var position = 1;
				items.each( function()
				{
					tids.push( $jQ(this).id() );
				});

				var url = '{{ url( "tisheets" ) }}/' + $jQ( '#timesheet' ).today();

				// update backend
				$jQ.ajax({
					url: url,
					type: 'put',
					data: { tids: tids }
				});
			}
		});

		// load columns
		$jQ.ajax({
			url: '{{ url( "tisheets" ) }}/'+ $jQ( '#timesheet' ).today() +'/columns',
			success: function( data )
			{
				$jQ( '#columns' ).html( data );

				makeColumnsSortable();
			}
		});
	});

	$jQ( document ).on( 'click', '.datepicker', function( event )
	{
		picker.toggleClass( 'cc-element-invisible' );
		picker.css( 'left', event.pageX - ( picker.width() / 2 ) );
	});

	$jQ( document ).on( 'focusin', 'input.tisheet-description', function()
	{
		oldDescription = $jQ( this ).val();
        
        hideTisheetErrorMessages();
	});

	$jQ( document ).on( 'focusin', 'textarea.tisheet-note', function()
	{
		oldNote = $jQ( this ).val();
        
        hideTisheetErrorMessages();
	});

	$jQ( document ).on( 'focusin', 'div.timesheet-story textarea', function()
	{
		oldTopic = $jQ( this ).val();
        
        hideTisheetErrorMessages();
	});

	//
	$jQ( document ).on( 'focusout', 'input.tisheet-description', function()
	{
		var value = $jQ(this).val();
		var tisheet = $jQ(this).closest( 'div.js-tisheet' );

		if ( oldDescription == value )
			return;	// ignore if nothing changed

		if ( value.trim() == '' )
			return;	// ignore empty values

		var hasId = tisheet.id() != 'undefined' ? true : false;

		var url = '{{ url( "tisheets" ) }}/' + $jQ( '#timesheet' ).today() + ( hasId ? '/tisheet/'+ tisheet.id() : '' );
		var type = hasId ? 'put' : 'post';

		// activate loading icon
		tisheet.find( 'span.js-ajax-loader' ).toggleClass( 'cc-element-hidden' );

		$jQ.ajax({
			url: url,
			type: type,
			dataType: 'json',
			data: {
				vl: value
			},
			success: function( obj )
			{
				if ( obj.status !== 'ok' )
					// TODO ZL show appropriate error message
					alert( 'error' );

				if ( obj.action === 'add' )
					// update id that was given by the backend and context
					tisheet.attr( 'id', obj.id );
				
				if ( obj.context )
					// update context attribute that we need for the highlighting
					tisheet.attr( 'ctx', obj.context );
				else
					tisheet.removeAttr( 'ctx' );

				if ( obj.time )
				{
					// update time, when the tisheet has begun
					tisheet.find( 'span.js-tisheet-time-start' ).text( obj.time );

					// update quarters of time spent, if there are not active quarters yet
					var activeQuarters = tisheet.find( 'span.time-spent-quarter-active' ).length;
					// and if 'today' is the real today
					var todayForReal = '{{ $todayForReal }}';

					if ( activeQuarters == 0 && todayForReal )
					{
						var minutesToNow = ( Date.now() - Date.parse( '{{ $today }} ' + obj.time ) ) / 60000;
						var quartersToNow = Math.floor( minutesToNow / 15 );
						
						// update only, if given time lays after Date.now()
						if ( minutesToNow > 0 && quartersToNow > 0 )
							tisheet.find( 'span.js-time-spent-quarter:eq('+ ( quartersToNow - 1 ) + ')' ).click();
					}
				}
				
				descriptionFocusoutSuccessCallbackHandler( tisheet, obj );

				showAndFadeOutOkIcon( tisheet );

				invokeDescriptionChangeListener( tisheet );
			}
		});
	});

	//
	$jQ( document ).on( 'focusout', 'textarea.tisheet-note', function()
	{
		var value = $jQ(this).val();
		var tisheet = $jQ(this).closest( 'div.js-tisheet' );

		if ( oldNote == value )
			return;	// ignore if nothing changed

		var url = '{{ url( "tisheets" ) }}/' + $jQ( '#timesheet' ).today() + '/tisheet/'+ tisheet.id() +'/note';
		var type = value.trim() == '' ? 'delete' : 'put';

		// show loading icon
		tisheet.find( 'span.js-ajax-loader' ).toggleClass( 'cc-element-hidden' );

		$jQ.ajax({
			url: url,
			type: type,
			data: {
				nt: value.trim(),
				na: true
			},
			success: function( data )
			{
				if ( data == 'false' )
					alert( 'error' );

				showAndFadeOutOkIcon( tisheet );

				// show/hide octicon-info
				if ( value == '' || ( value != '' && oldNote == '' ) )
					tisheet.find( 'span.octicon-info' ).toggleClass( 'cc-element-visible cc-element-invisible' );

				// we do not need to invokeDescriptionChangeListener here, since the note 
				// does not change any tisheet properties

				app.BrainSocket.message( 'tisheet.note.update.event',
			    {   
			        'value': value.trim(),
			        'tid': tisheet.id()
			    });
			}
		});
	});

	//
	$jQ( document ).on( 'focusout', 'div.timesheet-story textarea', function()
	{
		var value = $jQ(this).val();

		if ( oldTopic == value )
			return; // ignore if nothing changed

		var url = '{{ url( "tisheets" ) }}/' + $jQ( '#timesheet' ).today();
		var type = value.trim() == '' ? 'delete' : 'put';

		$jQ.ajax({
			url: url,
			type: type,
			data: {
				gl: value.trim()
			}
		});
	});

	//
	var updateTisheetIsPlanned = function( item )
	{
		// update object
		var url = '{{ url( "tisheets" ) }}/' + $jQ( '#timesheet' ).today() + '/tisheet/'+ item.id();
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
	var updateTisheetTimeline = function()
	{
		var url = '{{ url( "tisheets" ) }}/' + $jQ( '#timesheet' ).today() + '/timeline';

		$jQ.ajax({
			url: url,
			success: function( data )
			{
				$jQ( '#timeline-today' ).html( data );
			}
		});
	}

	//
	var showAndFadeOutOkIcon = function( item )
	{
		if ( item == undefined )
			return;

		item.find( 'span.js-ajax-loader' ).toggleClass( 'cc-element-hidden' );

		var check = item.find( '.octicon-check' );
		check.show();
		check.fadeOut( 2000 );
	};

	// 
	var invokeDescriptionChangeListener = function( item )
	{
		// invoke all registered callbacks
		var callbacks = descriptionChangeListener.length;
		for ( var i=0; i<callbacks; i++ )
		{
			// get next element of array
			var obj = descriptionChangeListener.shift();

			if ( obj.startOnly != undefined )
				obj.callback()( item, obj.startOnly );
			else
				obj.callback()( item, false );
		}
	};

	//
	var updateTisheetSummary = function()
	{
		if ( $jQ( '#summary' ).is( ':not(:visible)' ) )
			return;

		$jQ( '#summary a.js-button-summary.js-button-active' ).click();
	}

	//
	var addAutocompleteOnTisheetDescription = function()
	{
		var url = '{{ url( "tisheets" ) }}/' + $jQ( '#timesheet' ).today() + '/autocomplete';

    	$jQ.ajax({
    		url: url,
    		type: 'get',
    		success: function( data )
    		{
    			autocompleteItems = eval( data );

				$jQ( '.tisheet-description' ).autocomplete(
				{
					source: autocompleteItems,
					minLength: 2,
					delay: 100
			    });
    		}
    	});

    	return false;
	}

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
	var getTisheetId = function( element )
	{
		return $jQ( element ).closest( 'div.js-tisheet' ).id();
	}

	//
	var getTisheet = function( element )
	{
		return $jQ( element ).closest( 'div.js-tisheet' );
	}

	//
	var getBaseUrl = function( value )
	{
		if ( value != undefined )
			return '{{ url( "'+ value +'" ) }}';
		else
			return '{{ url( "tisheets" ) }}/';
	}

	//
	var getSessionToken = function()
	{
		return '{{ Session::token() }}';
	}

@endif

</script>

</body>

</html>
