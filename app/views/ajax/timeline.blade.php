@foreach( $timeline as $element )
	<? if( $element->prefLabel ) $label=substr( $element->prefLabel, 1 ); else $label='nolabel'; ?>
	<div ts='{{ $element->total_time_spent }}' style='width: {{ ($element->total_time_spent/48)*100 }}%'><span>{{ $element->prefLabel }}</span><span>{{ $element->total_time_spent/4 }}h</span></div>
@endforeach