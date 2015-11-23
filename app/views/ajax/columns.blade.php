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

            {{-- this is empty and clonable --}}
            <li class='column-item js-column-item js-column-item-clonable element-invisible'>
                <textarea class='column-item-label js-column-item-label' type='text' placeholder='item description'></textarea>
                <span class='octicon octicon-trashcan octicon-no-padding-left element-invisible'></span>
            </li>
        </ul>
    </li>
@endforeach

    {{-- this is empty and clonable --}}
    <li class='column js-column js-column-clonable element-invisible' id='undefined'>
        <input class='column-label js-column-label' type='text' placeholder='column label'>
        <ul>
            <li class='column-item js-column-item js-column-item-clonable element-invisible'><textarea class='column-item-label js-column-item-label' type='text' placeholder='item description'></textarea></li>
        </ul>
    </li>
</ul>

<script type='text/javascript'>

    $jQ = jQuery.noConflict();

    $jQ( function()
    {
        $jQ( document ).on( 'focusin', 'input.js-column-label', function()
        {
            oldColumnLabel = $jQ( this ).val();
        });

        $jQ( document ).on( 'focusin', 'textarea.js-column-item-label', function()
        {
            oldColumnItemLabel = $jQ( this ).val();
        });

        //
        $jQ( document ).on( 'focusout', 'input.js-column-label', function()
        {
            var label = $jQ(this).val().trim();
            
            if ( label == '' )
                return; // ignore empty values

            if ( oldColumnLabel == label )
                return; // ignore if nothing changed

            var column = $jQ(this).closest( 'li.js-column' );
            var clonedColumn = column.clone();

            // post label and update id
            $jQ.ajax({
                url: '{{ url( "tisheets" ) }}/'+ $jQ( '#timesheet' ).today() +'/columns/'+ column.attr( 'id' ),
                type: 'put',
                data: { lb: label },
                success: function( data )
                {
                    if ( data.status == 'error' )
                        return;

                    clonedColumn.attr( 'id', data.id );
                }
            });

            if ( column.next( '.js-column-clonable' ).length > 0 )
                return; // nothing to clone

            // clone 
            column.find( 'input' ).val( '' );
            column.removeAttr( 'id' );

            clonedColumn.removeClass( 'js-column-clonable element-invisible' );
            clonedColumn.insertBefore( column );
        });

        // 
        $jQ( document ).on( 'focusout', 'textarea.js-column-item-label', function()
        {
            var label = $jQ(this).val().trim();

            if ( label == '' )
                return; // ignore empty values

            if ( oldColumnItemLabel == label )
                return; // ignore if nothing changed

            var columnItem = $jQ(this).closest( 'li.js-column-item' );
            var column = columnItem.closest( 'li.js-column' )

            // ignore when parent is not saved yet
            if ( column.hasClass( 'element-invisible' ) )
                return;

            var clonedItem = columnItem.clone();

            // post label and update id
            $jQ.ajax({
                url: '{{ url( "tisheets" ) }}/'+ $jQ( '#timesheet' ).today() +'/columns/'+ column.attr( 'id' ) +'/item/'+ columnItem.attr( 'id' ),
                type: 'put',
                data: { lb: label },
                success: function( data )
                {
                    if ( data.status == 'error' )
                        return;

                    clonedItem.attr( 'id', data.id );
                }
            });

            // ignore when there is already a cloned column-item
            if ( columnItem.next( '.js-column-item-clonable' ).length > 0 )
                return;

            columnItem.find( 'textarea' ).val( '' );

            clonedItem.find( 'textarea' ).val( label );
            clonedItem.removeClass( 'js-column-item-clonable element-invisible' );
            clonedItem.insertBefore( columnItem );
        });

    });

    //
    $jQ( document ).on( 'click', 'li.js-column-item span.octicon-trashcan', function()
    {
        var columnItem = $jQ(this).closest( 'li.js-column-item' );
        var column = columnItem.closest( 'li.js-column' );
        
        columnItem.hide();

        $jQ.ajax({
            url: '{{ url( "tisheets" ) }}/'+ $jQ( '#timesheet' ).today() +'/columns/'+ column.attr( 'id' ) +'/item/'+ columnItem.attr( 'id' ),
            type: 'delete',
            success: function( data )
            {
                if ( data.status != 'ok' )
                    return;

                columnItem.remove();
            }
        });
    });

</script>
