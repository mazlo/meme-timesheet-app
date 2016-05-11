<h3>Columns</h3><span class='js-ajax-loader ajax-loader element-hidden'><img src='{{ url( "loading.gif" ) }}' /></span>
<ul>
@foreach( $columns as $column )
    @if ( !$column->trashed )
    <li class='column js-column' id='{{ $column->id }}' @if( $column->color ) style='background-color: {{ $column->color }}' @endif>
        <div class='column-label js-column-label'>
            <input class='js-column-label-input' type='text' placeholder='item description' value='{{ $column->label }}' @if( $column->color ) style='background-color: {{ $column->color }}' @endif>
            <span class='octicon octicon-trashcan octicon-no-padding-left element-invisible-toggable'></span>
        </div>
        <ul>
            @foreach( $column->items as $item )
                @if ( !$item->trashed ) 
            <li class='column-item js-column-item' id='{{ $item->id }}'>
                <textarea class='column-item-label js-column-item-label' type='text' placeholder='item description'>{{ $item->label }}</textarea>

                <div class='column-item-options js-column-item-options element-hidden-toggable'>
                    <span class='octicon octicon-trashcan octicon-no-padding-left'></span>                    
                </div>

            </li>
                @endif
            @endforeach

            {{-- this is empty and empty --}}
            <li class='column-item js-column-item js-column-item-empty element-invisible-toggable'>
                <textarea class='column-item-label js-column-item-label' type='text' placeholder='item description'></textarea>
                <span class='octicon octicon-trashcan octicon-no-padding-left element-invisible-toggable'></span>
            </li>
        </ul>

        <div class='column-item-color-palette'>
            <span style='background-color: #e1e2cd' color='#e1e2cd'>&nbsp;</span>
            <span style='background-color: #efe292' color='#efe292'>&nbsp;</span>
            <span style='background-color: #d7d2cc' color='#d7d2cc'>&nbsp;</span>
            <span style='background-color: #8e9eab' color='#8e9eab'>&nbsp;</span>
            <span style='background-color: #948E99' color='#948E99'>&nbsp;</span>
            <span style='background-color: #eef2f3' color='#eef2f3'>&nbsp;</span>
        </div>
    </li>
    @endif
@endforeach

    {{-- this is empty and empty --}}
    <li class='column js-column js-column-empty element-invisible-toggable' id='undefined'>
        <div class='column-label js-column-label'>
            <input class='js-column-label-input' type='text' placeholder='column label'>
            <span class='octicon octicon-trashcan octicon-no-padding-left element-invisible-toggable'></span>
        </div>
        <ul>
            <li class='column-item js-column-item js-column-item-empty element-invisible-toggable'><textarea class='column-item-label js-column-item-label' type='text' placeholder='item description'></textarea></li>
        </ul>

        <div class='column-item-color-palette'>
            <span style='background-color: #e1e2cd' color='#e1e2cd'>&nbsp;</span>
            <span style='background-color: #efe292' color='#efe292'>&nbsp;</span>
            <span style='background-color: #d7d2cc' color='#d7d2cc'>&nbsp;</span>
            <span style='background-color: #8e9eab' color='#8e9eab'>&nbsp;</span>
            <span style='background-color: #948E99' color='#948E99'>&nbsp;</span>
            <span style='background-color: #eef2f3' color='#eef2f3'>&nbsp;</span>
        </div>
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
