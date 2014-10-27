@extends( 'layout' )

<?php
	$todayAsTime = strtotime( $today );
?>

@section( 'header' )

	<div class='options element-float-right'>
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
				<col width='3%'>
				<col width='55%'>
				<col width='25%'>
				<col width='4%'>
				<col width='4%'>
			</colgroup>
			
			<tr>
				<th></th>
				<th>No.</th>
				<th>Task Description (planned?)</th>
				<th>Time Spent / Estimate Time</th>
				<th>Total</th>
				<th></th>
			</tr>

		@foreach( $tisheets as $key => $tisheet )
			<tr class='item js-tisheet-options' id='{{ $tisheet->id}}'>
				<td>
					<span class='octicon octicon-trashcan element-invisible' style='padding-left: 3px'></span>
					<span class='octicon octicon-info element-invisible' style='padding-left: 3px'></span>
				</td>
				
				<td class='js-tisheet-no'>{{ $key+1 }}.</td>
				<td>
					{{ Form::text( 'description', $tisheet->description, array( 'class' => 'textfield tisheet-description' ) ) }}
					<input class='js-tisheet-planned' type='checkbox' @if( $tisheet->planned ) checked='checked' @endif />

					<div class='js-tisheet-note element-hidden' style='margin-top: 8px'>
						<textarea class='tisheet-note'>@if ( $tisheet->note ){{ $tisheet->note->content }}@endif</textarea>
					</div>
				</td>
				
				<td class='tisheet-time-spent'>

				{{-- render actual time spent --}}
				@for( $i=0; $i<$tisheet->time_spent; $i++ )
					@if( $i != 0 && $i % 4 == 0 )
						<span class='time-spent-blank'></span>
					@endif

					<span class='js-tisheet-time time-spent-quarter time-spent-quarter-active'></span>
				@endfor

				{{-- print remaining time spent --}}
				@for( $i=$tisheet->time_spent; $i<16; $i++ )
					@if( $i != 0 && $i % 4 == 0 )
						<span class='time-spent-blank'></span>
					@endif

					<span class='js-tisheet-time time-spent-quarter'></span>
				@endfor
				</td>

				<td class='tisheet-total-time-spent'>{{ $tisheet->time_spent*0.25 }}h</td>
				<td>
					<span class='octicon octicon-check element-hidden'></span>
					<span class='js-ajax-loader ajax-loader element-hidden'><img src='{{ url( "loading.gif" ) }}' /></span>
				</td>
			</tr>
		@endforeach

			{{-- insert an empty cloneable tr that is cloned when needed --}}
			<tr class='item js-tisheet-options js-item-clonable element-hidden' id='undefined'>
				<td>
					<span class='octicon octicon-trashcan element-invisible' style='padding-left: 3px'></span>
					<span class='octicon octicon-info element-invisible' style='padding-left: 3px'></span>
				</td>
				<td class='js-tisheet-no'></td>
				<td>
					{{ Form::text( 'description', '', array( 'class' => 'textfield tisheet-description' ) ) }}
					<input class='js-tisheet-planned' type='checkbox' />

					<div class='js-tisheet-note element-hidden' style='margin-top: 8px'>
						<textarea class='tisheet-note'></textarea>
					</div>
				</td>
				<td>
					<span class='js-tisheet-time time-spent-quarter'></span>
					<span class='js-tisheet-time time-spent-quarter'></span>
					<span class='js-tisheet-time time-spent-quarter'></span>
					<span class='js-tisheet-time time-spent-quarter'></span>
					<span class='time-spent-blank'></span>
					<span class='js-tisheet-time time-spent-quarter'></span>
					<span class='js-tisheet-time time-spent-quarter'></span>
					<span class='js-tisheet-time time-spent-quarter'></span>
					<span class='js-tisheet-time time-spent-quarter'></span>
					<span class='time-spent-blank'></span>
					<span class='js-tisheet-time time-spent-quarter'></span>
					<span class='js-tisheet-time time-spent-quarter'></span>
					<span class='js-tisheet-time time-spent-quarter'></span>
					<span class='js-tisheet-time time-spent-quarter'></span>
					<span class='time-spent-blank'></span>
					<span class='js-tisheet-time time-spent-quarter'></span>
					<span class='js-tisheet-time time-spent-quarter'></span>
					<span class='js-tisheet-time time-spent-quarter'></span>
					<span class='js-tisheet-time time-spent-quarter'></span>
				</td>
				<td class='tisheet-total-time-spent'></td>
				<td>
					<span class='octicon octicon-check element-hidden'></span>
					<span class='js-ajax-loader ajax-loader element-hidden'><img src='{{ url( "loading.gif" ) }}' /></span>
				</td>
			</tr>

			<tr>
				<td colspan='4'>&nbsp;</td>
				<td class='js-tisheet-today-total' style='border-top: 1px solid #ccc'></td>
			</tr>

		</table>

	</div>

	<div class='options'>
		<ul class='list-inline'>
			<li><a href='{{ url( "tisheets/$today/summary/week/groupby/contexts" ) }}' class='js-show-summary option'>show summary by contexts</a></li>
			<li><a href='{{ url( "tisheets/$today/summary/week/groupby/days/contexts" ) }}' class='js-show-summary option'>show summary by days</a></li>
		</ul>
	</div>

	<div id='summaryWrapper' class='element-hidden'>
		<h3>Summary <span class='js-ajax-loader ajax-loader element-hidden'><img src='{{ url( "loading.gif" ) }}' /></span></h3>

		<div id='summary'>
			{{-- ajax content here --}}
		</div>
	</div>

{{-- if there are no tisheets, print an initial empty one --}}
@if ( count( $tisheets ) == 0 )
<script type='text/javascript'>

	$jQ( function()
	{
		// find the cloneable tr and clone it
		var trEmpty = $jQ( '.js-item-clonable' );
		var trClone = trEmpty.clone();

		// insert before the cloneable tr and show
		trClone.insertBefore( trEmpty );
		trClone.find( '.js-tisheet-no' ).text( trClone.index()+ '.' );
		trClone.removeClass( 'js-item-clonable element-hidden' );
	});

</script>
@endif

@stop

@section( 'footer' )
	@include( 'footer' )
@stop
