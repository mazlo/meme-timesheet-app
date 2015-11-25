<h3>Columns</h3>
<ul>
@foreach( $columns as $column )
    <li class='column js-column' id='{{ $column->id }}'><input class='column-label js-column-label' type='text' placeholder='item description' value='{{ $column->label }}'>
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
@endforeach

    {{-- this is empty and empty --}}
    <li class='column js-column js-column-empty element-invisible' id='undefined'>
        <input class='column-label js-column-label' type='text' placeholder='column label'>
        <ul>
            <li class='column-item js-column-item js-column-item-empty element-invisible'><textarea class='column-item-label js-column-item-label' type='text' placeholder='item description'></textarea></li>
        </ul>
    </li>
</ul>
