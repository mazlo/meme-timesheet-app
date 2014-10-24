<table style='border: 1px #ccc solid; width: 480px; padding: 3px 13px; text-align: left'>
	<colgroup>
		<col width='50%'>
		<col width='50%'>
	</colgroup>

	<tr>
		<th>Day</th>
		<th>Time Spent</th>
	</tr>

	@foreach( $summary as $key => $tisheet )
	<tr>

		<? $day = date( 'l, dS M.', strtotime( $tisheet->day ) ) ?>
		<td>{{ $day }}</td>
		<td style='width: 300px'>
			<div class='js-background-variable' ts='{{ $tisheet->time_spent }}' style='background-color: #c0c0c0'>
				<span style='padding: 0 8px'>{{ $tisheet->time_spent/4 }}h</span>
			</div>
		</td>

	</tr>
	@endforeach

</table>

<script type="text/javascript">

	$jQ( function()
	{
		$jQ( '#summary-by-context-details .js-background-variable' ).each( function()
		{
			var width = ($jQ(this).attr( 'ts' ) / {{ $tts }}) * 100;
			$jQ(this).css( 'width', width + '%' );
		});
	});

</script>