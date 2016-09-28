{{-- returned when user selects Main Context from the table on the left side --}}

<div class='button-group'>
    <button class='button button-active js-button-summary-and-operator' style='width: 96px'>Match all</button>
</div>

<div class='button-group' ts='{{ $tts }}'>
	@foreach ( $words as $word )
	<button>{{ $word }}</button>
	@endforeach
</div>

<div id='summary-by-context-words' style='margin-top: 18px'>
	@include( 'ajax.summary-groupby-context-filter-words' )
</div>

<script type="text/javascript">

	$jQ( function()
	{
		$jQ( document ).on( 'click', 'div.button-group > button', function () 
		{
			$jQ(this).toggleClass( 'button-active' )

			var url = '{{ url( "tisheets/$today/summary/$option/groupby/contexts/" . $context_id . "/words" ) }}'
			var time = $jQ(this).parent().attr( 'ts' );
            var operatorButton = $jQ(document).find( 'button.js-button-summary-and-operator' );

            if ( operatorButton.hasClass( 'button-active' ) )
                operatorButton = 'and'
            else
                operatorButton = 'or'

			var buttons = []
			$jQ(this).parent().find( 'button.button-active' ).each( function()
			{
				buttons.push( $jQ(this).text() )
			});

			var words = buttons.join( ',' )

			$jQ.ajax({
				url: url,
				type: 'get',
				data: {
					ws: words,
					tts: time,
                    and: operatorButton
				},
				success: function( data )
				{
					$jQ( '#summary-by-context-words' ).html( data )
				}
			});

			return false;
		})
	});

</script>