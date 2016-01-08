@extends( 'layout' )

<?php
	$todayAsTime = strtotime( $today );
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

	<div class='title'>
		<a href='{{ url( "tisheets/$tomorrow" ) }}'><span class='octicon octicon-arrow-right element-float-right' title='{{ $tomorrow }}'></span></a>
		<a href='{{ url( "tisheets/today" ) }}'><span class='octicon-text js-tisheet-today element-float-right'>today</span></a>
		<a href='{{ url( "tisheets/$yesterday" ) }}'><span class='octicon octicon-arrow-left element-float-right' title='{{ $yesterday }}'></span></a>
		<span class='octicon octicon-plus datepicker element-float-right'></span>
		<span class='element-invisible element-float-right' id='datepicker'></span>
	</div>
	
	<h2>ya timesheet for @if( $today == date( 'Y-m-d', time() ) ) today - @endif {{ date( 'l, dS M.', $todayAsTime ) }}</h2>

	<div id='topic'>
		<textarea class='timesheet-topic js-timesheet-topic' placeholder='Do you want this day to have a special aim?'>@if( isset( $timesheet ) && $timesheet->topic ){{ $timesheet->topic }}@endif</textarea>
	</div>

	@if ( date( 'l', $todayAsTime ) == 'Sunday' )
		<div>Jeez, it's Sunday, why are you working at all?</div>
	@endif

	<div id='timesheet' class='timesheet' day='{{ $today }}'>
		<table cellpadding='0' cellspacing='0'>
			
			<colgroup>
				<col width='6%'>
				<col width='50%'>
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
					<span class='octicon octicon-trashcan element-toggable'></span>
					<span class='octicon octicon-info @if( $tisheet->note ) element-visible @else element-toggable @endif'></span>
				</td>
				
				<td>
					{{ Form::text( 'description', $tisheet->description, array( 'class' => 'textfield tisheet-description js-tisheet-description' ) ) }}
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
					<span class='octicon octicon-trashcan element-toggable'></span>
					<span class='octicon octicon-info element-toggable'></span>
				</td>
				<td>
					{{ Form::text( 'description', '', array( 'class' => 'textfield tisheet-description js-tisheet-description' ) ) }}
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

	<div id='timeline-today' class='timeline-today'>
		@include( 'ajax.timeline' )
	</div>

	<ul class='list-inline js-button-group' style='margin-left: 11px'>
		<li><a href='{{ url( "tisheets/$today/summary/week/groupby/contexts" ) }}' class='js-button js-button-summary'>show summary by contexts</a></li>
		<li><a href='{{ url( "tisheets/$today/summary/week/groupby/days/contexts" ) }}' class='js-button js-button-summary'>show summary by days</a></li>
	</ul>

	<div id='summaryWrapper' class='element-hidden'>
		<h3>Summary</h3><span class='js-ajax-loader ajax-loader element-hidden'><img src='{{ url( "loading.gif" ) }}' /></span>

		<div id='summary'>
			{{-- ajax content here --}}
		</div>
	</div>

	<div id='columns' class='columns js-columns'>
		<h3>Columns</h3><span class='js-ajax-loader ajax-loader'><img src='{{ url( "loading.gif" ) }}' /></span>
		{{-- ajax content here --}}
	</div>

	<div id='summary-same-as'>
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

</script>


@stop