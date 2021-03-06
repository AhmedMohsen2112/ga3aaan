@extends('layouts.backend')

@section('pageTitle')
{{ _lang('app.edit') }}
@endsection
@section('breadcrumb')
<li><a href="{{url('admin')}}">{{_lang('app.dashboard')}}</a> <i class="fa fa-circle"></i></li>
<li><a href="{{url('admin/resturantes')}}">{{_lang('app.resturantes')}}</a> <i class="fa fa-circle"></i></li>
<li><a href="{{url('admin/menu_sections?resturant='.$meal->resturant_id)}}">{{_lang('app.menu_sections')}}</a> <i class="fa fa-circle"></i></li>
<li><a href="{{url('admin/meals?menu_section='.$meal->menu_section_id)}}">{{_lang('app.meals')}}</a> <i class="fa fa-circle"></i></li>
<li><a href="{{url('admin/meal_sizes?meal='.$meal->id)}}">{{_lang('app.meal_sizes')}}</a> <i class="fa fa-circle"></i></li>
<li><span> {{_lang('app.edit')}}</span></li>

@endsection
@section('js')
<script src="{{url('public/backend/js')}}/meal_sizes.js" type="text/javascript"></script>
@endsection
@section('content')

<form role="form"  id="addEditMealSizesForm" enctype="multipart/form-data">
    {{ csrf_field() }}

    <div class="panel panel-default" id="addEditMealSizes">
        <div class="panel-heading">
            <h3 class="panel-title">{{_lang('app.Meal_info') }}</h3>
        </div>
        <div class="panel-body">


            <div class="form-body">
                <input type="hidden" name="id" id="id" value="{{$meal_size->id}}">


                <div class="form-group form-md-line-input col-md-3">
                    <select class="form-control edited" id="size" name="size">
                        <option value="">{{_lang('app.choose')}}</option>
                        @foreach ($sizes as $size)
                        <option {{ $meal_size->size_id == $size->id ? 'selected':'' }} value="{{ $size->id }}">{{ $size->title }}</option>
                        @endforeach 
                    </select>
                    <label>{{ _lang('app.sizes') }}</label>
                    <span class="help-block"></span>
                </div>

                <div class="form-group form-md-line-input col-md-3">
                    <input type="number" class="form-control" name="price" value="{{$meal_size->price}}">
                    <label for="price">{{_lang('app.price') }}</label>
                    <span class="help-block"></span>
                </div>

                <div class="form-group form-md-line-input col-md-3">
                    <input type="number" class="form-control" id="this_order" name="this_order" value="{{$meal_size->this_order}}">
                    <label for="this_order">{{_lang('app.this_order') }}</label>
                    <span class="help-block"></span>
                </div>
                <div class="form-group form-md-line-input col-md-3">
                    <select class="form-control edited" id="active" name="active">
                        <option  {{ $meal_size->active == 1 ? 'selected':'' }} value="1">{{ _lang('app.active') }}</option>
                        <option  {{ $meal_size->active == 0 ? 'selected':'' }} value="0">{{ _lang('app.not_active') }}</option>
                    </select>
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
                                                    <input type="checkbox" id="{{$choice_id}}" {{$meal_size_choices->has($one->id)?'checked':''}} name="selected[]" value="{{$one->id}}" class="md-check">
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
                                                    <input type="text" class="form-control" data-choice="{{$one->id}}"  name="choices[{{$one->id}}][min]" value="{{isset($meal_size_choices[$one->id])?$meal_size_choices[$one->id]->min:''}}">
                                                    <span class="help-block"></span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <input type="text" class="form-control" data-choice="{{$one->id}}"  name="choices[{{$one->id}}][max]" value="{{isset($meal_size_choices[$one->id])?$meal_size_choices[$one->id]->max:''}}">
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

                                                <input type="checkbox" id="{{ $_id }}" {{isset($meal_size_choices[$one->id])&&in_array($one_sub->id,$meal_size_choices[$one->id]->sub->toArray())?'checked':''}} data-choice="{{$one->id}}" name="sub_choices[{{$one->id}}][]" value="{{ $one_sub->id }}" class="md-check">
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
    meal: "{{$meal->id}}",
    choices: '{!!$choices!!}',
}
</script>
@endsection