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

	<div style='position: relative'>
		<div class='title'>
			<a href='{{ url( "tisheets/$tomorrow" ) }}'><span class='octicon octicon-arrow-right element-float-right' title='{{ $tomorrow }}'></span></a>
			<a href='{{ url( "tisheets/today" ) }}'><span class='octicon-text js-tisheet-today element-float-right'>today</span></a>
			<a href='{{ url( "tisheets/$yesterday" ) }}'><span class='octicon octicon-arrow-left element-float-right' title='{{ $yesterday }}'></span></a>
			<span class='octicon octicon-plus datepicker element-float-right'></span>
			<span class='element-invisible element-float-right' id='datepicker'></span>
		</div>

		<div class='timesheet-options' style='position: absolute; left: -75px; width: 32px; text-align: center'>
			<span class='octicon octicon-book octicon-light @if( Auth::user()->showStory )octicon-active@endif'></span>
			<span class='octicon octicon-server octicon-light @if( Auth::user()->showColumns )octicon-active@endif'></span>
			<span class='octicon-splitter'>&nbsp;</span>	<?php $sickToday = isset( $timesheet ) && $timesheet->sick ?>
			<span class='ionicons ion-medkit @if( $sickToday )octicon-active@endif'></span>
		</div>

		<h2>ya timesheet for @if( $today == $todayForReal ) today - @endif {{ date( 'l, dS M.', $todayAsTime ) }}</h2>
	</div>

	@if ( date( 'l', $todayAsTime ) == 'Sunday' )
		<div>Jeez, it's Sunday, why are you working at all?</div>
	@endif

	<div id='timesheet' class='timesheet element-collectable' day='{{ $today }}' @if( $sickToday )style='display: none'@endif>
		<table cellpadding='0' cellspacing='0'>
			
			<colgroup>
				<col width='4%'>
				<col width='52%'>
				<col width='24%'>
				<col width='6%'>
				<col width='4%'>
				<col width='5%'>
			</colgroup>
			
			<tbody>
			<tr class='timesheet-header'>
				<th></th>
				<th>Task Description (planned?)</th>
				<th>Time Spent / Estimated Time</th>
				<th>Begin</th>
				<th>Total</th>
				<th></th>
			</tr>

		@foreach( $tisheets as $key => $tisheet )
			<tr class='tisheet js-tisheet' id='{{ $tisheet->id}}' @if( $tisheet->context ) ctx='{{ substr( $tisheet->context->prefLabel, 1 ) }}' @endif>
				<td>
                    <span class='tisheet-error js-tisheet-error element-invisible'></span>
                    <span class='octicon octicon-list-unordered element-toggable'></span>
					<span class='octicon octicon-info @if( $tisheet->note && $tisheet->note->content != '' ) element-visible @else element-toggable @endif'></span>
				</td>
				
				<td>
					<?php $placeholder = $todayAsTime < $todayForRealAsTime ? 'I managed to ...' : 'I am about to ...'; ?>
					{{ Form::text( 'description', $tisheet->description, array( 'placeholder' => $placeholder, 'class' => 'textfield tisheet-description js-tisheet-description' ) ) }}
					<span class='octicon octicon-trashcan octicon-no-padding-left element-toggable'></span>
					<span class='octicon octicon-playback-play js-octicon-stopwatch element-toggable'></span>

					<div class='js-tisheet-note @if( !$tisheet->note || !$tisheet->note->visible ) element-hidden @endif' style='margin-top: 8px'>
						<textarea class='tisheet-note'>@if ( $tisheet->note ){{ $tisheet->note->content }}@endif</textarea>
					</div>
				</td>
				
				<td>

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
				</td>

				<td><span class='tisheet-time-start js-tisheet-time-start'>{{ $tisheet->time_start }}</span></td>

				<td><span class='tisheet-time-spent js-tisheet-time-spent'>{{ $tisheet->time_spent*0.25 }}h</span></td>
				<td>
					<span class='octicon octicon-check element-hidden'></span>
					<span class='js-ajax-loader ajax-loader element-hidden'><img src='{{ url( "loading.gif" ) }}' /></span>
					<span class='octicon octicon-arrow-right js-tisheet-move element-toggable'></span>
				</td>
			</tr>
		@endforeach

			{{-- insert an empty cloneable tr that is cloned when needed --}}
			<tr class='tisheet js-tisheet js-tisheet-clonable element-hidden' id='undefined'>
				<td>
					<span class='octicon octicon-list-unordered element-toggable'></span>
					<span class='octicon octicon-info element-toggable'></span>
				</td>
				<td>
					<?php $placeholder = $todayAsTime < $todayForRealAsTime ? 'I managed to ...' : 'I am about to ...'; ?>
					{{ Form::text( 'description', '', array( 'placeholder' => $placeholder, 'class' => 'textfield tisheet-description js-tisheet-description' ) ) }}
					<span class='octicon octicon-trashcan octicon-no-padding-left element-toggable'></span>
					<span class='octicon octicon-playback-play js-octicon-stopwatch element-toggable'></span>

					<div class='js-tisheet-note element-hidden' style='margin-top: 8px'>
						<textarea class='tisheet-note'></textarea>
					</div>
				</td>
				<td>
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
				</td>
				<td><span class='tisheet-time-start js-tisheet-time-start'></span></td>
				<td><span class='tisheet-time-spent js-tisheet-time-spent'></span></td>
				<td>
					<span class='octicon octicon-check element-hidden'></span>
					<span class='js-ajax-loader ajax-loader element-hidden'><img src='{{ url( "loading.gif" ) }}' /></span>
					<span class='octicon octicon-arrow-right js-tisheet-move element-toggable'></span>
				</td>
			</tr>

			<tr class='timesheet-footer'>
				<td colspan='4'>&nbsp;</td>
				<td><span class='time-spent-today js-time-spent-today'></span></td>
			</tr>

			</tbody>
		</table>

	</div>

	<div id='timeline-today' class='timeline-today element-collectable' @if( $sickToday )style='display: none'@endif>
		@include( 'ajax.timeline' )
	</div>

	<ul class='list-inline js-button-group element-collectable' style='margin-left: 11px; @if( $sickToday )display: none;@endif'>
		<li><a href='{{ url( "tisheets/$today/summary/week/groupby/contexts" ) }}' class='js-button js-button-summary'>show summary by contexts</a></li>
		<li><a href='{{ url( "tisheets/$today/summary/week/groupby/days/contexts" ) }}' class='js-button js-button-summary'>show summary by days</a></li>
	</ul>

	<div id='summaryWrapper' class='element-hidden element-collectable' @if( $sickToday )style='display: none'@endif>
		<h3>Summary</h3><span class='js-ajax-loader ajax-loader element-hidden'><img src='{{ url( "loading.gif" ) }}' /></span>

		<div id='summary'>
			{{-- ajax content here --}}
		</div>
	</div>

	<div id='story' class='timesheet-story' @if ( !Auth::user()->showStory && !$sickToday ) style='display: none' @endif>
		<h3>What happened?</h3>
		<textarea class='js-timesheet-story' placeholder='You like to give a feedback on this day?'>@if( isset( $timesheet ) && $timesheet->story ){{ $timesheet->story }}@endif</textarea>
	</div>

	<div id='columns' class='columns js-columns element-collectable' @if( !Auth::user()->showColumns && !$sickToday) style='display: none' @endif>
		<h3>Columns</h3><span class='js-ajax-loader ajax-loader'><img src='{{ url( "loading.gif" ) }}' /></span>
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
		// find the cloneable tr and clone it
		var trEmpty = $jQ( 'tr.js-tisheet-clonable' );
		var trClone = trEmpty.clone();

		// insert before the cloneable tr and show
		trClone.insertBefore( trEmpty );
		trClone.removeClass( 'js-tisheet-clonable element-hidden' );
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