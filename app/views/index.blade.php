@extends( 'layout' )

@section( 'header' )

	<div id='options' style='float: right'>
		<ul class='list-inline'>
			<li><a class='js-show-summary option'>show summary</a></li>
		</ul>
	</div>

@stop

@section( 'content' )

	<div class='title'>
		<a href='{{ url( "tisheets/". $tomorrow ) }}'><span class='octicon octicon-arrow-right' title='{{ $tomorrow }}'></span></a>
		<a href='{{ url( "tisheets/today" ) }}'><span class='octicon js-tisheet-today' style='font-size: 13px'>today</span></a>
		<a href='{{ url( "tisheets/". $yesterday ) }}'><span class='octicon octicon-arrow-left' title='{{ $yesterday }}'></span></a>
	</div>
	
	<h2>ya timesheet for @if( $today == date( 'Y-m-d', time() ) ) today - @endif {{ date( 'l, dS M.', strtotime( $today ) ) }}</h2>

	<div id='timesheet' day='{{ $today }}' style='margin: 32px 0;'>

		{{ Form::open( array( 'id' => 'tisheet-form' ) ) }}

		<table style='border: 0; width: 100%; font-size: 13px; text-align: left'>
			
			<colgroup>
				<col width='3%'>
				<col width='3%'>
				<col width='57%'>
				<col width='25%'>
				<col width='3%'>
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
			<tr class='item' id='{{ $tisheet->id}}'>
				<td class='js-tisheet-delete'><span class='octicon octicon-trashcan' style='padding:0'></span></td>
				
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
				<td class='js-tisheet-delete'><span class='octicon octicon-trashcan' style='padding:0'></span></td>
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
				<td class='js-tisheet-delete'><span class='octicon octicon-trashcan' style='padding:0'></span></td>
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
			<li><span class='js-button js-button-active js-get-summary' for='today'>today</span></li>
			<li><span class='js-button js-get-summary' for='week'>last week</span></li>
		</ul>

		<div id='summary'>
			<img src='{{ url( "loading.gif" ) }}' />
		</div>
	</div>

@stop
