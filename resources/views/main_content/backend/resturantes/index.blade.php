@extends('layouts.backend')

@section('pageTitle', _lang('app.resturantes'))
@section('breadcrumb')
<li><a href="{{url('admin')}}">{{_lang('app.dashboard')}}</a> <i class="fa fa-circle"></i></li>
<li><span> {{_lang('app.resturantes')}}</span></li>

@endsection
@section('js')
<script src="{{url('public/backend/js')}}/resturantes.js" type="text/javascript"></script>
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
    <div class = "panel-body">
        <!--Table Wrapper Start-->
        <a class = "btn btn-sm btn-info pull-left" style = "margin-bottom: 40px;"  href = "resturantes/create" > {{_lang('app.add_new')}} </a>

        <table class = "table table-striped table-bordered table-hover table-checkable order-column dataTable no-footer">
            <thead>
                <tr>
                    <th>{{_lang('app.returant')}}</th>
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
        'add_category': "{{_lang('app.add_category')}}",
        'edit_category': "{{_lang('app.edit_category')}}",
       
    };
</script>
@endsection