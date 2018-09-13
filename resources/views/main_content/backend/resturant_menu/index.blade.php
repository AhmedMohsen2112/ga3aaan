@extends('layouts.backend')

@section('pageTitle', _lang('app.menu'))

@section('js')
<script src="{{url('public/backend/js')}}/resturant_menu.js" type="text/javascript"></script>
@endsection
@section('content')

<div class = "panel panel-default">
    <div class = "panel-heading">
        <a class="panel-title data-box"> {{_lang('app.menu')}}</a>


    </div>
    <div class = "panel-body">
        <!--Table Wrapper Start-->

        <table class = "table table-striped table-bordered table-hover table-checkable order-column dataTable no-footer">
            <thead>
                <tr>
                    <th>{{_lang('app.title')}}</th>
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
}
</script>
@endsection