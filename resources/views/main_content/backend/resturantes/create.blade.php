@extends('layouts.backend')

@section('pageTitle',_lang('app.add'))
@section('breadcrumb')
<li><a href="{{url('admin')}}">{{_lang('app.dashboard')}}</a> <i class="fa fa-circle"></i></li>
<li><a href="{{url('admin/resturantes')}}">{{_lang('app.resturantes')}}</a> <i class="fa fa-circle"></i></li>
<li><span> {{_lang('app.add')}}</span></li>

@endsection

@section('js')
<script src="{{url('public/backend/js')}}/resturantes.js" type="text/javascript"></script>
@endsection
@section('content')
<form role="form"  id="addEditResturantesForm" enctype="multipart/form-data">
    {{ csrf_field() }}

    <div class="panel panel-default" id="addEditResturantes">
        <div class="panel-heading">
            <h3 class="panel-title">{{_lang('app.Resturant_info') }}</h3>
        </div>
        <div class="panel-body">


            <div class="form-body">
                <input type="hidden" name="id" id="id" value="0">
                <div class="form-group col-md-6 col-md-offset-4">
                    <label class="control-label">{{_lang('app.logo')}}</label>

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
                    <input type="number" class="form-control" id="delivery_time" name="delivery_time" value="">
                    <label for="delivery_time">{{_lang('app.delivery_time') }}</label>
                    <span class="help-block"></span>
                </div>
                <div class="form-group form-md-line-input col-md-6">
                    <input type="number" class="form-control" id="minimum_charge" name="minimum_charge" value="">
                    <label for="minimum_charge">{{_lang('app.minimum_charge') }}</label>
                    <span class="help-block"></span>
                </div>
                <div class="form-group form-md-line-input col-md-6">
                    <input type="number" class="form-control" id="service_charge" name="service_charge" value="">
                    <label for="service_charge">{{_lang('app.service_charge') }}</label>
                    <span class="help-block"></span>
                </div>

                <div class="form-group form-md-line-input col-md-6">
                    <input type="number" class="form-control" id="vat" name="vat" value="">
                    <label for="vat">{{_lang('app.vat') }}</label>
                    <span class="help-block"></span>
                </div>

                <div class="form-group form-md-line-input col-md-6">
                    <input type="number" class="form-control" id="commission" name="commission" value="">
                    <label for="commission">{{_lang('app.commission') }}</label>
                    <span class="help-block"></span>
                </div>



                <div class="form-group form-md-checkboxes col-md-6">

                    <label>{{ _lang('app.payment_methods') }}</label>
                    <div class="md-checkbox-inline">

                        @foreach ($payment_methods as $method) 
                        @php
                        $s_open_id = $method->title . '_' . $method->id;
                        @endphp
                        <div class="md-checkbox has-success">

                            <input type="checkbox" id="{{ $s_open_id }}" name="payment_methods[]" value="{{ $method->id }}" class="md-check">
                            <label for="{{ $s_open_id }}">
                                <span class="inc"></span>
                                <span class="check"></span>
                                <span class="box"></span>{{ $method->title }} </label>
                        </div>
                        @endforeach

                    </div>
                    <div class="clearfix"></div>
                    <span class="help-block"></span>

                </div>


                <div class="clearfix"></div>
                <div class="form-group form-md-line-input col-md-6">
                    <select class="form-control edited" id="category" name="category">
                        @foreach ($categories as $category)
                        <option  value="{{ $category->id }}">{{ $category->title }}</option>
                        @endforeach 
                    </select>
                    <label>{{ _lang('app.category') }}</label>
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
                    <select class="form-control edited" id="options" name="options">
                        <option  value="">{{ _lang('app.choose') }}</option>
                        <option  value="1">{{ _lang('app.new') }}</option>
                        <option  value="2">{{ _lang('app.ad') }}</option>
                    </select>
                    <span class="help-block"></span>
                </div> 

                <div class="form-group form-md-line-input col-md-3">
                    <select class="form-control edited" id="is_famous" name="is_famous">
                        <option  value="0">{{ _lang('app.no') }}</option>
                        <option  value="1">{{ _lang('app.yes') }}</option>
                    </select>
                     <label>{{ _lang('app.famous') }}</label>
                    <span class="help-block"></span>
                </div> 





            </div>




            <!--Table Wrapper Finish-->
        </div>

    </div>
    <div class="panel panel-default">

        <div class="panel-heading">
            <h3 class="panel-title">{{_lang('app.cuisines') }}</h3>
        </div>
        <div class="panel-body">

            <div class="form-body">



                <div class="form-group form-md-checkboxes col-md-12">
                    <span class="help-block"></span>
                    <div class="md-checkbox-inline">

                        @foreach ($cuisines as $cuisine) 
                        @php
                        $s_open_id = $cuisine->title . '_' . $cuisine->id;
                        @endphp
                        <div class="md-checkbox has-success">

                            <input type="checkbox" id="{{ $s_open_id }}" name="cuisines[]" value="{{ $cuisine->id }}" class="md-check">
                            <label for="{{ $s_open_id }}">
                                <span class="inc"></span>
                                <span class="check"></span>
                                <span class="box"></span>{{ $cuisine->title }} </label>
                        </div>
                        @endforeach

                    </div>

                </div>

            </div>

            <!--Table Wrapper Finish-->
        </div>
    </div>

    <div class="panel panel-default">

        <div class="panel-heading">
            <h3 class="panel-title">{{_lang('app.working_hours') }}</h3>
        </div>
        <div class="panel-body">

            <div id="working_hours">
                @foreach($week_days as $arr)
                <div class="row">
                @foreach($arr as $day)
                    <div class="col-md-6">
                        <h4>{{ _lang('app.'.$day['title']) }}</h4>
                        <div class="form-group form-md-line-input col-md-6">  
                            <label class="control-label">{{ _lang('app.from') }}</label>
                            <input type="text" class="form-control timepicker" name="working_hours[{{$day['name']}}][from]" placeholder="from">
                            <span class="help-block"></span>
                        </div>

                        <div class="form-group form-md-line-input col-md-6">
                            <label class="control-label">{{ _lang('app.to') }}</label>
                            <input type="text" class="form-control timepicker" name="working_hours[{{$day['name']}}][to]"  placeholder="to">
                            <span class="help-block"></span>
                        </div>

                    </div>
                @endforeach
           
                
                </div>
                @endforeach

      


            </div>
        </div>
        
    </div>


    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">{{_lang('app.resturant_admin_info') }}</h3>
        </div>
        <div class="panel-body">

            <div class="form-body">

                <div class="form-group form-md-line-input col-md-6">
                    <input type="text" class="form-control" id="username" name="username" value="">
                    <label for="username">{{_lang('app.username') }}</label>
                    <span class="help-block"></span>
                </div>

                <div class="form-group form-md-line-input col-md-6">
                    <input type="password" class="form-control" id="password" name="password" value="">
                    <label for="password">{{_lang('app.password') }}</label>
                    <span class="help-block"></span>
                </div>

                <div class="form-group form-md-line-input col-md-6">
                    <input type="number" class="form-control" id="phone" name="phone" value="">
                    <label for="phone">{{_lang('app.phone') }}</label>
                    <span class="help-block"></span>
                </div>

                <div class="form-group form-md-line-input col-md-6">
                    <input type="email" class="form-control" id="email" name="email" value="">
                    <label for="email">{{_lang('app.email') }}</label>
                    <span class="help-block"></span>
                </div>
                <div class="form-group form-md-line-input col-md-3">
                    <select class="form-control edited" id="user_active" name="user_active">
                        <option  value="1">{{ _lang('app.active') }}</option>
                        <option  value="0">{{ _lang('app.not_active') }}</option>
                    </select>
                    <span class="help-block"></span>
                </div> 

                <div class="clearfix"></div>
            </div>

            <!--Table Wrapper Finish-->
        </div>
         <div class="panel-footer text-center">
                <button type="button" class="btn btn-info submit-form"
                        >{{_lang('app.save') }}</button>
            </div>

    </div>
   


</form>
@endsection