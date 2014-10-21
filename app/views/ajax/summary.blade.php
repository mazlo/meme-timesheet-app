<ul class='list-inline list-inline-padded js-button-group'>
	<li><a href='{{ url( "tisheets/". $today ."/summary/today/groupby/contexts" ) }}' class='js-button @if( $option == "today" ) js-button-active @endif js-get-summary'>today</a></li>
	<li><a href='{{ url( "tisheets/". $today ."/summary/week/groupby/contexts" ) }}' class='js-button @if( $option == "week" ) js-button-active @endif js-get-summary'>last week</a></li>
</ul>

<table style='border: 1px #ccc solid; width: 400px; padding: 3px 13px; text-align: left'>
	<colgroup>
		<col width='60%'>
		<col width='40%'>
	</colgroup>
	
	<tr>
		<th>Context</th>
		<th>Total Time Spent</th>
	</tr>

@if ( count( $summary ) > 0 )
	@foreach( $summary as $key => $object )

	<tr>
		<td>{{ $object->prefLabel }}</td>
		<td>{{ $object->total_time_spent/4 }}h</td>
	</tr>

	@endforeach
@else
	<tr>
		<td colspan='2'>No entries for today.</td>
	</tr>
@endif

</table>