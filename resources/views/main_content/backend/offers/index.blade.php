@extends('layouts.backend')

@section('pageTitle', _lang('app.offers'))

@section('js')
<script src="{{url('public/backend/js')}}/offers.js" type="text/javascript"></script>
@endsection
@section('content')
{{ csrf_field() }}
     
    @if (session("message"))
         <div class="alert alert-success alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
            {{ session('message') }}
        </div>
    @endif
<div class = "panel panel-default">
    <div class = "panel-heading">
        <a class="panel-title data-box" data-where="outTable"  data-id="0" data-level="0">{{_lang('app.all')}}</a>

    </div>
    <div class = "panel-body">
        <!--Table Wrapper Start-->
        <a class = "btn btn-sm btn-info pull-left" style = "margin-bottom: 40px;"  href = "offers/create" > {{_lang('app.add_new')}} </a>

        <table class = "table table-striped table-bordered table-hover table-checkable order-column dataTable no-footer">
            <thead>
                <tr>
                    <th>{{_lang('app.returant')}}</th>
                    <th>{{_lang('app.image')}}</th>
                    <th>{{_lang('app.discount')}}</th>
                    <th>{{_lang('app.available_until')}}</th>
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
        'add_category': "{{_lang('app.add_category')}}",
        'edit_category': "{{_lang('app.edit_category')}}",
       
    };
</script>
@endsection