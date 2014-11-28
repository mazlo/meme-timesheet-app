@foreach( $timeline as $element )
	<? if( $element->prefLabel ) $label=substr( $element->prefLabel, 1 ); else $label='nolabel'; ?>
	<div ts='{{ $element->total_time_spent }}'><span>{{ $element->prefLabel }}</span><span>{{ $element->total_time_spent/4 }}h</span></div>
@endforeach

<script type="text/javascript">

	$jQ( function()
	{
		// adjusts the width according to the natural width of the div and 
		// then to the computed width, if it is greater
		$jQ( '#timeline-today div' ).each( function()
		{
			var ts = $jQ(this).attr( 'ts' );
			var widthInPixel = $jQ(this).css( 'width' ).split( 'px' )[0];
			var widthInPercent = ( widthInPixel / $jQ(this).parent().css( 'width' ).split( 'px' )[0] ) * 100;
			var newWidthInPercent = (ts/48)*100;

			if ( newWidthInPercent > widthInPercent )
				$jQ(this).css( 'width', newWidthInPercent +'%' );
		});
	});

</script>