<ul>
@foreach( $columns as $column )
    <li class='column js-column'>{{ $column->label }}
        <ul>
            @foreach( $column->items as $item )
            <li>{{ $item->label }}
            </li>
            @endforeach
            <li class='column-item js-column-item-clonable element-invisible'><input type='text' placeholder='value'></li>
        </ul>
    </li>
@endforeach

    <li class='column js-column js-column-cloneable element-invisible'>
        <input type='text' placeholder='label'>
        <ul>
            <li class='column-item js-column-item-clonable element-invisible'><input type='text' placeholder='value'></li>
        </ul>
    </li>
</ul>

<script type='text/javascript'>

    $jQ( function()
    {
        //
        $jQ( document ).on( 'hover', 'li.js-column', function() 
        {
            $jQ(this).find( 'li.js-column-item-clonable' ).toggleClass( 'element-invisible' );
        });
    });

</script>