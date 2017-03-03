@extends( 'layout' )

<?php
	$todayAsTime = strtotime( $today );
	$todayForReal = date( 'Y-m-d', time() );
	$todayForRealAsTime = strtotime( $todayForReal );
?>

@section( 'header' )

	<div class='options options-right-oriented element-float-right'>
		<ul class='list-inline'>
			<li>Hello {{ Auth::user()->username }}</li>
			<li><a href='{{ url( "logout" ) }}' class='option'>logout</a></li>
		</ul>
	</div>

@stop

@section( 'content' )

	<?php $sickToday = isset( $timesheet ) && $timesheet->sick ?>

	<div class='w3-row'>
		<h2 class='w3-col l10'>ya timesheet for @if( $today == $todayForReal ) today - @endif {{ date( 'l, dS M.', $todayAsTime ) }}</h2>
		<div class='w3-col l2 w3-right-align ts-left-align-middle ts-margin-top-23'>
			<a href='{{ url( "tisheets/$yesterday" ) }}'><span class='octicon octicon-arrow-left' title='{{ $yesterday }}'></span></a>
			<a href='{{ url( "tisheets/today" ) }}'><span class='octicon-text js-tisheet-today'>today</span></a>
			<a href='{{ url( "tisheets/$tomorrow" ) }}'><span class='octicon octicon-arrow-right' title='{{ $tomorrow }}'></span></a>			
		</div>
	</div>

	@if ( date( 'l', $todayAsTime ) == 'Sunday' )
		<div>Jeez, it's Sunday, why are you working at all?</div>
	@endif

	<div id='timesheet' class='timesheet element-collectable' day='{{ $today }}' @if( $sickToday )style='display: none'@endif>
			
		<div class='w3-row timesheet-header'>
			<h4 class='w3-col l7'>Task Description (planned?)</h4>
			<div class='w3-col l5 w3-row w3-hide-medium w3-hide-small'>
				<h4 class='w3-col l8'>Time Spent / Estimated Time</h4>
				<h4 class='w3-col l4'>Begin / Total Spent</h4>
			</div>
		</div>

		@foreach( $tisheets as $key => $tisheet )
		<div class='w3-row tisheet js-tisheet' id='{{ $tisheet->id}}' @if( $tisheet->context ) ctx='{{ substr( $tisheet->context->prefLabel, 1 ) }}' @endif>
			<div class='w3-col l7'>
                <span class='tisheet-error js-tisheet-error cc-element-invisible'></span>
                
				<?php $placeholder = $todayAsTime < $todayForRealAsTime ? 'I managed to ...' : 'I am about to ...'; ?>
				{{ Form::text( 'description', $tisheet->description, array( 'placeholder' => $placeholder, 'class' => 'cc-keep-clear-content-little textfield tisheet-description js-tisheet-description' ) ) }}
				
				<span class='octicon octicon-info @if( $tisheet->note && $tisheet->note->content != '' ) cc-element-visible @else cc-element-toggable @endif'></span>
    			<span class='octicon octicon-playback-play js-octicon-stopwatch cc-element-toggable'></span>
    			<span class='octicon octicon-list-unordered cc-element-toggable'></span>
				<span class='octicon octicon-trashcan octicon-no-padding-left cc-element-toggable'></span>
				
				<div class='js-tisheet-note @if( !$tisheet->note || !$tisheet->note->visible ) cc-element-hidden @endif' style='margin-top: 8px'>
					<textarea class='cc-keep-clear-content-little tisheet-note'>@if ( $tisheet->note ){{ $tisheet->note->content }}@endif</textarea>
				</div>
			</div>
			
			<div class='w3-col l5 w3-row'>
				<div class='w3-col l8 m5'>

			{{-- render actual time spent --}}
			@for( $i=0; $i<$tisheet->time_spent; $i++ )
				@if( $i != 0 && $i % 4 == 0 )
					<span class='time-spent-blank'>&nbsp;</span>
				@endif

				<span class='js-time-spent-quarter time-spent-quarter time-spent-quarter-active'>&nbsp;</span>
			@endfor

			{{-- print remaining time spent --}}
			@for( $i=$tisheet->time_spent; $i<16; $i++ )
				@if( $i != 0 && $i % 4 == 0 )
					<span class='time-spent-blank'>&nbsp;</span>
				@endif

				<span class='js-time-spent-quarter time-spent-quarter'>&nbsp;</span>
			@endfor
				</div>

				<div class='w3-col l4 m7 w3-row'>
					<span class='w3-col l4 m2 s2 cc-keep-clear-little js-tisheet-time-start'>{{ $tisheet->time_start }}</span>
					<span class='w3-col l4 m2 s2 cc-keep-clear-little js-tisheet-time-spent'>{{ $tisheet->time_spent*0.25 }}h</span>

					<span class='w3-display-topright cc-margin-top-little js-ajax-loader cc-element-hidden'><img src='{{ url( "loading.gif" ) }}' /></span>
				</div>
			</div>
		</div>
		@endforeach

		{{-- insert an empty cloneable tr that is cloned when needed --}}
		<div class='w3-row tisheet js-tisheet js-tisheet-clonable cc-element-hidden' id='undefined'>
			<div class='w3-col l7'>
				<?php $placeholder = $todayAsTime < $todayForRealAsTime ? 'I managed to ...' : 'I am about to ...'; ?>
				{{ Form::text( 'description', '', array( 'placeholder' => $placeholder, 'class' => 'cc-keep-clear-content-little textfield tisheet-description js-tisheet-description' ) ) }}
				
				<span class='octicon octicon-info cc-element-toggable'></span>
    			<span class='octicon octicon-playback-play js-octicon-stopwatch cc-element-toggable'></span>
    			<span class='octicon octicon-list-unordered cc-element-toggable'></span>
				<span class='octicon octicon-trashcan octicon-no-padding-left cc-element-toggable'></span>

				<div class='js-tisheet-note cc-element-hidden' style='margin-top: 8px'>
					<textarea class='cc-keep-clear-content tisheet-note'></textarea>
				</div>
			</div>

			<div class='w3-col l5 w3-row'>
				<div class='w3-col l8 m8'>
					<span class='js-time-spent-quarter time-spent-quarter'></span>
					<span class='js-time-spent-quarter time-spent-quarter'></span>
					<span class='js-time-spent-quarter time-spent-quarter'></span>
					<span class='js-time-spent-quarter time-spent-quarter'></span>
					<span class='time-spent-blank'></span>
					<span class='js-time-spent-quarter time-spent-quarter'></span>
					<span class='js-time-spent-quarter time-spent-quarter'></span>
					<span class='js-time-spent-quarter time-spent-quarter'></span>
					<span class='js-time-spent-quarter time-spent-quarter'></span>
					<span class='time-spent-blank'></span>
					<span class='js-time-spent-quarter time-spent-quarter'></span>
					<span class='js-time-spent-quarter time-spent-quarter'></span>
					<span class='js-time-spent-quarter time-spent-quarter'></span>
					<span class='js-time-spent-quarter time-spent-quarter'></span>
					<span class='time-spent-blank'></span>
					<span class='js-time-spent-quarter time-spent-quarter'></span>
					<span class='js-time-spent-quarter time-spent-quarter'></span>
					<span class='js-time-spent-quarter time-spent-quarter'></span>
					<span class='js-time-spent-quarter time-spent-quarter'></span>
				</div>

				<div class='w3-col l4 m4'>
					<span class='w3-col l4 m2 s2 cc-keep-clear-little js-tisheet-time-start'></span>
					<span class='w3-col l4 m2 s2 cc-keep-clear-little js-tisheet-time-spent'></span>

					<span class='w3-display-topright cc-margin-top-little js-ajax-loader cc-element-hidden'><img src='{{ url( "loading.gif" ) }}' /></span>
				</div>
			</div>
		</div>

	</div> {{-- div#timesheet --}}

	<div id='timeline-today' class='timeline-today element-collectable' @if( $sickToday )style='display: none'@endif>
		@include( 'ajax.timeline' )
	</div>

	<ul class='list-inline js-button-group element-collectable' style='margin-top: 23px; margin-left: 11px; @if( $sickToday )display: none;@endif'>
		<li><a href='{{ url( "tisheets/$today/summary/week/groupby/contexts" ) }}' class='js-button js-button-summary'>show summary by contexts</a></li>
		<li><a href='{{ url( "tisheets/$today/summary/week/groupby/days/contexts" ) }}' class='js-button js-button-summary'>show summary by days</a></li>
	</ul>

	<div id='summaryWrapper' class='cc-element-hidden element-collectable' @if( $sickToday )style='display: none'@endif>
		<h3 class='w3-show-inline-block'>Summary</h3><span class='cc-keep-clear js-ajax-loader cc-element-hidden'><img src='{{ url( "loading.gif" ) }}' /></span>

		<div id='summary'>
			{{-- ajax content here --}}
		</div>
	</div>

	<div id='story' class='timesheet-story' @if ( !Auth::user()->showStory && !$sickToday ) style='display: none' @endif>
		<h3>What happened?</h3>
		<textarea class='js-timesheet-story' placeholder='You like to give a feedback on this day?'>@if( isset( $timesheet ) && $timesheet->story ){{ $timesheet->story }}@endif</textarea>
	</div>

	<div id='columns' class='columns js-columns element-collectable' style='margin-top: 32px; @if( !Auth::user()->showColumns && !$sickToday)display: none @endif;'>
		<h3 class='w3-show-inline-block'>Columns</h3><span class='cc-keep-clear js-ajax-loader'><img src='{{ url( "loading.gif" ) }}' /></span>
		{{-- ajax content here --}}
	</div>

	<div id='summary-same-as' class='element-collectable'>
		{{-- ajax content here --}}
	</div>


<script type='text/javascript'>

{{-- if there are no tisheets, print an initial empty one --}}
@if ( count( $tisheets ) == 0 )
	$jQ( function()
	{
		// find the cloneable div and clone it
		var div_empty = $jQ( 'div.js-tisheet-clonable' );
		var div_clone = div_empty.clone();

		// insert before the cloneable div and show
		div_clone.insertBefore( div_empty );
		div_clone.removeClass( 'js-tisheet-clonable cc-element-hidden' );
	});
@endif

    $jQ( function()
    {
        // adjust height of all visible textareas on load
        $jQ( '#timesheet textarea:visible' ).each( function()
        {
            adjustHeightOfTextarea( this );
        })
    });

</script>


@stop