@extends('layouts.backend')

@section('pageTitle', 'Page Title')

@section('js')
<script src="{{url('public/backend/js')}}/groups.js" type="text/javascript"></script>
@endsection
@section('content')
<div class="modal fade" id="addEditGroups" role="dialog">
    <div class="modal-dialog modal-lg">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="addEditGroupsLabel"></h4>
            </div>

            <div class="modal-body">


                <form role="form"  id="addEditGroupsForm"  enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <input type="hidden" name="id" id="id" value="0">
                    <div class="form-body">
                        <div class="row">
                            <div class="form-group form-md-line-input col-md-6">
                                <input type="text" class="form-control" id="name" name="name" placeholder="{{ _lang('app.name') }}">
                                <span class="help-block"></span>
                            </div>
                            <div class="form-group form-md-line-input col-md-6">
                                <select class="form-control edited" id="active" name="active">
                                    <option  value="1">{{ _lang('app.active') }}</option>
                                    <option  value="0">{{ _lang('app.not_active') }}</option>

                                </select>

                            </div>
                        </div>
                        <div class="row">
                            @php
                            $count = 0;
                            @endphp

                            @foreach ($modules as $module) 
                            <div class="form-group form-md-checkboxes col-md-2">
                                <label>{{ _lang('app.'.$module->name) }}</label>
                                <div class="md-checkbox-inline">

                                    @foreach ($module->actions as $action) 
                                    <div class="md-checkbox has-success">
                                        @php
                                        $s_open_id = $module->name . '_' . $action 
                                        @endphp
                                        <input type="checkbox" id="{{ $s_open_id }}" name="group_options[{{ $module->name }}][{{ $action }}]" value="1" class="md-check">
                                        <label for="{{$s_open_id }}">
                                            <span class="inc"></span>
                                            <span class="check"></span>
                                            <span class="box"></span> {{ $action }} </label>
                                    </div>
                                    @endforeach

                                </div>
                            </div>
                            @php $count ++; @endphp


                            @if($count==6)
                            @php $count=0; @endphp
                            <div class="clearfix"></div>
                            @endif
                            @endforeach

                        </div>




                    </div>


                </form>

            </div>

            <div class="modal-footer">
                <span class="margin-right-10 loading hide"><i class="fa fa-spin fa-spinner"></i></span>
                <button type="button" class="btn btn-info submit-form"
                        >{{_lang("app.save")}}</button>
                <button type="button" class="btn btn-white"
                        data-dismiss="modal">{{ _lang("app.close") }}</button>
            </div>
        </div>
    </div>
</div>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">{{ _lang('app.groups')}}</h3>
    </div>
    <div class="panel-body">
        <!--Table Wrapper Start-->
        <a class="btn btn-sm btn-info pull-left" style="margin-bottom: 40px;" href="" onclick="Groups.add(); return false;">{{ _lang('app.add_new')}} </a>

        <table class="table table-striped table-bordered table-hover table-checkable order-column dataTable no-footer">
            <thead>
                <tr>
                    <th>{{ _lang('app.group_name')}}</th>
                    <th>{{ _lang('app.active')}}</th>
                    <th>{{ _lang('app.options')}}</th>
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
        add_group: "{{_lang('app.add_group')}}",
        edit_group: "{{_lang('app.edit_group')}}",
        messages: {
            name: {
                required: lang.required
            }
        }
    };
</script>
@endsection
