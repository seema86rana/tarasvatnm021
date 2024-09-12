<div class="sidebar sidebar-main">
    <div class="sidebar-content">
        <!-- User menu -->
        <div class="sidebar-user">
            <div class="category-content">
                <div class="media">
                    <a href="javascript: void(0)" class="media-left">
                        @if(!empty(Auth::user()->profile_image))
                            <img src="{{ url('/assets/profile_image').'/'.Auth::user()->profile_image }}" class="img-circle img-sm bg-white" alt="{{ Auth::user()->name ?? '' }}">
						@else
                            <img src="{{ asset('assets/admin/images/admin-profile.png') }}" class="img-circle img-sm bg-white" alt="{{ Auth::user()->name ?? '' }}">
						@endif
                    </a>
                    <div class="media-body">
                        <span class="media-heading text-semibold">{{ Auth::user()->name ?? '-' }}</span>
                        <div class="text-size-mini text-muted">
                            <i class="icon-user-tie text-size-small"></i> {{ userRole() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="sidebar-category sidebar-category-visible">
            <div class="category-content no-padding">
                <ul class="navigation navigation-main navigation-accordion">
                    <!-- Main -->
                    @if(menuAccesspermission('dashboard'))
                    <li class="{{ request()->routeIs('dashboard.index') ? 'active' : '' }}">
                        <a href="{{ route('dashboard.index') }}">
                            <i class="icon-home4"></i> <span>Dashboard</span>
                        </a>
                    </li>
                    @endif
                    @if(menuAccesspermission('users'))
                    <li class="{{ request()->routeIs('users.index') ? 'active' : '' }}">
                        <a href="{{ route('users.index') }}">
                            <i class="icon-users"></i> <span>User</span>
                        </a>
                    </li>
                    @endif
                    @if(menuAccesspermission('roles'))
                    <li class="{{ request()->routeIs('roles.index') ? 'active' : '' }}">
                        <a href="{{ route('roles.index') }}">
                            <i class="icon-menu6"></i> <span>Role</span>
                        </a>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>