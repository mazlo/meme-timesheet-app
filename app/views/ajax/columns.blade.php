<h3>Columns</h3><span class='js-ajax-loader ajax-loader element-hidden'><img src='{{ url( "loading.gif" ) }}' /></span>
<ul>
@foreach( $columns as $column )
    @if ( !$column->trashed )
    <li class='column js-column' id='{{ $column->id }}'>
        <div class='column-label js-column-label'>
            <input class='js-column-label-input' type='text' placeholder='item description' value='{{ $column->label }}'>
            <span class='octicon octicon-trashcan octicon-no-padding-left element-invisible'></span>
        </div>
        <ul>
            @foreach( $column->items as $item )
            <li class='column-item js-column-item' id='{{ $item->id }}'>
                <textarea class='column-item-label js-column-item-label' type='text' placeholder='item description'>{{ $item->label }}</textarea>
                <span class='octicon octicon-trashcan octicon-no-padding-left element-invisible'></span>
            </li>
            @endforeach

            {{-- this is empty and empty --}}
            <li class='column-item js-column-item js-column-item-empty element-invisible'>
                <textarea class='column-item-label js-column-item-label' type='text' placeholder='item description'></textarea>
                <span class='octicon octicon-trashcan octicon-no-padding-left element-invisible'></span>
            </li>
        </ul>
    </li>
    @endif
@endforeach

    {{-- this is empty and empty --}}
    <li class='column js-column js-column-empty element-invisible' id='undefined'>
        <div class='column-label js-column-label'>
            <input class='js-column-label-input' type='text' placeholder='column label'>
            <span class='octicon octicon-trashcan octicon-no-padding-left element-invisible'></span>
        </div>
        <ul>
            <li class='column-item js-column-item js-column-item-empty element-invisible'><textarea class='column-item-label js-column-item-label' type='text' placeholder='item description'></textarea></li>
        </ul>
    </li>
</ul>

<script type="text/javascript">

    $jQ( function()
    {
        // adjust height of all textareas on load
        $jQ( '#columns textarea' ).each( function()
        {
            adjustHeightOfTextarea( this );
        })
    });

</script>
