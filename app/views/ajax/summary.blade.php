<ul class='list-inline list-inline-padded js-button-group'>
	<li><a href='{{ url( "tisheets/$today/summary/today/groupby/contexts" ) }}' class='js-button @if( $option == "today" ) js-button-active @endif js-get-summary'>today</a></li>
	<li><a href='{{ url( "tisheets/$today/summary/week/groupby/contexts" ) }}' class='js-button @if( $option == "week" ) js-button-active @endif js-get-summary'>last week</a></li>
	<li><a href='{{ url( "tisheets/$today/summary/month/groupby/contexts" ) }}' class='js-button @if( $option == "month" ) js-button-active @endif js-get-summary'>last month</a></li>
</ul>

<div id='summary-by-context' tts='' style='float: left; vertical-align: top; width: 48%'>
	<table style='border: 1px #ccc solid; width: 100%; padding: 3px 13px; text-align: left'>
		<colgroup>
			<col width='50%'>
			<col width='50%'>
		</colgroup>
		
		<tr>
			<th>Contexts of Last Week</th>
			<th>Total Time Spent</th>
		</tr>

	<? $tts = 0 ?>
	@if ( count( $summary ) > 0 )
		@foreach( $summary as $key => $tisheet )

		<tr>
			<td>
				{{-- display link only in option 'week' --}}
				@if( $option == 'week' )
				<a href='{{ url( "tisheets/$today/summary/week/groupby/days/contexts/". substr( $tisheet->prefLabel, 1 ) ) }}' ts='{{ $tisheet->total_time_spent }}' class='js-get-summary-by-context'>{{ $tisheet->prefLabel }}</a>
				@else
				{{ $tisheet->prefLabel }}
				@endif
			</td>
			
			<td>
				<div class='js-background-variable' ts='{{ $tisheet->total_time_spent }}' style='background-color: #c0c0c0'>
					<span style='padding: 0 8px'>{{ $tisheet->total_time_spent/4 }}h</span>
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

<div id='summary-by-context-details' style='float: right; vertical-align: top; width: 48%'>
	{{-- ajax response here --}}
</div>

<script type="text/javascript">

	$jQ( function()
	{
		$jQ( '#summary-by-context .js-background-variable' ).each( function()
		{
			var width = ($jQ(this).attr( 'ts' ) / {{ $tts }}) * 100;
			$jQ(this).css( 'width', width + '%' );
		});

		{{-- save summed up total time spent in attribute of div --}}
		$jQ( '#summary-by-context' ).attr( 'tts', {{ $tts }} );
	});

</script>