@extends('layouts.backend')

@section('pageTitle')
{{_lang('app.resturant_orders') }}
@endsection

@section('js')
<script src="{{url('public/backend/js')}}/resturant_orders.js" type="text/javascript"></script>
@endsection
@section('content')
{{ csrf_field() }}
<input type="hidden" id="type" value="{{ $type }}">

<div class = "panel panel-default">
    <div class = "panel-heading">
        <a class="panel-title data-box">{{_lang('app.orders') }}</a>


    </div>
    <div class = "panel-body">
        <!--Table Wrapper Start-->

        <table class = "table table-striped table-bordered table-hover table-checkable order-column datatable no-footer">
            <thead>
                <tr>
                    <th>{{_lang('app.order_no')}}</th>
                    <th>{{_lang('app.branch')}}</th>
                    <th>{{_lang('app.name')}}</th>
                    <th>{{_lang('app.address')}}</th>
                    <th>{{_lang('app.mobile')}}</th>
                    <th>{{_lang('app.status')}}</th>
                    <th>{{_lang('app.created_at')}}</th>
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