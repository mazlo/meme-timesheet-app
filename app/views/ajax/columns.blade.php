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

        //
        $jQ( document ).on( 'focusout', 'input.js-column-label', function()
        {
            // save js-column

            if ( $jQ(this).val().trim() == '' )
                return;

            var elementToClone = $jQ(this).closest( 'li.js-column' );
            var clonedColumn = elementToClone.clone();
            
            elementToClone.find( 'input' ).val( '' );
            clonedColumn.removeClass( 'js-column-clonable element-invisible' );
            clonedColumn.insertBefore( elementToClone ) // ?
        });

        // 
        $jQ( document ).on( 'focusout', 'input.js-column-item-label', function()
        {
            // save js-column-item

            if ( $jQ(this).val().trim() == '' )
                return;

            var elementToClone = $jQ(this).closest( 'li.js-column-item' );

            if ( elementToClone.closest( 'li.js-column' ).hasClass( 'element-invisible' ) )
                return;

            var clonedColumn = elementToClone.clone();
            
            elementToClone.find( 'input' ).val( '' );
            clonedColumn.removeClass( 'js-column-item-clonable element-invisible' );
            clonedColumn.insertBefore( elementToClone ) // ?
        });
    });

</script>