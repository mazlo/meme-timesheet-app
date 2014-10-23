<table style='border: 1px #ccc solid; padding: 3px 13px; text-align: left'>
	
	<tr>

@if ( count( $summary ) == 0 )
	<td colspan='2'>No entries found.</td>
@else

	<? $lastDay = '' ?>
	@foreach( $summary as $key => $tisheet )

		<? $day = date( 'Y-m-d', strtotime( $tisheet->day ) ) ?>
		@if ( $lastDay != $day )
			<th>{{ $day }}</th>
			<th></th>
			<? $lastDay = $day ?>
		@endif
		
		{{-- creates a multidimensional array which looks like this: array[ '2014-10-02' ][ '@private' ] = $tisheet --}}
		<? $summaryTable[ ''. $day ][ ''. $tisheet->prefLabel ] = $tisheet ?>
	@endforeach

	</tr>

	<tr style='vertical-align: top'>

	@foreach( $summaryTable as $day => $contexts )
	
		<td colspan='2'>
		
			<? $summaryPerDay = 0 ?>
			@foreach( $contexts as $tisheet )
			
			<div style='display: table; padding: 0'>
				<div style='display: table-cell; width: 120px; padding: 0 8px 0 4px'>{{ $tisheet->prefLabel }}</div>
				<div style='display: table-cell; padding: 0 8px'>{{ $tisheet->time_spent/4 }}h</div>
				<? $summaryPerDay += $tisheet->time_spent/4 ?>
			</div>

			@endforeach

			<? $summariesPerDay[ ''. $day ] = $summaryPerDay ?>
		</td>
	
	@endforeach
	
	</tr>

	<tr style='text-align: right'>

		@foreach( $summariesPerDay as $day => $summaryPerDay )
			<td></td>
			<td style='border-top: 1px #ccc solid; padding: 0 11px 0 0'>{{ $summaryPerDay }}h</td>
		@endforeach

@endif

	</tr>

</table>

<table style='border: 1px #ccc solid; padding: 3px 13px; text-align: left'>
	
	<? $lastDay = '' ?>
	@foreach( $summary as $key => $tisheet )
	<tr>
		
		<? $day = date( 'Y-m-d', strtotime( $tisheet->day ) ) ?>
		<td>{{ $day }}</td>
		<td style='width: 500px'><div style='background-color: #c0c0c0; width: {{ $tisheet->time_spent != 0 ? $tisheet->time_spent/4 * 100: 0 }}px'><span style='padding: 0 8px'>{{ $tisheet->prefLabel }}</span></div></td>

	</tr>
	@endforeach

</table>
