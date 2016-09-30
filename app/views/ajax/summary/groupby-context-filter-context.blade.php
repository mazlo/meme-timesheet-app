{{-- returned when user selects Main Context from the table on the left side --}}

<div class='button-group'>
    <button class='button-active js-button-summary-and-operator' style='width: 96px'>Match all</button>
</div>

<div class='button-group js-button-group-words' ts='{{ $tts }}' url='{{ url( "tisheets/$today/summary/$option/groupby/contexts/" . $context_id . "/words" ) }}'>
    @foreach ( $words as $word )
    <button>{{ $word }}</button>
    @endforeach
</div>

<div id='summary-by-context-words' style='margin-top: 18px'>
    @include( 'ajax.summary.groupby-context-filter-words' )
</div>