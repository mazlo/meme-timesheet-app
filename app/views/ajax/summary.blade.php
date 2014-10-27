<ul class='list-inline list-inline-padded js-button-group'>
	<li><a href='{{ url( "tisheets/$today/summary/today/groupby/contexts" ) }}' class='js-button @if( $option == "today" ) js-button-active @endif js-get-summary'>today</a></li>
	<li><a href='{{ url( "tisheets/$today/summary/week/groupby/contexts" ) }}' class='js-button @if( $option == "week" ) js-button-active @endif js-get-summary'>last week</a></li>
	<li><a href='{{ url( "tisheets/$today/summary/month/groupby/contexts" ) }}' class='js-button @if( $option == "month" ) js-button-active @endif js-get-summary'>last month</a></li>
</ul>

<div id='summary-by-context' tts='' style='display: inline-block; vertical-align: top; margin-right: 32px;'>
	<table style='border: 1px #ccc solid; width: 480px; padding: 3px 13px; text-align: left'>
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
		@foreach( $summary as $key => $object )

		<tr>
			<td><a href='{{ url( "tisheets/$today/summary/week/groupby/days/contexts/". substr( $object->prefLabel, 1 ) ) }}' ts='{{ $object->total_time_spent }}' class='js-get-summary-by-context'>{{ $object->prefLabel }}</a></td>
			<td>
				<div class='js-background-variable' ts='{{ $object->total_time_spent }}' style='background-color: #c0c0c0'>
					<span style='padding: 0 8px'>{{ $object->total_time_spent/4 }}h</span>
				</div>
			</td>
		</tr>

		<? $tts += $object->total_time_spent ?>

		@endforeach

	@else
		<tr>
			<td colspan='2'>No entries for today.</td>
		</tr>
	@endif

	</table>
</div>

<div id='summary-by-context-details' style='display: inline-block; vertical-align: top; margin-right: 32px;'>
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

		$jQ( '#summary-by-context' ).attr( 'tts', {{ $tts }} );
	});

</script>