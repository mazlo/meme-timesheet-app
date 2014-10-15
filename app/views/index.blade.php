@extends( 'layout' )

<?php
	$todayAsTime = strtotime( $today );
?>

@section( 'header' )

	<div id='options' style='float: right'>
		<ul class='list-inline'>
			<li>Hello {{ Auth::user()->username }}</li>
			<li><a href='{{ url( "tisheets/". $today ."/summary/today" ) }}' class='js-show-summary option'>show summary</a></li>
			<li><a href='{{ url( "logout" ) }}' class='option'>logout</a></li>
		</ul>
	</div>

@stop

@section( 'content' )

	<div class='title'>
		<a href='{{ url( "tisheets/". $tomorrow ) }}'><span class='octicon octicon-float-right octicon-arrow-right' title='{{ $tomorrow }}'></span></a>
		<a href='{{ url( "tisheets/today" ) }}'><span class='octicon octicon-float-right js-tisheet-today' style='font-size: 13px'>today</span></a>
		<a href='{{ url( "tisheets/". $yesterday ) }}'><span class='octicon octicon-float-right octicon-arrow-left' title='{{ $yesterday }}'></span></a>
	</div>
	
	<h2>ya timesheet for @if( $today == date( 'Y-m-d', time() ) ) today - @endif {{ date( 'l, dS M.', $todayAsTime ) }}</h2>

	@if ( date( 'l', $todayAsTime ) == 'Sunday' )
		<div>Jeez, it's Sunday, why are you working at all?</div>
	@endif

	<div id='timesheet' day='{{ $today }}' style='margin: 32px 0;'>

		{{ Form::open( array( 'id' => 'tisheet-form' ) ) }}

		<table cellpadding='0' cellspacing='0' style='border: 0; width: 100%; font-size: 13px; text-align: left'>
			
			<colgroup>
				<col width='2%'>
				<col width='3%'>
				<col width='57%'>
				<col width='25%'>
				<col width='4%'>
				<col width='4%'>
			</colgroup>
			
			<tr>
				<th></th>
				<th>No.</th>
				<th>Task Description</th>
				<th>Time Spent</th>
				<th>Total</th>
				<th></th>
			</tr>

		@foreach( $tisheets as $key => $tisheet )
			<tr class='item js-enable-trashcan' id='{{ $tisheet->id}}'>
				<td><span class='octicon octicon-trashcan element-invisible js-tisheet-delete' style='padding:0'></span></td>
				
				<td class='js-tisheet-no'>{{ $key+1 }}.</td>
				<td>{{ Form::text( 'description', $tisheet->description, array( 'class' => 'description' ) ) }}</td>
				
				<td class='tisheet-col-time-spent'>

				{{-- render actual time spent --}}
				@for( $i=0; $i<$tisheet->time_spent; $i++ )
					@if( $i != 0 && $i % 4 == 0 )
						<span class='time-spent-blank'></span>
					@endif

					<span class='time-spent-quarter time-spent-quarter-active'></span>
				@endfor

				{{-- print remaining time spent --}}
				@for( $i=$tisheet->time_spent; $i<16; $i++ )
					@if( $i != 0 && $i % 4 == 0 )
						<span class='time-spent-blank'></span>
					@endif

					<span class='time-spent-quarter'></span>
				@endfor
				</td>

				<td class='tisheet-col-total'>{{ $tisheet->time_spent*0.25 }}h</td>
				<td class='js-tisheet-check element-hidden'><span class="octicon octicon-check"></span></td>
			</tr>
		@endforeach

		{{-- if there are no tisheets, print an initial empty one --}}
		@if ( count( $tisheets ) == 0 )
			<tr class='item' id='undefined'>
				<td><span class='octicon octicon-trashcan element-invisible js-tisheet-delete' style='padding:0'></span></td>
				<td class='js-tisheet-no'>1.</td>
				<td>{{ Form::text( 'description', '', array( 'class' => 'description' ) ) }}</td>
				<td>
					<span class='time-spent-quarter'></span>
					<span class='time-spent-quarter'></span>
					<span class='time-spent-quarter'></span>
					<span class='time-spent-quarter'></span>
					<span class='time-spent-blank'></span>
					<span class='time-spent-quarter'></span>
					<span class='time-spent-quarter'></span>
					<span class='time-spent-quarter'></span>
					<span class='time-spent-quarter'></span>
					<span class='time-spent-blank'></span>
					<span class='time-spent-quarter'></span>
					<span class='time-spent-quarter'></span>
					<span class='time-spent-quarter'></span>
					<span class='time-spent-quarter'></span>
					<span class='time-spent-blank'></span>
					<span class='time-spent-quarter'></span>
					<span class='time-spent-quarter'></span>
					<span class='time-spent-quarter'></span>
					<span class='time-spent-quarter'></span>
				</td>
				<td class='tisheet-col-total'></td>
				<td class='js-tisheet-check element-hidden'><span class="octicon octicon-check"></span></td>
			</tr>
		@endif

			<tr class='item element-hidden' id='undefined'>
				<td><span class='octicon octicon-trashcan element-invisible js-tisheet-delete' style='padding:0'></span></td>
				<td class='js-tisheet-no'></td>
				<td>{{ Form::text( 'description', '', array( 'class' => 'description' ) ) }}</td>
				<td>
					<span class='time-spent-quarter'></span>
					<span class='time-spent-quarter'></span>
					<span class='time-spent-quarter'></span>
					<span class='time-spent-quarter'></span>
					<span class='time-spent-blank'></span>
					<span class='time-spent-quarter'></span>
					<span class='time-spent-quarter'></span>
					<span class='time-spent-quarter'></span>
					<span class='time-spent-quarter'></span>
					<span class='time-spent-blank'></span>
					<span class='time-spent-quarter'></span>
					<span class='time-spent-quarter'></span>
					<span class='time-spent-quarter'></span>
					<span class='time-spent-quarter'></span>
					<span class='time-spent-blank'></span>
					<span class='time-spent-quarter'></span>
					<span class='time-spent-quarter'></span>
					<span class='time-spent-quarter'></span>
					<span class='time-spent-quarter'></span>
				</td>
				<td class='tisheet-col-total'></td>
				<td class='js-tisheet-check element-hidden'><span class="octicon octicon-check"></span></td>
			</tr>

			<tr>
				<td colspan='4'>&nbsp;</td>
				<td class='js-tisheet-today-total' style='border-top: 1px solid #ccc'></td>
			</tr>

		</table>

		{{ Form::close() }}

	</div>

	<div id='summaryWrapper' class='element-hidden'>
		<h3>Summary</h3>

		<ul class='list-inline list-inline-padded js-button-group'>
			<li><a href='{{ url( "tisheets/". $today ."/summary/today" ) }}' class='js-button js-button-active js-get-summary'>today</a></li>
			<li><a href='{{ url( "tisheets/". $today ."/summary/week" ) }}' class='js-button js-get-summary'>last week</a></li>
		</ul>

		<div id='summary'>
			<img src='{{ url( "loading.gif" ) }}' />
		</div>
	</div>

@stop
