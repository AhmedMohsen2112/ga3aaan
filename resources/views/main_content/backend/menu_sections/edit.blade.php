@extends('layouts.backend')

@section('pageTitle')
{{ _lang('app.edit_menu_section') }}
@endsection
@section('breadcrumb')
<li><a href="{{url('admin')}}">{{_lang('app.dashboard')}}</a> <i class="fa fa-circle"></i></li>
<li><a href="{{url('admin/resturantes')}}">{{_lang('app.resturantes')}}</a> <i class="fa fa-circle"></i></li>
<li><a href="{{url('admin/menu_sections?resturant='.$resturant->id)}}">{{_lang('app.menu_sections')}}</a> <i class="fa fa-circle"></i></li>
<li><span> {{_lang('app.edit')}}</span></li>

@endsection
@section('js')
<script src="{{url('public/backend/js')}}/menu_sections.js" type="text/javascript"></script>
@endsection
@section('content')
<form role="form"  id="addEditMenuSectionsForm" enctype="multipart/form-data">
    {{ csrf_field() }}

    <div class="panel panel-default" id="addEditMenuSections">
        <div class="panel-heading">
            <h3 class="panel-title">{{_lang('app.section_info') }}</h3>
        </div>
        <div class="panel-body">


            <div class="form-body">
                <input type="hidden" name="id" id="id" value="{{ $menu_section->id }}">
                <input type="hidden" name="resturant"  value="{{ $resturant->id }}">
                <div class="form-group form-md-line-input col-md-6">
                    <input type="text" class="form-control" id="title_ar" name="title_ar" value="{{ $menu_section->title_ar }}">
                    <label for="title_ar">{{_lang('app.title_ar') }}</label>
                    <span class="help-block"></span>
                </div>
                <div class="form-group form-md-line-input col-md-6">
                    <input type="text" class="form-control" id="title_en" name="title_en" value="{{ $menu_section->title_en }}">
                    <label for="title_en">{{_lang('app.title_en') }}</label>
                    <span class="help-block"></span>
                </div>
                <div class="form-group form-md-line-input col-md-3">
                    <input type="number" class="form-control" id="this_order" name="this_order" value="{{ $menu_section->this_order }}">
                    <label for="this_order">{{_lang('app.this_order') }}</label>
                    <span class="help-block"></span>
                </div>
                <div class="form-group form-md-line-input col-md-3">
                    <select class="form-control edited" id="active" name="active">
                        <option {{ $menu_section->active == 1 ? 'selected':'' }}  value="1">{{ _lang('app.active') }}</option>
                        <option {{ $menu_section->active == 0 ? 'selected':'' }}  value="0">{{ _lang('app.not_active') }}</option>
                    </select>
                    <label for="status">{{_lang('app.status') }}</label>
                    <span class="help-block"></span>
                </div> 




            </div>

            <!--Table Wrapper Finish-->
        </div>
        <div class="panel-footer text-center">
            <button type="button" class="btn btn-info submit-form"
                    >{{_lang('app.save') }}</button>
        </div>

    </div>
   


</form>
<script>
var new_lang = {

};
var new_config = {
    toppings: '{!!$toppings!!}'
}
</script>
@endsection