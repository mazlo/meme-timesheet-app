<table>
	<colgroup>
		<col width='50%'>
		<col width='50%'>
	</colgroup>

	<tr>
		<th>Days Spent at Context {{ $context }}</th>
		<th>Time Spent</th>
	</tr>

	@foreach( $summary as $key => $tisheet )
	<tr>

		<? $day = date( 'l, dS M.', strtotime( $tisheet->day ) ) ?>
		<td>{{ $day }}</td>
		<td>
			<div class='js-variable-background variable-background' ts='{{ $tisheet->time_spent }}'>
				<span>{{ $tisheet->time_spent/4 }}h</span>
			</div>
		</td>

	</tr>
	@endforeach

</table>

<script type="text/javascript">

	$jQ( function()
	{
		{{-- set background to proportional with of total time spent --}}
		$jQ( '#summary-by-context-details .js-variable-background' ).each( function()
		{
			var width = ($jQ(this).attr( 'ts' ) / {{ $tts }}) * 100;
			$jQ(this).css( 'width', width + '%' );
		});
	});

</script>