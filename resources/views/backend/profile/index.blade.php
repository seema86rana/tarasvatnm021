@extends('layouts.backend')

@section('title')
{{ $title }}
@endsection

@section('page_header')
<div class="page-header page-header-default">
	@if (Auth::user()->role_id == 0)
		<div class="page-header-content">
			<div class="page-title">
				<h4>
					<i class="icon-user position-left"></i>
					<span class="text-semibold">Profile</span>
				</h4>
			</div>
		</div>
	@endif

    @if ( isset( $breadcrumbs )  && Auth::user()->role_id == 0)
    	@include('layouts.include.backend.breadcrumb', ['breadcrumbs' => $breadcrumbs])
    @endif
</div>
@endsection

@section('content')
	<div class="panel panel-flat">
		<div class="panel-heading">
			<h5 class="panel-title">Update Profile</h5>
		</div>
		<div class="row px-4 removable-flash-messages">
			<div class="col-md-12">
				@include('layouts.include.backend.message')
			</div>
		</div>	
		<div class="panel-body">
			<form action="{{ route('profile.updates', Auth::user()->id) }}" method="post" autocomplete="off" enctype="multipart/form-data">
				@csrf
				@if ( isset( $user ) )
					<input type="hidden" name="id" id="user_id" value="{{ $user->id }}" />
				@endif
				<div class="form-group">
					<div class="row">
						<div class="col-md-6">
							<label class="col-form-label label_text text-lg-right" for="name">Full Name <small class="req text-danger">*</small></label>
							<input type="text" name="name" class="form-control" id="name" placeholder="Enter full name" tabindex="1" value="{{ isset($user) ? $user->name : old('name') }}" autofocus />
							@if ($errors->has('name'))
								<p class="text-danger">{{ $errors->first('name') }}</p>
							@endif
						</div>
						<div class="col-md-6">
							<label class="col-form-label label_text text-lg-right" for="phone_number">Phone Number <small class="req text-danger">*</small></label>
							<input type="text" name="phone_number" class="form-control" id="phone_number" placeholder="Enter phone number" tabindex="3" value="{{ isset($user) ? $user->phone_number : old('phone_number') }}" onkeyup="this.value=this.value.replace(/[^\d]/,'')" required />
							@if ($errors->has('phone_number'))
								<p class="text-danger">{{ $errors->first('phone_number') }}</p>
							@endif
						</div>
					</div>
				</div>
				
				<div class="form-group">
					<div class="row">
						<div class="col-md-6">
							<label class="col-form-label label_text text-lg-right" for="name">Role <small class="req text-danger">*</small></label>
							<input type="text" name="name" class="form-control" id="name" placeholder="Enter full name" tabindex="1" value="{{ userRole() }}" {{ isset($user) ? 'disabled' : '' }} />
						</div>						
						<div class="col-md-6">
							<label class="col-form-label label_text text-lg-right" for="email">Email <small class="req text-danger">*</small></label>
							<input type="email" name="email" class="form-control" id="email" placeholder="Enter email" tabindex="4" value="{{ isset($user) ? $user->email : old('email') }}" {{ isset($user) ? 'disabled' : '' }} />
						</div>
					</div>
				</div>
					
				<div class="form-group">
					<div class="row">
						<div class="col-md-6">
							<label class="col-form-label label_text text-lg-right" for="company_name">Company Name <small class="req text-danger">*</small></label>
							<input type="text" name="company_name" class="form-control" id="company_name" placeholder="Enter company name" tabindex="5" value="{{ isset($user) ? $user->company_name : old('company_name') }}" />
							@if ($errors->has('company_name'))
								<p class="text-danger">{{ $errors->first('company_name') }}</p>
							@endif
						</div>
						<div class="col-md-6">
							<label class="col-form-label label_text text-lg-right" for="gst_number">GST Number <small class="req text-danger">*</small></label>
							<input type="text" name="gst_number" class="form-control" id="gst_number" placeholder="Enter GST number" tabindex="6" value="{{ isset($user) ? $user->gst_number : old('gst_number') }}" />
							@if ($errors->has('gst_number'))
								<p class="text-danger">{{ $errors->first('gst_number') }}</p>
							@endif
						</div>
					</div>
				</div>

				<div class="form-group">
					<div class="row">
						<div class="col-md-6">
							<label class="col-form-label label_text text-lg-right" for="profile_image">Profile Image </label>
							<input type="file" name="profile_image" class="form-control" id="profile_image" tabindex="6" accept=".jpg, .png, .gif, .jpeg" />
							@if ($errors->has('profile_image'))
								<p class="text-danger">{{ $errors->first('profile_image') }}</p>
							@endif
							@if(!empty($user->profile_image))
								<a href="{{ url('/assets/profile_image').'/'.$user->profile_image }}" target="_blank" rel="noopener noreferrer">Profile image</a>
							@endif
						</div>
						<div class="col-md-6">
							<label class="col-form-label label_text text-lg-right" for="address">Address <small class="req text-danger">*</small></label>
							<textarea name="address" class="form-control" id="address" placeholder="Enter address" tabindex="6">{{ isset($user) ? $user->address : old('address') }}</textarea>
							@if ($errors->has('address'))
								<p class="text-danger">{{ $errors->first('address') }}</p>
							@endif
						</div>
					</div>
				</div>
				
				<div class="form-group mb-2 text-right">
					<a href="{{ route('dashboard.index') }}" type="button" class="btn btn-theme-dark" style="float: inline-start;">
						<i class="icon-arrow-left13"></i> Back
					</a>
					<button type="submit" class="btn btn-theme-dark" style="float: inline-end;">
						<i class="icon-check"></i> Save
					</button>
				</div>
			</form>
		</div>
	</div>

	<div class="panel panel-flat">
		<div class="panel-heading">
			<h5 class="panel-title">Update Password</h5>
		</div>
		<div class="panel-body">
			<form action="{{ route('profile.password', Auth::user()->id) }}" method="post" autocomplete="off">
				@csrf
				<div class="form-group">
					<div class="row">
						<div class="col-md-6">
							<label class="col-form-label label_text text-lg-right" for="password">Password <small class="req text-danger">*</small></label>
							<input type="password" name="password" class="form-control" id="password" placeholder="Enter password" tabindex="1"  required autofocus />
							@if ($errors->has('password'))
								<p class="text-danger">{{ $errors->first('password') }}</p>
							@endif
						</div>
						<div class="col-md-6">
							<label class="col-form-label label_text text-lg-right" for="password_confirmation">Confirm Password <small class="req text-danger">*</small></label>
							<input type="password" name="password_confirmation" class="form-control" id="password_confirmation" placeholder="Enter confirm password" tabindex="2" required />
							@if ($errors->has('password_confirmation'))
								<p class="text-danger">{{ $errors->first('password_confirmation') }}</p>
							@endif
						</div>
					</div>
				</div>
				
				<div class="form-group mb-2 text-right">
					<a href="{{ route('dashboard.index') }}" type="button" class="btn btn-theme-dark" style="float: inline-start;">
						<i class="icon-arrow-left13"></i> Back
					</a>
					<button type="submit" class="btn btn-theme-dark" style="float: inline-end;">
						<i class="icon-check"></i> Save
					</button>
				</div>
			</form>
		</div>
	</div>
@endsection

@section('footer_js')
@endsection