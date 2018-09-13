@extends('layouts.backend')

@section('pageTitle')
{{ _lang('app.add') }}
@endsection
@section('breadcrumb')
<li><a href="{{url('admin')}}">{{_lang('app.dashboard')}}</a> <i class="fa fa-circle"></i></li>
<li><a href="{{url('admin/resturantes')}}">{{_lang('app.resturantes')}}</a> <i class="fa fa-circle"></i></li>
<li><a href="{{url('admin/menu_sections?resturant='.$menu_section->resturant_id)}}">{{_lang('app.menu_sections')}}</a> <i class="fa fa-circle"></i></li>
<li><a href="{{url('admin/meals?menu_section='.$menu_section->id)}}">{{_lang('app.meals')}}</a> <i class="fa fa-circle"></i></li>
<li><span> {{_lang('app.add')}}</span></li>

@endsection
@section('js')
<script src="{{url('public/backend/js')}}/meals.js" type="text/javascript"></script>
@endsection
@section('content')

<form role="form"  id="addEditMealsForm" enctype="multipart/form-data">
    {{ csrf_field() }}

    <div class="panel panel-default" id="addEditMeals">
        <div class="panel-heading">
            <h3 class="panel-title">{{_lang('app.Meal_info') }}</h3>
        </div>
        <div class="panel-body">


            <div class="form-body">
                <input type="hidden" name="id" id="id" value="0">

                <div class="form-group col-md-6 col-md-offset-4">
                    <label class="control-label">{{_lang('app.image')}}</label>

                    <div class="image_box">
                        <img src="{{url('no-image.png')}}" width="100" height="80" class="image" />
                    </div>
                    <input type="file" name="image" id="image" style="display:none;">     
                    <span class="help-block"></span>             
                </div>

                <div class="form-group form-md-line-input col-md-6">
                    <input type="text" class="form-control" id="title_ar" name="title_ar" value="">
                    <label for="title_ar">{{_lang('app.title_ar') }}</label>
                    <span class="help-block"></span>
                </div>
                <div class="form-group form-md-line-input col-md-6">
                    <input type="text" class="form-control" id="title_en" name="title_en" value="">
                    <label for="title_en">{{_lang('app.title_en') }}</label>
                    <span class="help-block"></span>
                </div>
                <div class="form-group form-md-line-input col-md-6">
                    <textarea rows="5" class="form-control" id="description_ar" name="description_ar" value=""></textarea>
                    <label for="description_ar">{{_lang('app.description_ar') }}</label>
                    <span class="help-block"></span>
                </div>
                <div class="form-group form-md-line-input col-md-6">
                    <textarea rows="5" class="form-control" id="description_en" name="description_en" value=""></textarea>
                    <label for="description_en">{{_lang('app.description_en') }}</label>
                    <span class="help-block"></span>
                </div>


                <div class="form-group form-md-line-input col-md-3" id="price">
                    <input type="number" class="form-control" name="price" value="">
                    <label for="price">{{_lang('app.price') }}</label>
                    <span class="help-block"></span>
                </div>

                <div class="form-group form-md-line-input col-md-3">
                    <input type="number" class="form-control" id="this_order" name="this_order" value="">
                    <label for="this_order">{{_lang('app.this_order') }}</label>
                    <span class="help-block"></span>
                </div>



                <div class="form-group form-md-line-input col-md-3">
                    <select class="form-control edited" id="active" name="active">
                        <option  value="1">{{ _lang('app.active') }}</option>
                        <option  value="0">{{ _lang('app.not_active') }}</option>
                    </select>
                    <span class="help-block"></span>
                </div> 
                <div class="form-group form-md-line-input col-md-3">
                    <select class="form-control" id="has_sizes" name="has_sizes">
                        <option  value="0">{{ _lang('app.no') }}</option>
                        <option  value="1">{{ _lang('app.yes') }}</option>
                    </select>
                    <label for="has_sizes">{{_lang('app.has_sizes') }}</label>
                    <span class="help-block"></span>
                </div>
                <table class = "table table-bordered text-center" id="table-choices">
                    <thead>
                        <tr>
                            <td colspan="2">{{_lang('app.choices')}}</td>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($choices as $one)

                        @php $choice_id='ch-'.$one->id @endphp
                        <tr>
                            <td width="25%">
                                <table class = "table table-bordered">
                                    <thead>
                                        <tr> 
                                            <td colspan="2">
                                                <div class="md-checkbox md-checkbox-inline has-success">
                                                    <input type="checkbox" id="{{$choice_id}}" name="selected[]" value="{{$one->id}}" class="md-check">
                                                    <label for="{{$choice_id}}">
                                                        <span></span>
                                                        <span class="check"></span>
                                                        <span class="box"></span>
                                                        {{$one->title}} 
                                                    </label>
                                                </div>
                                            </td> 
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>{{_lang('app.min')}}</td>
                                            <td>{{_lang('app.max')}}</td>

                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="form-group">
                                                    <input type="text" class="form-control" data-choice="{{$one->id}}"  name="choices[{{$one->id}}][min]">
                                                    <span class="help-block"></span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <input type="text" class="form-control" data-choice="{{$one->id}}"  name="choices[{{$one->id}}][max]">
                                                    <span class="help-block"></span>
                                                </div>
                                            </td>

                                        </tr>


                                    </tbody>
                                </table>
                            </td>
                            <td>
                                <div class="form-group" id="sch-box-{{$one->id}}">
                                    <div class="form-md-checkboxes">
                                        <span class="help-block"></span>

                                        <div class="md-checkbox-inline">
                                            @php $count=0; @endphp
                                            @foreach ($one->sub as $one_sub) 
                                            @php
                                            $_id = 'm' . $one_sub->id;
                                            @endphp
                                            <div class="md-checkbox col-md-2">

                                                <input type="checkbox" id="{{ $_id }}" data-choice="{{$one->id}}" name="sub_choices[{{$one->id}}][]" value="{{ $one_sub->id }}" class="md-check">
                                                <label for="{{ $_id }}">
                                                    <span class="inc"></span>
                                                    <span class="check"></span>
                                                    <span class="box"></span>{{ $one_sub->title }} </label>
                                            </div>


                                            @php $count++; @endphp
                                            @endforeach
                                        </div>


                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach

                    </tbody>
                </table>


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
    menu_section: "{{$menu_section->id}}",
    choices: '{!!$choices!!}',
}
</script>
@endsection