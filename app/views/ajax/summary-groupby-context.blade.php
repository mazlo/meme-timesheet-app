{{-- returned when user clicks on the time period, e.g. 'current week', 'current month', after clicking on 'Summary by Context' --}}

<ul class='list-inline list-inline-padded js-button-group'>
	<li><a href='{{ url( "tisheets/$today/summary/today/groupby/contexts" ) }}' class='js-button @if( $option == "today" ) js-button-active @endif js-button-summary'>today</a></li>
	<li><a href='{{ url( "tisheets/$today/summary/week/groupby/contexts" ) }}' class='js-button @if( $option == "week" ) js-button-active @endif js-button-summary'>current week</a></li>
	<li><a href='{{ url( "tisheets/$today/summary/month/groupby/contexts" ) }}' class='js-button @if( $option == "month" ) js-button-active @endif js-button-summary'>current month</a></li>
	<li><a href='{{ url( "tisheets/$today/summary/year/groupby/contexts" ) }}' class='js-button @if( $option == "year" ) js-button-active @endif js-button-summary'>current year</a></li>
</ul>

<div id='summary-by-context' tts='' class='summary-table element-float-left'>
	<table>
		<colgroup>
			<col width='50%'>
			<col width='50%'>
		</colgroup>
		
		<tr>
			<th>Main Contexts</th>
			<th>Total Time Spent in Context</th>
		</tr>

	<? $tts = 0 ?>
	@if ( count( $summary ) > 0 )
		@foreach( $summary as $key => $tisheet )

		<tr>
			<td>
				<? if( $tisheet->prefLabel ) $label=$tisheet->prefLabel; else $label='w/o context'; ?>
				{{-- display link only in option 'week' --}}
				@if( $label != 'w/o context' )
				<a id='{{ substr( $tisheet->prefLabel, 1 ) }}' href='{{ url( "tisheets/$today/summary/$option/groupby/days/contexts/". substr( $tisheet->prefLabel, 1 ) ) }}' ts='{{ $tisheet->total_time_spent }}' class='js-button-summary-by-context'>{{ $label }}</a>
				@else
					{{ $label }}
				@endif
			</td>
			
			<td>
				<div class='js-variable-background variable-background' ts='{{ $tisheet->total_time_spent }}'>
					<span>{{ $tisheet->total_time_spent/4 }}h</span>
				</div>
			</td>
		</tr>

		{{-- sum up all tisheets to total time spent --}}
		<? $tts += $tisheet->total_time_spent ?>

		@endforeach

	@else
		<tr>
			<td colspan='2'>No entries for today.</td>
		</tr>
	@endif

	</table>
</div>

<div id='summary-by-context-details' class='summary-table element-float-right'>
	{{-- ajax response here --}}
</div>

<script type="text/javascript">

	$jQ( function()
	{
		{{-- set background color width proportional to total time spent --}}
		$jQ( '#summary-by-context .js-variable-background' ).each( function()
		{
			var width = ($jQ(this).attr( 'ts' ) / {{ $tts }}) * 100;
			$jQ(this).css( 'width', width + '%' );
		});

		{{-- save summed up total time spent in attribute of div --}}
		$jQ( '#summary-by-context' ).attr( 'tts', {{ $tts }} );
	});

</script>