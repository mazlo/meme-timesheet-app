<h3>Columns</h3><span class='js-ajax-loader cc-element-hidden'><img src='{{ url( "loading.gif" ) }}' /></span>
<ul>
@foreach( $columns as $column )
    @if ( !$column->trashed )
    <li class='column js-column' id='{{ $column->id }}' @if( $column->color ) style='background-color: {{ $column->color }}' @endif>
        <div class='column-label js-column-label'>
            <input class='js-column-label-input' type='text' placeholder='item description' value='{{ $column->label }}' @if( $column->color ) style='background-color: {{ $column->color }}' @endif>
            <span class='octicon octicon-trashcan octicon-no-padding-left cc-element-invisible-toggable'></span>
        </div>

        {{-- list of column-items --}}
        <ul class='js-column-items'>
            @foreach( $column->items as $item )
                @if ( !$item->trashed ) 

            {{-- column-item --}}
            <li class='column-item js-column-item' id='{{ $item->id }}'>
                <textarea class='column-item-label js-column-item-label @if ( $item->important )column-item-label-important@endif' type='text' placeholder='item description'>{{ $item->label }}</textarea>

                {{-- options below the textarea --}}
                <div class='column-item-options js-column-item-options cc-element-hidden-toggable'>
                    <span class='octicon octicon-trashcan octicon-no-padding-left'></span>
                    <span class='ionicons ion-alert octicon-lower-padding-left'></span>
                    <span class='ionicons ion-arrow-move octicon-lower-padding-left'></span>
                </div>

            </li>
                @endif
            @endforeach

            {{-- empty column-item, ready to be added --}}
            <li class='column-item js-column-item js-column-item-empty cc-element-invisible-toggable'>
                <textarea class='column-item-label js-column-item-label' type='text' placeholder='item description'></textarea>
                
                {{-- options below the textarea --}}
                <div class='column-item-options js-column-item-options cc-element-hidden-toggable'>
                    <span class='octicon octicon-trashcan octicon-no-padding-left'></span>
                    <span class='ionicons ion-alert octicon-lower-padding-left'></span>
                    <span class='ionicons ion-arrow-move octicon-lower-padding-left'></span>
                </div>
            </li>
        </ul>

        <div class='column-item-color-palette cc-element-invisible-toggable'>
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

    {{-- this is an empty column, ready to be added --}}
    <li class='column js-column js-column-empty cc-element-invisible-toggable' id='undefined'>
        <div class='column-label js-column-label'>
            <input class='js-column-label-input' type='text' placeholder='column label'>
            <span class='octicon octicon-trashcan octicon-no-padding-left cc-element-invisible-toggable'></span>
        </div>

        {{-- list of column-items --}}
        <ul class='js-column-items'>

            {{-- empty column-item, ready to be added --}}
            <li class='column-item js-column-item js-column-item-empty cc-element-invisible-toggable'>
                <textarea class='column-item-label js-column-item-label' type='text' placeholder='item description'></textarea>
                
                {{-- options below the textarea --}}
                <div class='column-item-options js-column-item-options cc-element-hidden-toggable'>
                    <span class='octicon octicon-trashcan octicon-no-padding-left'></span>
                    <span class='ionicons ion-alert octicon-lower-padding-left'></span>
                    <span class='ionicons ion-arrow-move octicon-lower-padding-left'></span>
                </div>
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
