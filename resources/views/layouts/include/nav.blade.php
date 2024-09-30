<div class="navbar navbar-inverse navbar-fixed-top">
	<div class="navbar-header">
		<a  href="{{ url('/') }}" style="display: flex;">
			<img class="FullLogo" src="{{asset('/')}}assets/logo.png" alt="">
			<!-- <img class="IconLogo" src="{{asset('/')}}assets/test_logo.png" alt=""> -->
			<img class="" src="{{asset('/')}}assets/test_logo.png" alt="">
		</a>

		<ul class="nav navbar-nav visible-xs-block">
			<li>
				<a data-toggle="collapse" data-target="#navbar-mobile">
					<i class="icon-tree5"></i>
				</a>
			</li>
			<li>
				<a class="sidebar-mobile-main-toggle">
					<i class="icon-paragraph-justify3"></i>
				</a>
			</li>
		</ul>
	</div>
	<div class="navbar-collapse collapse" id="navbar-mobile">
		<ul class="nav navbar-nav">
			<li>
				<a class="sidebar-control sidebar-main-toggle hidden-xs">
					<i class="icon-paragraph-justify3"></i>
				</a>
			</li>
		</ul>

		<ul class="nav navbar-nav navbar-right ml-auto">
			<li class="dropdown dropdown-user">
				@if(!empty(Auth::user()->profile_image))
					<a class="dropdown-toggle" data-toggle="dropdown">
						<img src="{{ url('/assets/profile_image').'/'.Auth::user()->profile_image }}" class="bg-white" alt="{{ Auth::user()->name ?? '-' }}" />
						<span>{{ Auth::user()->name ?? '-' }}</span>
						<i class="caret"></i>
					</a>
				@else
					<a class="dropdown-toggle" data-toggle="dropdown">
						<img src="{{ asset('assets/admin/images/admin-profile.png') }}" class="bg-white" alt="{{ Auth::user()->name ?? '-' }}" />
						<span>{{ Auth::user()->name ?? '-' }}</span>
						<i class="caret"></i>
					</a>
				@endif
				<ul class="dropdown-menu dropdown-menu-right">
					<li><a href="{{ route('profile.index') }}"><i class="icon-user-plus"></i> My Profile</a></li>
					<li class="divider"></li>
					<li>
						<a href="javascript: void(0)" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
							<i class="icon-switch2"></i> Logout
						</a>
						<form method="POST" id="logout-form" action="{{ route('logout') }}">
							@csrf
						</form>
					</li>
				</ul>
			</li>
		</ul>
	</div>
</div>