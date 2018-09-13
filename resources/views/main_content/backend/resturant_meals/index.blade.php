@extends('layouts.backend')

@section('pageTitle')
{{$menu_section->title }}
@endsection

@section('js')
<script src="{{url('public/backend/js')}}/resturant_meals.js" type="text/javascript"></script>
@endsection
@section('content')
{{ csrf_field() }}


<div class = "panel panel-default">
    <div class = "panel-heading">
        <a class="panel-title data-box">{{ $menu_section->title }}</a>


    </div>
    <div class = "panel-body">
        <!--Table Wrapper Start-->

        <table class = "table table-striped table-bordered table-hover table-checkable order-column dataTable no-footer">
            <thead>
                <tr>
                    <th>{{_lang('app.title')}}</th>
                    <th>{{_lang('app.image')}}</th>
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