@if ( count( $posts ) > 0 )

	@foreach( $posts as $post )
	
	<div class='post' pid='{{ $post->id }}'>
		@include( 'ajax.post' )
	</div>

	<div class='tags cc-element-invisible'>
		<ul class='list-inline' style='padding-left: 23px'>
			<li>{{ $post->tags }}</li>
		</ul>
	</div>
	
	@endforeach

@else

	<p style='margin-top: 23px'>no posts here</p>

@endif