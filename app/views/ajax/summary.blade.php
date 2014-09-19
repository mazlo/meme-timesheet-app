<h3>Summary for Today</h3>

<table style='border: 1px #ccc solid; width: 400px; padding: 3px 13px; text-align: left'>
	<colgroup>
		<col width='60%'>
		<col width='40%'>
	</colgroup>
	
	<tr>
		<th>Context</th>
		<th>Total Time Spent</th>
	</tr>

@foreach( $summary as $key => $object )

	<tr style='border-bottom: 1px #ccc solid'>
		<td>{{ $object->prefLabel }}</td>
		<td>{{ $object->total_time_spent/4 }}h</td>
	</tr>

@endforeach

</table>