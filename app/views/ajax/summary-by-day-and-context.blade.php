<table>
	<colgroup>
		<col width='50%'>
		<col width='50%'>
	</colgroup>

	<tr>
		<th>Days Spent with {{ $context }}</th>
		<th>Time Spent in Context</th>
	</tr>

	<? $day = '' ?>
	@foreach( $summary as $key => $tisheet )
	<tr>

		<? $currentDay = date( 'l, dS M.', strtotime( $tisheet->day ) ) ?>
		<td>@if ( $day != $currentDay ) <a href='{{ url( "tisheets/". substr( $tisheet->day, 0, 10 ) ) }}'>{{ $currentDay }}</a> <? $day = $currentDay ?> @endif</td>
		<td>
			<div class='js-variable-background variable-background' ts='{{ $tisheet->time_spent }}'>
				<span>{{ $tisheet->time_spent/4 }}h {{ $tisheet->subContext }}</span>
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