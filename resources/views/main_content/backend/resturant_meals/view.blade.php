@extends('layouts.backend')

@section('pageTitle')
{{$meal->title }}
@endsection

@section('js')
<script src="{{url('public/backend/js')}}/resturant_meals.js" type="text/javascript"></script>
@endsection
@section('content')
{{ csrf_field() }}
<div class="container">
    <div class="row">

        <div class="row">
            <div class="col-md-6">
                <!-- BEGIN SAMPLE TABLE PORTLET-->
                <div class="portlet box red">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-cogs"></i>{{ _lang('app.basic_info')}}
                        </div>
                        <div class="tools">
                            <a href="javascript:;" class="collapse" data-original-title="" title="">
                            </a>

                            <a href="javascript:;" class="remove" data-original-title="" title="">
                            </a>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-scrollable">
                            <table class="table table-hover">

                                <tbody>
                                    <tr>
                                        <td>{{ _lang('app.title')}}</td>
                                        <td>{{$meal->title}}</td>

                                    </tr>
                                    <tr>
                                        <td>{{ _lang('app.description')}}</td>
                                        <td>{{$meal->description}}</td>

                                    </tr>
                                    <tr>
                                        <td>{{ _lang('app.image')}}</td>
                                        <td>
                                            <a class="fancybox-button" data-rel="fancybox-button" href="{{url('public/uploads/meals/'.$meal->image)}}">
                                                <img alt="" style="width:120px;height: 120px;" src="{{url('public/uploads/meals/'.$meal->image)}}">
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">{{ _lang('app.sizes')}}</td>
                                    </tr>
                                    @foreach($meal_sizes as $size)
                                    <tr>
                                        <td>{{ $size->title }}</td>
                                        <td>{{ $size->price }}</td>


                                    </tr>
                                    @endforeach






                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- END SAMPLE TABLE PORTLET-->
            </div>
            <div class="col-md-6">
                <!-- BEGIN SAMPLE TABLE PORTLET-->
                <div class="portlet box red">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-cogs"></i>{{ _lang('app.toppings')}}
                        </div>
                        <div class="tools">
                            <a href="javascript:;" class="collapse" data-original-title="" title="">
                            </a>

                            <a href="javascript:;" class="remove" data-original-title="" title="">
                            </a>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-scrollable">
                            <table class="table table-hover">

                                <tbody>

                                    @foreach($meal_toppings as $topping)
                                    <tr>
                                        <td>{{ $topping->title }}</td>
                                        <td>{{ $topping->price }}</td>


                                    </tr>
                                    @endforeach




                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- END SAMPLE TABLE PORTLET-->
            </div>
        </div>
      

    </div>
</div>
<script>
var new_lang = {

};
var new_config = {

}

</script>
@endsection
