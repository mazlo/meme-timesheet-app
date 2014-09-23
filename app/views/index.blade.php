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
		<span class='octicon js-tisheet-today' style='font-size: 13px'>today</span>
		<a href='{{ url( "tisheets/". $yesterday ) }}'><span class='octicon octicon-arrow-left' title='{{ $yesterday }}'></span></a>
	</div>
	
	<h2>Timesheet for Today: {{ date( 'dS, M.', strtotime( $today ) ) }}</h2>

	<div id='timesheet' style='margin: 32px 0;'>

		{{ Form::open( array( 'id' => 'tisheet-form' ) ) }}

		<table style='border: 0; width: 100%; font-size: 13px; text-align: left'>
			
			<colgroup>
				<col width='4%'>
				<col width='58%'>
				<col width='28%'>
				<col width='6%'>
				<col width='4%'>
			</colgroup>
			
			<tr>
				<th>No.</th>
				<th>Task Description</th>
				<th>Time Spent</th>
				<th>Total</th>
				<th></th>
			</tr>

		@foreach( $tisheets as $key => $tisheet )
			<tr class='item' id='{{ $tisheet->id}}'>
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

		</table>

		{{ Form::close() }}

	</div>

	<div id='summary' class='element-hidden'>

	</div>

@stop
