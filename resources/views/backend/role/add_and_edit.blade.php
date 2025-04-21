<style>
    .scrollable-div {
        height: 250px;
        overflow-y: auto;
        border: 1px solid #ccc;
        padding: 10px;
    }
    ul {
        padding-left: 0;
        list-style-type: none;
    }
    .form-check-label, .form-check-input {
        cursor: pointer;
    }
</style>
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
        <div class="form-group">
            <div class="row">
                <div class="col-md-12">
                    <label class="col-form-label label_text text-lg-right" for="name">Role Permission <small class="req text-danger">*</small></label>
                    <div class="scrollable-div">
                        <ul class="list-unstyled">
                            @if(isset($role))
                                @php $permission = json_decode($role->permission, true); @endphp
                            @else
                                @php $permission = []; @endphp
                            @endif
                            @if($menu && count($menu) > 0)
                                @foreach($menu as $key => $menu)
                                    @php $unique = $menu['route']; @endphp
                                    @if(count($menu['sub_menu']) > 0 && !empty($unique))
                                        <li>
                                            <div class="form-check">
                                                <input class="form-check-input parent_menu {{ $unique }}_parent" data-route="{{ $unique }}" name="permission[]" type="checkbox" value="{{ $unique }}" id="{{ $unique }}" {{ in_array($unique, $permission) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="{{ $unique }}">
                                                    {{ $menu['name'] }}
                                                </label>
                                            </div>
                                            <!-- Submenu for "Role & Permission" -->
                                            <ul class="ms-4" style="margin-left: 20px;">
                                                @foreach($menu['sub_menu'] as $key => $subMenu)
                                                    @if(!empty($subMenu['route']))
                                                        <li>
                                                            <div class="form-check">
                                                                <input class="form-check-input child_menu {{ $unique }}_child" data-parent="{{ $unique }}" name="permission[]" type="checkbox" value="{{ $subMenu['route'] }}" id="{{ $subMenu['route'] }}" {{ in_array($subMenu['route'], $permission) ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="{{ $subMenu['route'] }}">
                                                                    {{ $subMenu['name'] }}
                                                                </label>
                                                            </div>
                                                        </li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        </li>
                                    @else
                                        @if(!empty($unique))
                                            @if($key == 0)
                                                <input  name="permission[]" type="hidden" value="{{$unique}}">
                                            @endif
                                            <li>
                                                <div class="form-check">
                                                    <input class="form-check-input" {{ ($key == 0) ? 'checked disabled' : '' }} name="permission[]" type="checkbox" value="{{ $unique }}" id="{{ $unique }}" {{ in_array($unique, $permission) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="{{ $unique }}">
                                                        {{ $menu['name'] }}
                                                    </label>
                                                </div>
                                            </li>
                                        @endif
                                    @endif
                                @endforeach
                            @endif
                        </ul>
                    </div>
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