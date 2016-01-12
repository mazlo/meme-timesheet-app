<h3>History of Notes</h3>
<h4>{{ $refTisheet->description }}</h4>

<table cellpadding='0' cellspacing='0'>
    <colgroup>
        <col width='120px'>
        <col width='500px'>
        <col width='25px'
    </colgroup>
@foreach( $tisheets as $tisheet )
    <tr>
        <td>{{ date( 'Y-m-d', strtotime( $tisheet->day ) ) }}</td>
        <td>{{ Markdown::parse( nl2br( $tisheet->content ) ) }}</td>
        <td>{{ $tisheet->time_spent/4 }}h</td>
    </tr>
@endforeach
</table>