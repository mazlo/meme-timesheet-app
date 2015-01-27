<!doctype html>
<html lang='de'>
<head>
    <meta charset='UTF-8'>
    <title>Timesheet</title>

	<!-- browser icon -->
	<link rel="shortcut icon" href='{{ url( "favicon.ico" ) }}' />

	<link rel='stylesheet' type='text/css' href='{{ url( "jquery-ui.css" ) }}' }} />
	<link rel='stylesheet' type='text/css' href='{{ url( "octicons.css" ) }}' }} />
    <link rel='stylesheet' type='text/css' href='{{ url( "main.css" ) }}' />
	
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

		<div class='options element-float-left'>
			<ul class='list-inline'>
				<li><a href='{{ url( "terms-and-conditions" ) }}' class='option'>about yatimesheet.de</a></li>
			</ul>
		</div>

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

@if( Auth::check() ) {{-- add javascript functionality only when user is logged in --}}

	descriptionChangeListener = [];
	autocompleteItems = [];

	$jQ( function()
	{
		// updates total hours spent for the day
		updateTisheetTimeSpentToday();

		// 
		addAutocompleteOnTisheetDescription();

		$jQ( '#timesheet tbody' ).sortable(
		{ 
			cursor: 'move',
			items: $jQ( '#timesheet .item' ).not( '.js-item-clonable, .timesheet-footer' ),
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
				var items = $jQ(this).find( '.item' ).not( '.js-item-clonable, .timesheet-footer' );

				// collect all ids in the correct order and btw. reset position value
				var position = 1;
				items.each( function()
				{
					tids.push( $jQ(this).attr( 'id' ) );
					// update position value in column 1
					$jQ(this).find( 'span.js-tisheet-no' ).text( position++ +'.' );
				});

				var url = '{{ url( "tisheets" ) }}/' + $jQ( '#timesheet' ).attr( 'day' );

				// update backend
				$jQ.ajax({
					url: url,
					type: 'put',
					data: { tids: tids }
				});
			}
		});

	});

	// add new line to table or focus next textfield of next line
	$jQ( document ).keydown( function( event )
	{
		if ( event.keyCode != 13 && event.keyCode != 27 )
			return;

		var target = $jQ( event.target );
		var item = target.closest( '.item' );

		// focusout on escape key
		if ( event.keyCode == 27 )
		{
			if ( target.hasClass( 'tisheet-note' ) )
				target.val( oldNote );

			else if ( target.hasClass( 'tisheet-description' ) )
			{
				// remove whole line when textfield is empty, but ignore first element
				if ( target.val() == '' && item.index() != 1 )
					item.remove();

				// replace with old value and blur
				else
				{
					target.val( oldDescription );
					target.blur();
				}
			}

			return;
		}

		// ignore textareas from here
		if ( target.hasClass( 'tisheet-note' ) )
			return;

		// the line to clone
		var tr = $jQ( 'tr.js-item-clonable' );

		// event fires in textfield
		if ( item.hasClass( 'item' ) )
		{
			// ignore when fired from empty textfield
			if ( target.val() == '' )
				return;

			// focus next textfield when fired NOT from the last textfield
			if ( tr.index() - item.index() > 1 )
			{
				target.blur(); // first focusout, then focus in. otherwise request of change will fire
				item.next().find( 'input.tisheet-description' ).focus();

				return;
			}

			// focus out after hitting enter
			target.blur();
			return;
		} 

		// event fires in document

		// focus first textfield if it is empty
		else if ( tr.index() >= 2 ) 
		{
			var textfield = tr.prev().find( 'input.tisheet-description' );
			
			if ( textfield.val() == '' )
			{
				textfield.focus();
				return;
			}
		}

		var trClone = tr.clone();

		trClone.insertBefore( tr );
		trClone.find( 'span.js-tisheet-no' ).text( trClone.index()+ '.' );
		trClone.removeClass( 'js-item-clonable element-hidden' );
		
		// invoke manually to prevent asynchronous side effects
		target.blur();
		trClone.find( 'input.tisheet-description' ).focus();

		// add autocomplete functionality
		trClone.find( 'input.tisheet-description' ).autocomplete(
		{
			source: autocompleteItems,
			minLength: 2,
			delay: 100
	    });
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

		var hasId = item.attr( 'id' ) != 'undefined' ? true : false;

		var url = '{{ url( "tisheets" ) }}/' + $jQ( '#timesheet' ).attr( 'day' ) + ( hasId ? '/tisheet/'+ item.attr( 'id' ) : '' );
		var type = hasId ? 'put' : 'post';

		// activate loading icon
		item.find( 'span.js-ajax-loader' ).toggleClass( 'element-hidden' );

		$jQ.ajax({
			url: url,
			type: type,
			data: {
				vl: value
			},
			success: function( data )
			{
				if ( data == 'false' )
					alert( 'error' );

				if ( data > 0 )
					item.attr( 'id', data );

				notifyUserOfChange( item );

				invokeDescriptionChangeListener( item );
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

		var url = '{{ url( "tisheets" ) }}/' + $jQ( '#timesheet' ).attr( 'day' ) + '/tisheet/'+ item.attr( 'id' ) +'/note';
		var type = value.trim() == '' ? 'delete' : 'put';

		// show loading icon
		item.find( 'span.js-ajax-loader' ).toggleClass( 'element-hidden' );

		$jQ.ajax({
			url: url,
			type: type,
			data: {
				nt: value.trim()
			},
			success: function( data )
			{
				if ( data == 'false' )
					alert( 'error' );

				notifyUserOfChange( item );

				// show/hide octicon-info
				if ( value == '' || ( value != '' && oldNote == '' ) )
					item.find( 'span.octicon-info' ).toggleClass( 'element-visible element-invisible' );

				// we do not need to invokeDescriptionChangeListener here, since the note 
				// does not change any tisheet properties
			}
		});
	});

	// 
	$jQ( document ).on( 'change', '.js-tisheet-planned', function()
	{
		var item = $jQ(this).closest( '.item' );

		if ( item.attr( 'id' ) == 'undefined' )
			descriptionChangeListener.push( updateTisheetIsPlanned );
		else
			updateTisheetIsPlanned( item );
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
		$jQ(this).closest( '.item' ).find( 'span.js-tisheet-time-spent' ).text( count/4 + 'h');

		// register feature for post update

		var item = $jQ(this).closest( '.item' );
		
		if ( item.attr( 'id' ) == 'undefined' )
			// tisheet without an id -> update when tisheet was saved
			descriptionChangeListener.push( updateTisheetTimeSpentQuarter );
		else
			updateTisheetTimeSpentQuarter( item );

		// update total time spent for the day -> static
		updateTisheetTimeSpentToday();
	});

	//
	var updateTisheetTimeSpentQuarter = function( item )
	{
		// update object
		var url = '{{ url( "tisheets" ) }}/' + $jQ( '#timesheet' ).attr( 'day' ) + '/tisheet/'+ item.attr( 'id' );
		var count = item.find( '.time-spent-quarter-active' ).length;

		$jQ.ajax({
			url: url,
			type: 'put',
			data: {
				ts: count
			},
			success: function( data )
			{
				updateTisheetTimeline();
				updateTisheetSummary();
			}
		});
	};

	//
	var updateTisheetTimeSpentToday = function()
	{
		count = $jQ( '#timesheet' ).find( 'span.time-spent-quarter-active' ).length;
		$jQ( 'span.js-time-spent-today' ).text( count/4 + 'h');
	}

	//
	var updateTisheetTimeline = function()
	{
		var url = '{{ url( "tisheets" ) }}/' + $jQ( '#timesheet' ).attr( 'day' ) + '/timeline';

		$jQ.ajax({
			url: url,
			success: function( data )
			{
				$jQ( '#timeline-today' ).html( data );
			}
		});
	}

	//
	var notifyUserOfChange = function( item )
	{
		if ( item == undefined )
			return;

		item.find( 'span.js-ajax-loader' ).toggleClass( 'element-hidden' );

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
			descriptionChangeListener.shift()(item);
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
		var url = '{{ url( "tisheets" ) }}/' + $jQ( '#timesheet' ).attr( 'day' ) + '/autocomplete';

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
				updateTisheetTimeSpentToday();

				updateTisheetTimeline();
				updateTisheetSummary();
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
	$jQ( document ).on( 'click', '.js-button-summary', function()
	{
		$jQ( '#summaryWrapper' ).show();
		$jQ( '#summaryWrapper .js-ajax-loader' ).toggleClass( 'element-hidden' );

		var url = $jQ(this).attr( 'href' );

		$jQ.ajax({
			url: url,
			type: 'get',
			success: function( data )
			{
				$jQ( '#summaryWrapper .js-ajax-loader' ).toggleClass( 'element-hidden' );
				$jQ( '#summary' ).html( data );
			}
		});

		return false;
	});

	//
	$jQ( document ).on( 'click', '.js-button-summary-by-context', function()
	{
		var url = $jQ(this).attr( 'href' );
		var time = $jQ(this).attr( 'ts' );

		$jQ.ajax({
			url: url,
			type: 'get',
			data: {
				tts: time
			},
			success: function( data )
			{
				$jQ( '#summary-by-context-details' ).html( data );
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
	$jQ( document ).on( 'hover', '.js-tisheet-options', function() 
	{
		$jQ(this).find( '.octicon-trashcan, .octicon-info, .js-octicon-stopwatch' ).not( 'element-visible' ).toggleClass( 'element-invisible' );
	});

	var interval;
	var minutesByTisheets = [];

	//
	$jQ( document ).on( 'click', '.js-octicon-stopwatch', function()
	{
		var stopwatch = $jQ(this);
		var tisheet = stopwatch.closest( 'tr.item' );

		var currentStopwatchId = getTisheetId( stopwatch );

		// change status of running stopwatch

		var runningStopwatch = $jQ( '#timesheet' ).find( 'span.octicon-playback-pause' );
		if ( runningStopwatch.length > 0 )
		{
			var activeStopwatchId = getTisheetId( runningStopwatch );
			
			// but only if it's not the current stopwatch
			if ( activeStopwatchId != currentStopwatchId )
				stopwatchToggleStatus( runningStopwatch.closest( 'tr.item' ) );
		}

		// change status of stopwatch now

		if ( currentStopwatchId == 'undefined' )
			// register for post update description field
			descriptionChangeListener.push( stopwatchToggleStatus );
		else
			// change status of pressed stopwatch now
			stopwatchToggleStatus( tisheet );
	});

	// starts or stops the stopwatch for the given tisheet
	var stopwatchToggleStatus = function ( tisheet )
	{
		var stopwatch = tisheet.find( 'span.js-octicon-stopwatch' );
		var tisheet = stopwatch.closest( 'tr.item' );

		if ( stopwatch.hasClass( 'octicon-playback-pause' ) )
		{
			// completes the quarter if it's done more than the half
			if ( minutesByTisheets[ tisheet.attr( 'id' ) ] > 7 )
				triggerQuarterTimeSpentClick( tisheet );

			minutesByTisheets[ tisheet.attr( 'id' ) ] = 0;

			// reset stopwatch
			clearInterval( interval );
		}
		else 
		{
			// start stopwatch with handler
			interval = setInterval( function()
			{
				checkTriggerQuarterTimeSpent( tisheet );
			}, 1000*60 );

			if ( minutesByTisheets[ tisheet.attr( 'id' ) ] == undefined ) 
				minutesByTisheets[ tisheet.attr( 'id' ) ] = 1;
		}

		stopwatch.toggleClass( 'octicon-playback-play octicon-playback-pause element-visible' );
	};

	// check whether a quarter of an hour has passed
	var checkTriggerQuarterTimeSpent = function( tisheet )
	{	
		var minutesCounter = minutesByTisheets[ tisheet.attr( 'id' ) ] + 1;

		if ( minutesCounter <= 15 )
		{
			minutesByTisheets[ tisheet.attr( 'id' ) ] = minutesCounter;
			return;
		}

		var nextQuarter = triggerQuarterTimeSpentClick( tisheet );

		// if the end was reached reset the interval and stopwatch icon
		if ( nextQuarter == undefined )
		{
			clearInterval( interval );

			stopwatchToggleStatus( tisheet );

			// TODO ZL write email or something

			return;
		}

		minutesByTisheets[ tisheet.attr( 'id' ) ] = 0;
	};
	
	// updates the next time spent quarter
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

	// 
	var getTisheetId = function( element )
	{
		return $jQ( element ).closest( 'tr.item' ).attr( 'id' );
	}

@endif

</script>

</body>

</html>
