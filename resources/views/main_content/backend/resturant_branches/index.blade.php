@extends('layouts.backend')

@section('pageTitle')
{{ $resturant->title.' '._lang('app.branches') }}
@endsection

@section('js')

<script src="{{url('public/backend/js')}}/resturant_branches.js" type="text/javascript"></script>

@endsection
@section('content')
<input type="hidden" id="res" value="{{ $resturant->id }}">
{{ csrf_field() }}
<div class = "panel panel-default">
    <div class = "panel-heading">
        <a class="panel-title data-box" data-where="outTable"  data-id="0" data-level="0">{{ $resturant->title.' '._lang('app.branches') }}</a>

    </div>
    <div class = "panel-body">
        <!--Table Wrapper Start-->
        <a class = "btn btn-sm btn-info pull-left" style = "margin-bottom: 40px;"  href="{{ route('resturant_branches.create') }}?resturant={{ $resturant->id }}"> {{_lang('app.add_new')}} </a>

        <table class = "table table-striped table-bordered table-hover table-checkable order-column dataTable no-footer">
            <thead>
                <tr>
                    <th>{{_lang('app.city')}}</th>
                    <th>{{_lang('app.region')}}</th>
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
   
var new_config = {
    
}

</script>
@endsection