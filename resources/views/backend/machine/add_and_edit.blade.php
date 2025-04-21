<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLongTitle">{{ $modal_title}}</h5>
    <button type="button" class="close close-modal" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    <form class="{{ isset($machine) ? 'edit-machine-form' : 'add-machine-form' }}" id="{{ isset($machine) ? 'edit-machine-form' : 'add-machine-form' }}" action="#" method="post" autocomplete="off">
        @csrf
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
        @if ( isset( $machine ) )
            <input type="hidden" name="id" id="id" value="{{ $machine->id }}" />
        @endif
        <div class="form-group">
            <div class="row">
                <div class="col-md-6">
                    <label class="col-form-label label_text text-lg-right" for="display_name">Machine Name <small class="req text-danger">*</small></label>
                    <input type="text" name="display_name" class="form-control display_name" id="display_name" placeholder="Enter machine name" tabindex="1" value="{{ isset($machine) ? (isset($machine->display_name) ? $machine->display_name : $machine->name) : old('name') }}" required autofocus />
                </div>
                <div class="col-md-6">
                    <label class="col-form-label label_text text-lg-right" for="priority">Priority</label>
                    <div class="form-control">
                        <input type="radio" name="priority" id="priority" value="1" {{ isset($machine) && $machine->priority == 1 ? 'checked' : '' }}> Yes
                        &nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="radio" name="priority" id="priority" value="0" {{ isset($machine) && $machine->priority == 0 ? 'checked' : '' }}> No
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group mb-2 text-right">
            <button type="button" class="btn btn-theme-dark close-modal" style="float: inline-start;">
                <i class="icon-arrow-left13"></i> Back
            </button>
            <button type="button" data-id="{{ isset($machine) ? $machine->id : '' }}" class="btn btn-theme-dark {{ isset($machine) ? 'update-machine' : 'save-machine' }}" id="{{ isset($machine) ? 'update-machine' : 'save-machine' }}" style="float: inline-end;">
                <i class="icon-check"></i> {{ isset($machine) ? 'Update' : 'Add' }}
            </button>
        </div>
    </form>
</div>
<div class="modal-footer">
</div>