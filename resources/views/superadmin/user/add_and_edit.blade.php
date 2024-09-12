<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLongTitle">{{ $modal_title}}</h5>
    <button type="button" class="close close-modal" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    <form class="{{ isset($user) ? 'edit-user-form' : 'add-user-form' }}" id="{{ isset($user) ? 'edit-user-form' : 'add-user-form' }}" action="#" method="post" autocomplete="off">
        @csrf
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
        @if ( isset( $user ) )
            <input type="hidden" name="id" id="id" value="{{ $user->id }}" />
        @endif
        <div class="form-group">
            <div class="row">
                <div class="col-md-12">
                    <label class="col-form-label label_text text-lg-right" for="name">User Full Name <small class="req text-danger">*</small></label>
                    <input type="text" name="name" class="form-control name" id="name" placeholder="Enter user full name" tabindex="1" value="{{ isset($user) ? $user->name : old('name') }}" required autofocus />
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-md-12">
                    <label class="col-form-label label_text text-lg-right" for="role_id">Select Role <small class="req text-danger">*</small></label>
                    <select class="form-control select2" name="role_id"  id="role_id" tabindex="2" required>
                        <option value="">Select a user</option>
                        @foreach ($role as $value)
                            @if(isset($user))
                                @if ($user->role_id == $value->id)
                                    <option value="{{$value->id }}" selected>{{ $value->name }}</option>
                                @else
                                    <option value="{{ $value->id }}">{{ $value->name }}</option>
                                @endif
                            @else
                                <option value="{{$value->id}}">{{$value->name}}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-md-12">
                    <label class="col-form-label label_text text-lg-right" for="phone_number">Phone Number <small class="req text-danger">*</small></label>
                    <input type="text" name="phone_number" class="form-control phone_number" id="phone_number" placeholder="Enter phone number" tabindex="3" value="{{ isset($user) ? $user->phone_number : old('phone_number') }}" onkeyup="this.value=this.value.replace(/[^\d]/,'')" required />
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-md-12">
                    <label class="col-form-label label_text text-lg-right" for="email">Email <small class="req text-danger">*</small></label>
                    <input type="email" name="email" class="form-control email" id="email" placeholder="Enter email" tabindex="4" value="{{ isset($user) ? $user->email : old('email') }}" {{ isset($user) ? 'disabled' : 'required' }} />
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-md-12">
                    <label class="col-form-label label_text text-lg-right" for="password">Password <small class="req text-danger">*</small></label>
                    <input type="password" name="password" class="form-control password" id="password" placeholder="Enter password" tabindex="5" value="{{ isset($user) ? $user->password : old('password') }}" {{ isset($user) ? 'disabled' : 'required' }} />
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-md-12">
                    <label class="col-form-label label_text text-lg-right" for="password_confirmation">Password Confirmation <small class="req text-danger">*</small></label>
                    <input type="password" name="password_confirmation" class="form-control password_confirmation" id="password_confirmation" placeholder="Enter password confirmation" tabindex="6" value="{{ isset($user) ? $user->password : old('password_confirmation') }}" {{ isset($user) ? 'disabled' : 'required' }} />
                </div>
            </div>
        </div>
        <div class="form-group mb-2 text-right">
            <button type="button" class="btn btn-theme-dark close-modal" style="float: inline-start;">
                <i class="icon-arrow-left13"></i> Back
            </button>
            <button type="button" data-id="{{ isset($user) ? $user->id : '' }}" class="btn btn-theme-dark {{ isset($user) ? 'update-user' : 'save-user' }}" id="{{ isset($user) ? 'update-user' : 'save-user' }}" style="float: inline-end;">
                <i class="icon-check"></i> {{ isset($user) ? 'Update' : 'Add' }}
            </button>
        </div>
    </form>
</div>
<div class="modal-footer">
</div>
<script>
    $(document).ready(function () {
        $('.select2').select2({
            placeholder: 'Select a user',
            allowClear: true,
            width: '100%'
        });
    });
</script>