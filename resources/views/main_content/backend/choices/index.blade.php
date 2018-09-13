@extends('layouts.backend')

@section('pageTitle', _lang('app.choices'))
@section('breadcrumb')
<li><a href="{{url('admin')}}">{{_lang('app.dashboard')}}</a> <i class="fa fa-circle"></i></li>
<li><a href="{{url('admin/resturantes')}}">{{_lang('app.resturantes')}}</a> <i class="fa fa-circle"></i></li>
<li><span> {{_lang('app.choices')}}</span></li>

@endsection
@section('js')
<script src="{{url('public/backend/js')}}/choices.js" type="text/javascript"></script>
@endsection
@section('content')
<div class="modal fade" id="addEditChoices" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="addEditChoicesLabel"></h4>
            </div>

            <div class="modal-body">


                <form role="form"  id="addEditChoicesForm"  enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <input type="hidden" name="id" id="id" value="0">
                    <div class="form-body">
                        <div class="form-group form-md-line-input">
                            <input type="text" class="form-control" id="title_ar" name="title_ar" placeholder="{{_lang('app.title_ar')}}">
                            <label for="title_ar">{{_lang('app.title_ar')}}</label>
                            <span class="help-block"></span>
                        </div>
                        <div class="form-group form-md-line-input">
                            <input type="text" class="form-control" id="title_en" name="title_en" placeholder="{{_lang('app.title_en')}}">
                            <label for="title_en">{{_lang('app.title_en')}}</label>
                            <span class="help-block"></span>
                        </div>

                         

                    </div>


                </form>

            </div>

            <div class = "modal-footer">
                <span class = "margin-right-10 loading hide"><i class = "fa fa-spin fa-spinner"></i></span>
                <button type = "button" class = "btn btn-info submit-form"
                        >{{_lang("app.save")}}</button>
                <button type = "button" class = "btn btn-white"
                        data-dismiss = "modal">{{_lang("app.close")}}</button>
            </div>
        </div>
    </div>
</div>
<div class = "panel panel-default">

    <div class = "panel-body">
        <!--Table Wrapper Start-->
        <a class = "btn btn-sm btn-info pull-left" style = "margin-bottom: 40px;"  onclick = "Choices.add();return false;" > {{_lang('app.add_new')}} </a>

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
    var new_config= {
        resturant:"{{$resturant->id}}"
       
    };
</script>
@endsection