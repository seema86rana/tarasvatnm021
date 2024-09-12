@if ( !empty( $breadcrumbs ) )
	<div class="breadcrumb-line">
		<ul class="breadcrumb">
			<li><a href="{{ route('dashboard.index') }}"><i class="icon-home2 position-left"></i> Home</a></li>
			@foreach ( $breadcrumbs as $k => $breadcrumb )
				@if ( $k == 'javascript: void(0)' )
					<li class="active">{{ $breadcrumb }}</li>
				@else
					<li><a href="{{ $k }}">{{ $breadcrumb }}</a></li>
				@endif
			@endforeach
		</ul>
	</div>
@endif