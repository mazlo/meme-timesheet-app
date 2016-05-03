<!doctype html>
<html lang='de'>
<head>
    <meta charset='UTF-8'>
    <title>Timesheet</title>

	<!-- browser icon -->
	<link rel="shortcut icon" href='{{ url( "favicon.ico" ) }}' />

	<link rel='stylesheet' type='text/css' href='{{ url( "jquery-ui.css" ) }}' />
	<link rel='stylesheet' type='text/css' href='{{ url( "octicons.css" ) }}' />
	<link rel='stylesheet' type='text/css' href='{{ url( "ionicons.css" ) }}' />
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

	<div id='header'>

		<div class='options element-float-left'>
			<ul class='list-inline'>
				<li><a href='{{ url( "terms-and-conditions" ) }}' class='option'>about tim.mazlo.de</a></li>
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
				// used on <tr> with id-attribute
				if ( this.prop( 'tagName' ) === 'TR' && this.attr( 'id' ) !== undefined )
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
		picker.toggleClass( 'element-invisible' );
		picker.css( 'left', event.pageX - ( picker.width() / 2 ) );
	});

	// add new line to table or focus next textfield of next line
	$jQ( document ).keydown( function( event )
	{
		var target = $jQ( event.target );
		var tisheet = target.closest( 'tr.js-tisheet' );

		if ( meetsKeydownExitCriteria( event, target ) )
		{
			return;
		}

		// focusout on escape key
		if ( escapeKeyCode( event ) )
		{
			if ( target.hasClass( 'tisheet-note' ) )
				target.val( oldNote );

			else if ( target.hasClass( 'tisheet-description' ) )
			{
				// remove whole line when textfield is empty, but ignore first element and if it has an id
				if ( target.val() == '' && tisheet.index() != 1 && tisheet.id() == 'undefined' )
					tisheet.remove();

				// remove whole line in case old description is empty
				else if ( oldDescription == '' && tisheet.index() != 1 )
				{
					// reset description here, otherwise focusout will send value to backend
					target.val( '' );
					tisheet.remove();
				}

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
			{
				target.val( 'TODO: describe' );
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
		var tisheet = $jQ(this).closest( 'tr.js-tisheet' );

		if ( oldDescription == value )
			return;	// ignore if nothing changed

		if ( value.trim() == '' )
			return;	// ignore empty values

		var hasId = tisheet.id() != 'undefined' ? true : false;

		var url = '{{ url( "tisheets" ) }}/' + $jQ( '#timesheet' ).today() + ( hasId ? '/tisheet/'+ tisheet.id() : '' );
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
		var tisheet = $jQ(this).closest( 'tr.js-tisheet' );

		if ( oldNote == value )
			return;	// ignore if nothing changed

		var url = '{{ url( "tisheets" ) }}/' + $jQ( '#timesheet' ).today() + '/tisheet/'+ tisheet.id() +'/note';
		var type = value.trim() == '' ? 'delete' : 'put';

		// show loading icon
		tisheet.find( 'span.js-ajax-loader' ).toggleClass( 'element-hidden' );

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
					tisheet.find( 'span.octicon-info' ).toggleClass( 'element-visible element-invisible' );

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

	/**
	*	this handler is currently not used, since the feature was removed
	*/
	$jQ( document ).on( 'change', '.js-tisheet-planned', function()
	{
		var item = $jQ(this).closest( 'tr.js-tisheet' );

		if ( item.id() == 'undefined' )
			descriptionChangeListener.push( { callback: updateTisheetIsPlanned } );
		else
			updateTisheetIsPlanned( item );
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
	var getTisheetId = function( element )
	{
		return $jQ( element ).closest( 'tr.js-tisheet' ).id();
	}

	//
	var getTisheet = function( element )
	{
		return $jQ( element ).closest( 'tr.js-tisheet' );
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
