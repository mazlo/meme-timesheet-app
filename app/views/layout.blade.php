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
    <link rel='stylesheet' type='text/css' href='{{ url( "datepicker.css" ) }}' />
	
	<script type='text/javascript' src='{{ url( "jquery-1.8.2.js" ) }}'></script>
	<script type='text/javascript' src='{{ url( "jquery-ui-1.9.2.custom.js" ) }}'></script>

	<script type='text/javascript' src='{{ url( "jquery.datepicker.min.js" ) }}'></script>

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
		// extend jquery functionality
		$jQ.fn.extend(
		{
			id: function()
			{
				if ( this.prop( 'tagName' ) === 'TR' && this.attr( 'id' ) !== undefined )
					return this.attr( 'id' );

				return 'undefined';
			}
		});

		// updates total hours spent for the day
		updateTisheetTimeSpentToday();

		// 
		addAutocompleteOnTisheetDescription();

		$jQ( '#timesheet tbody' ).sortable(
		{ 
			cursor: 'move',
			items: $jQ( '#timesheet tr.js-tisheet' ).not( '.js-tisheet-clonable, .timesheet-footer' ),
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
				var items = $jQ(this).find( 'tr.js-tisheet' ).not( '.js-tisheet-clonable, .timesheet-footer' );

				// collect all ids in the correct order and btw. reset position value
				var position = 1;
				items.each( function()
				{
					tids.push( $jQ(this).attr( 'id' ) );
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

		picker = $jQ( '#datepicker' ).datepicker(
		{
			selectedDate: new Date( '{{ $today }}' ),
		    onDateChanged: function ( pickedDate ) 
		    {
		    	var date = new Date(pickedDate);
		    	date = date.getFullYear() +'-'+ (date.getMonth()+1) +'-'+ date.getDate();
		    	if ( date == '{{ $today }}' )
		    		return;

				window.location = '{{ url( "tisheets" ) }}/'+ date; 
		    },
		});

	});

$jQ( document ).on( 'click', '.datepicker', function( event )
{
	picker.toggleClass( 'element-invisible' );
	picker.css( 'left', event.pageX - ( picker.width() / 2 ) );
});

	// add new line to table or focus next textfield of next line
	$jQ( document ).keydown( function( event )
	{
		if ( event.keyCode != 13 && event.keyCode != 27 )
			return;

		var target = $jQ( event.target );
		var tisheet = target.closest( 'tr.js-tisheet' );

		// focusout on escape key
		if ( event.keyCode == 27 )
		{
			if ( target.hasClass( 'tisheet-note' ) )
				target.val( oldNote );

			else if ( target.hasClass( 'tisheet-description' ) )
			{
				// remove whole line when textfield is empty, but ignore first element and if it has an id
				if ( target.val() == '' && tisheet.index() != 1 && tisheet.attr( 'id' ) == 'undefined' )
					tisheet.remove();

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
		var tr = $jQ( 'tr.js-tisheet-clonable' );

		// event fires in textfield
		if ( tisheet.hasClass( 'js-tisheet' ) )
		{
			// ignore when fired from empty textfield
			if ( target.val() == '' )
				return;

			// focus next textfield when fired NOT from the last textfield
			if ( tr.index() - tisheet.index() > 1 )
			{
				target.blur(); // first focusout, then focus in. otherwise request of change will fire
				
				var nextTisheet = tisheet.next().find( 'input.tisheet-description' );
				nextTisheet.focus();
				nextTisheet[0].setSelectionRange( nextTisheet.val().length, nextTisheet.val().length );

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

		cloneTisheet( tr, target );
	});

	$jQ( document ).on( 'focusin', 'input.tisheet-description', function()
	{
		oldDescription = $jQ( this ).val();
	});

	$jQ( document ).on( 'focusin', 'textarea.tisheet-note', function()
	{
		oldNote = $jQ( this ).val();
	});

	$jQ( document ).on( 'focusin', 'textarea.timesheet-topic', function()
	{
		oldTopic = $jQ( this ).val();
	});

	//
	$jQ( document ).on( 'focusout', 'input.tisheet-description', function()
	{
		var value = $jQ(this).val();
		var tisheet = $jQ(this).closest( 'tr.js-tisheet' );

		if ( oldDescription == value )
			return;	// ignore if nothing changed

		if ( value.trim() == '' )
			return;	// ignore empty values

		var hasId = tisheet.attr( 'id' ) != 'undefined' ? true : false;

		var url = '{{ url( "tisheets" ) }}/' + $jQ( '#timesheet' ).attr( 'day' ) + ( hasId ? '/tisheet/'+ tisheet.attr( 'id' ) : '' );
		var type = hasId ? 'put' : 'post';

		// activate loading icon
		tisheet.find( 'span.js-ajax-loader' ).toggleClass( 'element-hidden' );

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
				
				if ( obj.callback !== undefined && obj.callback !== '' )
				{
					var command = obj.callback.command;

					// starts the stopwatch
					if ( command === 'go' || command === 'run' )
						tisheet.find( '.js-octicon-stopwatch' ).trigger( 'click', { name: command, startOnly: true } );

					// updates quarters of time
					else if ( command === 'spent' || command === 'took' || command === 'planned' )
					{
						var param = obj.callback.param;

						if ( param.indexOf( 'h' ) > 0 )
						{
							param = param.split( 'h' )[0];
							if ( param <= 4 )
								tisheet.find( 'span.js-time-spent-quarter:eq('+ roundToQuarterOfHour( param*60 ) + ')' ).click();
							else
								tisheet.find( 'span.js-time-spent-quarter:eq(15)' ).click();
						}
						else if ( param.indexOf( 'min' ) > 0 )
						{
							param = param.split( 'min' )[0];
							if ( param <= 240 )
								tisheet.find( 'span.js-time-spent-quarter:eq('+ roundToQuarterOfHour( param ) + ')' ).click();
							else 
								tisheet.find( 'span.js-time-spent-quarter:eq(15)' ).click();

						}
					}

					tisheet.find( '.js-tisheet-description' ).val( obj.desc );
				}

				showAndFadeOutOkIcon( tisheet );

				invokeDescriptionChangeListener( tisheet );
			}
		});
	});

	//
	$jQ( document ).on( 'focusout', 'textarea.tisheet-note', function()
	{
		var value = $jQ(this).val();
		var item = $jQ(this).closest( 'tr.js-tisheet' );

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

				showAndFadeOutOkIcon( item );

				// show/hide octicon-info
				if ( value == '' || ( value != '' && oldNote == '' ) )
					item.find( 'span.octicon-info' ).toggleClass( 'element-visible element-invisible' );

				// we do not need to invokeDescriptionChangeListener here, since the note 
				// does not change any tisheet properties
			}
		});
	});

	//
	$jQ( document ).on( 'focusout', 'textarea.timesheet-topic', function()
	{
		var value = $jQ(this).val();

		if ( oldTopic == value )
			return; // ignore if nothing changed

		var url = '{{ url( "tisheets" ) }}/' + $jQ( '#timesheet' ).attr( 'day' );
		var type = value.trim() == '' ? 'delete' : 'put';

		$jQ.ajax({
			url: url,
			type: type,
			data: {
				gl: value.trim()
			}
		});
	});

	/**
	*	this handler is currently not used, since the feature was removed
	*/
	$jQ( document ).on( 'change', '.js-tisheet-planned', function()
	{
		var item = $jQ(this).closest( 'tr.js-tisheet' );

		if ( item.attr( 'id' ) == 'undefined' )
			descriptionChangeListener.push( { callback: updateTisheetIsPlanned } );
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

	//
	var updateTisheetTimeSpentQuarter = function( tisheet )
	{
		// update object
		var url = '{{ url( "tisheets" ) }}/' + $jQ( '#timesheet' ).attr( 'day' ) + '/tisheet/'+ tisheet.attr( 'id' );
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
	var showAndFadeOutOkIcon = function( item )
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
		var item = getTisheet( this );

		// do not delete items with no id
		if ( item.id() === undefined )
			return;

		var url = '{{ url( "tisheets" ) }}/' + $jQ( '#timesheet' ).attr( 'day' ) +'/tisheet/'+ item.id();

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
		var item = getTisheet( this );
		var note = item.find( '.js-tisheet-note' );

		note.toggleClass( 'element-hidden' );
		note.find( 'textarea' ).focus();
	});

	//
	$jQ( document ).on( 'click', '.js-tisheet-move', function()
	{
		var tisheet = getTisheet( this );

		var url = '{{ url( "tisheets" ) }}/' + $jQ( '#timesheet' ).attr( 'day' ) +'/tisheet/'+ tisheet.id();

		$jQ.ajax({
			url: url,
			type: 'put',
			data: { mv: 'tomorrow' },
			success: function( data )
			{
				// remove current element
				tisheet.remove();

				var tisheetToClone = $jQ( 'tr.js-tisheet-clonable' );

				if ( tisheetToClone.index() == 1 )
					// clone empty element
					cloneTisheet( tisheetToClone, undefined );
			}
		});

		return false;
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
		$jQ(this).find( '.octicon-trashcan, .octicon-info, .js-octicon-stopwatch, .octicon-arrow-right' ).not( 'element-visible' ).toggleClass( 'element-invisible' );
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

		var runningStopwatch = $jQ( '#timesheet' ).find( 'span.octicon-playback-pause' );
		if ( runningStopwatch.length > 0 )
		{
			var runningStopwatchId = getTisheetId( runningStopwatch );
			
			// but only if it's not the current stopwatch
			if ( runningStopwatchId != requestedStopwatchId )
				toggleStopwatchStatus( runningStopwatch.closest( 'tr.js-tisheet' ), false );
		}

		// change status of stopwatch now

		if ( requestedStopwatchId == 'undefined' )
			// register for post update description field
			descriptionChangeListener.push( { callback: toggleStopwatchStatus, startOnly: startOnly } );
		else
			// change status of pressed stopwatch now
			toggleStopwatchStatus( tisheet, startOnly );
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
		if ( minutesByTisheets[ tisheet.attr( 'id' ) ] > 7 )
			triggerQuarterTimeSpentClick( tisheet );

		minutesByTisheets[ tisheet.attr( 'id' ) ] = 0;

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

		if ( minutesByTisheets[ tisheet.attr( 'id' ) ] == undefined ) 
			minutesByTisheets[ tisheet.attr( 'id' ) ] = 1;

		// update tisheet start field only once
		var time = tisheet.find( 'span.js-tisheet-time-start' );
		if ( time.text() == '' )
			time.text( new Date().toTimeString().substring(0,5) );

		stopwatch.toggleClass( 'octicon-playback-play octicon-playback-pause element-visible' );
	}

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

			toggleStopwatchStatus( tisheet );

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
		return $jQ( element ).closest( 'tr.js-tisheet' ).attr( 'id' );
	}

	//
	var getTisheet = function( element )
	{
		return $jQ( element ).closest( 'tr.js-tisheet' );
	}

	var roundToQuarterOfHour = function( minutes )
	{
		var rounded = Math.ceil( minutes/15 );

		if ( rounded == 0 )
			return 0;
		else
			return rounded -1;
	}

	//
	var cloneTisheet = function( elementToClone, latestElement )
	{
		var clonedElement = elementToClone.clone();

		clonedElement.insertBefore( elementToClone );
		clonedElement.removeClass( 'js-tisheet-clonable element-hidden' );
		
		if ( latestElement !== undefined )
			// invoke manually to prevent asynchronous side effects
			latestElement.blur();

		clonedElement.find( 'input.tisheet-description' ).focus();

		// add autocomplete functionality
		clonedElement.find( 'input.tisheet-description' ).autocomplete(
		{
			source: autocompleteItems,
			minLength: 2,
			delay: 100
	    });
	}

@endif

</script>

</body>

</html>
