[ "" @foreach( $tisheets as $tisheet ), "{{ str_replace( '"', '\"', $tisheet->description ) }}" @endforeach ]