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
            var label = $jQ(this).val().trim();
            
            if ( label == '' )
                return;

            var column = $jQ(this).closest( 'li.js-column' );

            // post label and update id
            $jQ.ajax({
                url: '{{ url( "tisheets" ) }}/'+ $jQ( '#timesheet' ).today() +'/columns/'+ column.attr( 'id' ),
                type: 'put',
                data: { lb: label },
                success: function( data )
                {
                    if ( data.error )
                        return;

                    column.attr( 'id', data.id );
                }
            });

            // clone 
            var elementToClone = column;
            var clonedColumn = elementToClone.clone();
            
            elementToClone.find( 'input' ).val( '' );
            elementToClone.removeAttr( 'id' );
            clonedColumn.removeClass( 'js-column-clonable element-invisible' );
            clonedColumn.insertBefore( elementToClone ) // ?
        });

        // 
        $jQ( document ).on( 'focusout', 'input.js-column-item-label', function()
        {
            var label = $jQ(this).val().trim();

            if ( label == '' )
                return;

            var columnItem = $jQ(this).closest( 'li.js-column-item' );
            var column = columnItem.closest( 'li.js-column' )

            var elementToClone = columnItem;

            // ignore when parent is not saved yet
            if ( column.hasClass( 'element-invisible' ) )
                return;

            // post label and update id
            $jQ.ajax({
                url: '{{ url( "tisheets" ) }}/'+ $jQ( '#timesheet' ).today() +'/columns/'+ column.attr( 'id' ) +'/item/'+ columnItem.attr( 'id' ),
                type: 'put',
                data: { lb: label },
                success: function( data )
                {
                    if ( data.error )
                        return;

                    columnItem.attr( 'id', data.id );
                }
            });

            var clonedColumn = elementToClone.clone();
            
            elementToClone.find( 'input' ).val( '' );
            clonedColumn.removeClass( 'js-column-item-clonable element-invisible' );
            clonedColumn.insertBefore( elementToClone ) // ?
        });
    });

</script>