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
	</div>
	
	<h2>ya timesheet for @if( $today == date( 'Y-m-d', time() ) ) today - @endif {{ date( 'l, dS M.', $todayAsTime ) }}</h2>

	@if ( date( 'l', $todayAsTime ) == 'Sunday' )
		<div>Jeez, it's Sunday, why are you working at all?</div>
	@endif

	<div id='timesheet' class='timesheet' day='{{ $today }}'>
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
				<th>Time Spent / Estimate Time</th>
				<th>Started</th>
				<th>Total</th>
				<th></th>
			</tr>

		@foreach( $tisheets as $key => $tisheet )
			<tr class='tisheet js-tisheet js-tisheet-options' id='{{ $tisheet->id}}'>
				<td>
					<span class='octicon octicon-trashcan element-invisible'></span>
					<span class='octicon octicon-info @if( $tisheet->note ) element-visible @else element-invisible @endif'></span>
				</td>
				
				<td>
					{{ Form::text( 'description', $tisheet->description, array( 'class' => 'textfield tisheet-description' ) ) }}
					<span class='octicon octicon-playback-play js-octicon-stopwatch element-invisible'></span>

					<div class='js-tisheet-note element-hidden' style='margin-top: 8px'>
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
				</td>
			</tr>
		@endforeach

			{{-- insert an empty cloneable tr that is cloned when needed --}}
			<tr class='tisheet js-tisheet js-tisheet-options js-tisheet-clonable element-hidden' id='undefined'>
				<td>
					<span class='octicon octicon-trashcan element-invisible'></span>
					<span class='octicon octicon-info element-invisible'></span>
				</td>
				<td>
					{{ Form::text( 'description', '', array( 'class' => 'textfield tisheet-description' ) ) }}
					<span class='octicon octicon-playback-play js-octicon-stopwatch element-invisible'></span>

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

	<ul class='list-inline js-button-group'>
		<li><a href='{{ url( "tisheets/$today/summary/week/groupby/contexts" ) }}' class='js-button js-button-summary'>show summary by contexts</a></li>
		<li><a href='{{ url( "tisheets/$today/summary/week/groupby/days/contexts" ) }}' class='js-button js-button-summary'>show summary by days</a></li>
	</ul>

	<div id='summaryWrapper' class='element-hidden'>
		<h3>Summary <span class='js-ajax-loader ajax-loader element-hidden'><img src='{{ url( "loading.gif" ) }}' /></span></h3>

		<div id='summary'>
			{{-- ajax content here --}}
		</div>
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