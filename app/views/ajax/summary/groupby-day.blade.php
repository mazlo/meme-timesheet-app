<div class='cc-element-hidden'>
	<a href='{{ url( "tisheets/$today/summary/week/groupby/days/contexts" ) }}' class='js-button js-button-active js-button-summary'>show summary by days</a>
</div>

<table style='border: 1px #ccc solid; padding: 3px 13px; text-align: left'>
	
	<tr>

@if ( count( $summary ) == 0 )
	<td colspan='2'>No entries found.</td>
@else

	<? $lastDay = '' ?>
	@foreach( $summary as $key => $tisheet )

		<? $day = date( 'l, dS M.', strtotime( $tisheet->day ) ) ?>
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
