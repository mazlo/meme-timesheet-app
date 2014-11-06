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

	invokeAfterTimesheetAjaxSuccess = [];

	$jQ( function()
	{
		updateTisheetTotalTimeSpent();

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
					$jQ(this).find( '.js-tisheet-no' ).text( position++ +'.' );
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
		if ( event.keyCode != 13 )
			return;

		var target = $jQ( event.target );

		// ignore textareas
		if ( target.hasClass( 'tisheet-note' ) )
			return;

		var item = target.closest( '.item' );

		// the line to clone
		var tr = $jQ( 'tr.js-item-clonable' );

		if ( item.hasClass( 'item' ) )
		{
			// event was fired in textarea
			
			if ( target.attr( 'value' ) == '' )
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

		// active loading icon
		item.find( 'span.js-ajax-loader' ).toggleClass( 'element-hidden' );

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
				if ( data == 'false' )
					alert( 'error' );

				if ( data > 0 )
					item.attr( 'id', data );

				notifyUserOfChange( item );

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

		var url = '{{ url( "tisheets" ) }}/' + $jQ( '#timesheet' ).attr( 'day' ) + '/tisheet/'+ item.attr( 'id' ) +'/note';
		var type = value.trim() == '' ? 'delete' : 'put';

		// active loading icon
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

				// we do not need to firePostUpdateActions here, since the note 
				// does not change any tisheet properties
			}
		});
	});

	// 
	$jQ( document ).on( 'change', '.js-tisheet-planned', function()
	{
		var item = $jQ(this).closest( '.item' );

		if ( item.attr( 'id' ) == 'undefined' )
			invokeAfterTimesheetAjaxSuccess.push( updateTisheetIsPlanned );
		else
			updateTisheetIsPlanned( item );
	});

	$jQ( document ).on( 'click', '.js-tisheet-time', function()
	{
		// reset all coming quarters
		$jQ(this).nextAll( '.js-tisheet-time' ).removeClass( 'time-spent-quarter-active' );

		// update current
		$jQ(this).addClass( 'time-spent-quarter-active' );
		// update all previous quarters
		$jQ(this).prevAll( '.js-tisheet-time' ).addClass( 'time-spent-quarter-active' );

		// update ui
		var count = $jQ(this).parent().find( '.time-spent-quarter-active' ).length;
		$jQ(this).closest( '.item' ).find( '.tisheet-total-time-spent' ).text( count/4 + 'h');

		var item = $jQ(this).closest( '.item' );
		
		if ( item.attr( 'id' ) == 'undefined' )
			// tisheet without an id -> update when tisheet was saved
			invokeAfterTimesheetAjaxSuccess.push( updateTisheetTimeSpent );
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
	var firePostUpdateActions = function( item )
	{
		// invoke all registered callbacks
		for ( var i=0; i<invokeAfterTimesheetAjaxSuccess.length; i++ )
			invokeAfterTimesheetAjaxSuccess.pop()(item);

		// 
		if ( $jQ( '#summary' ).is( ':not(:visible)' ) )
			return;

		$jQ( '.js-button-summary.js-button-active' ).click();
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
		$jQ(this).find( '.octicon-trashcan, .octicon-info' ).toggleClass( 'element-invisible' );
	});

	var interval;
	var minutesByTisheets = [];

	//
	$jQ( document ).on( 'click', '.js-octicon-stopwatch', function()
	{
		var stopwatch = $jQ(this);

		// change status of running stopwatch

		var runningStopwatch = $jQ( '#timesheet' ).find( '.octicon-playback-pause' );
		if ( runningStopwatch.length > 0 )
		{
			var activeStopwatchId = getTisheetId( runningStopwatch );
			var currentStopwachId = getTisheetId( stopwatch );
			
			// only if it's not the current stopwatch
			if ( activeStopwatchId != currentStopwachId )
				stopwatchToggleStatus( runningStopwatch );
		}

		// change status of pressed stopwatch

		stopwatchToggleStatus( stopwatch );
	});

	// starts or stops the given stopwatch
	var stopwatchToggleStatus = function ( stopwatch )
	{
		if ( stopwatch.hasClass( 'octicon-playback-pause' ) )
		{
			// reset stopwatch
			clearInterval( interval );
		}
		else 
		{
			var tisheet = stopwatch.closest( 'tr.item' );

			// start stopwatch with handler
			interval = setInterval( function()
			{
				updateTime( tisheet );
			}, 1000*60 );

			if ( minutesByTisheets[ tisheet.attr( 'id' ) ] == undefined ) 
				minutesByTisheets[ tisheet.attr( 'id' ) ] = 0;
		}

		stopwatch.toggleClass( 'octicon-playback-play' );
		stopwatch.toggleClass( 'octicon-playback-pause' );
	};

	// check whether a quarter of an hour has passed
	var updateTime = function( tisheet )
	{	
		var minutesCounter = minutesByTisheets[ tisheet.attr( 'id' ) ] + 1;

		if ( minutesCounter < 15 )
		{
			minutesByTisheets[ tisheet.attr( 'id' ) ] = minutesCounter;
			return;
		}

		var nextQuarter = updateQuarterTimeSpent( tisheet );

		// if the end was reached reset the interval and stopwatch icon
		if ( nextQuarter == undefined )
		{
			clearInterval( interval );

			stopwatchToggleStatus( tisheet.find( '.js-octicon-stopwatch' ) );

			// TODO ZL write email or something

			return;
		}

		minutesByTisheets[ tisheet.attr( 'id' ) ] = 0;
	};
	
	// updates the next time spent quarter
	var updateQuarterTimeSpent = function( tisheet )
	{
		// find the next not active quarter
		var nextQuarter = tisheet.find( '.js-tisheet-time.time-spent-quarter-active:last' ).nextAll( '.js-tisheet-time:first' );

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

</script>

</body>

</html>
