<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLongTitle">{{ $modal_title}}</h5>
    <button type="button" class="close close-modal" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    <form class="{{ isset($role) ? 'edit-role-form' : 'add-role-form' }}" id="{{ isset($role) ? 'edit-role-form' : 'add-role-form' }}" action="#" method="post" autocomplete="off">
        @csrf
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
        @if ( isset( $role ) )
            <input type="hidden" name="id" id="id" value="{{ $role->id }}" />
        @endif
        <div class="form-group">
            <div class="row">
                <div class="col-md-12">
                    <label class="col-form-label label_text text-lg-right" for="name">Role Name <small class="req text-danger">*</small></label>
                    <input type="text" name="name" class="form-control name" id="name" placeholder="Enter role name" tabindex="1" value="{{ isset($role) ? $role->name : old('name') }}" required autofocus />
                </div>
            </div>
        </div>
        <div class="form-group mb-2 text-right">
            <button type="button" class="btn btn-theme-dark close-modal" style="float: inline-start;">
                <i class="icon-arrow-left13"></i> Back
            </button>
            <button type="button" data-id="{{ isset($role) ? $role->id : '' }}" class="btn btn-theme-dark {{ isset($role) ? 'update-role' : 'save-role' }}" id="{{ isset($role) ? 'update-role' : 'save-role' }}" style="float: inline-end;">
                <i class="icon-check"></i> {{ isset($role) ? 'Update' : 'Add' }}
            </button>
        </div>
    </form>
</div>
<div class="modal-footer">
</div>