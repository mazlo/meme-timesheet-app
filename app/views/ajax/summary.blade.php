<h3>Summary</h3>

<ul class='list-inline list-inline-padded js-button-group'>
	<li><span class='js-button js-button-active'>today</span></li>
	<li><span class='js-button'>last week</span></li>
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

@foreach( $summary as $key => $object )

	<tr>
		<td>{{ $object->prefLabel }}</td>
		<td>{{ $object->total_time_spent/4 }}h</td>
	</tr>

@endforeach

</table>