@extends('layouts.backend')

@section('pageTitle')
{{ _lang('app.meals') }}
@endsection
@section('breadcrumb')
<li><a href="{{url('admin')}}">{{_lang('app.dashboard')}}</a> <i class="fa fa-circle"></i></li>
<li><a href="{{url('admin/resturantes')}}">{{_lang('app.resturantes')}}</a> <i class="fa fa-circle"></i></li>
<li><a href="{{url('admin/menu_sections?resturant='.$menu_section->resturant_id)}}">{{_lang('app.menu_sections')}}</a> <i class="fa fa-circle"></i></li>
<li><span> {{_lang('app.meals')}}</span></li>

@endsection

@section('js')
<script src="{{url('public/backend/js')}}/meals.js" type="text/javascript"></script>
@endsection
@section('content')
{{ csrf_field() }}

<div class = "panel panel-default">

    <div class = "panel-body">
        <!--Table Wrapper Start-->
        <a class = "btn btn-sm btn-info pull-left" style = "margin-bottom: 40px;"  href = "{{ route('meals.create')}}?menu_section={{ $menu_section->id }}" > {{_lang('app.add_new')}} </a>

        <table class = "table table-striped table-bordered table-hover table-checkable order-column dataTable no-footer">
            <thead>
                <tr>
                    <th>{{_lang('app.title')}}</th>
                    <th>{{_lang('app.order')}}</th>
                    <th>{{_lang('app.status')}}</th>
                    <th>{{_lang('app.options')}}</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>

        <!--Table Wrapper Finish-->
    </div>
</div>
<script>
var new_lang = {

};
var new_config = {
    menu_section:"{{$menu_section->id}}"
}
</script>
@endsection