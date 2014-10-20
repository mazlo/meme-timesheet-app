<table style='border: 1px #ccc solid; padding: 3px 13px; text-align: left'>
	
	<tr>

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
			
			<div style='display: table'>
				<div style='display: table-cell; width: 120px'>{{ $tisheet->prefLabel }}</div>
				<div style='display: table-cell; width: 20px'>{{ $tisheet->time_spent/4 }}h</div>
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
			<td style='border-top: 1px #ccc solid'>{{ $summaryPerDay }}h</td>
		@endforeach

	</tr>

</table>