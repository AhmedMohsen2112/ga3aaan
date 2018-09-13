@extends('layouts.user_profile')
@section('pageTitle',$page_title)

@section('title')
{{ $page_title }}
@endsection

@section('js')
<script src=" {{ url('public/front/scripts') }}/orders.js"></script>
@endsection

@section('content')

<div class="alert alert-success" style="display:{{Session('successMessage')?'block;':'none;'}}; " role="alert"><i class="fa fa-check" aria-hidden="true"></i> <span class="message">{{Session::get('successMessage')}}</span></div>
<div class="alert alert-danger" style="display:{{Session('errorMessage')?'block;':'none;'}}; " role="alert"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> <span class="message">{{Session::get('errorMessage')}}</span></div>
@if($minutes<$order_minutes_limit && $order->status==1)
<div class="centernamber" id="countdown">
    <div class="naminner countdown"></div>
    <!--naminner-->

    <p class="textinner">الوقت المتبقى للتعديل على الطلب</p>
    <a href="{{_url('user-orders/'.encrypt($order->order_id).'/edit')}}" class="botoom addadrs basket ">تعديل</a>
    <a href="#" data-id="{{encrypt($order->order_id)}}" class="botoom addadrs basket" onclick="Orders.delete(this);return false;">الغاء</a> 
</div>
@endif
<div class="orderbasc">
    <ul class="nameprofile basket">
        @foreach ($order_meals as $order_meal)

        <li>
            <p class="rad">{{ $order_meal->quantity }} X</p>
            <p>{{ $order_meal->size_title }} -  {{ $order_meal->meal_title }}</p>
            
            <p class="deyleft"> {{ $order_meal->cost_of_meal+$order_meal->sub_choices_price }} {{ $currency_sign }} </p>
            <div class="clearfix"></div>
            @foreach($order_meal->sub_choices as $choice)
            <p>{{$choice->$title_slug}}</p>
            <div class="clearfix"></div>
            @endforeach
        </li>

        @endforeach


    </ul>
</div>
<!--orderbasc-->

<ul class="nameprofile basket addtext nemerg">
    <li>
        <p class="sizcolo">{{ _lang('app.primary_price') }}</p>
        <p class="deyleft"> {{ $order->primary_price }} {{ $currency_sign }} </p>
    </li>

    <li>
        <p class="sizcolo">{{ _lang('app.service_charge') }}</p>
        <p class="deyleft">  {{ $order->service_charge }} % </p>
    </li>
    <li>
        <p class="sizcolo">{{ _lang('app.vat') }}</p>
        <p class="deyleft">  {{ $order->vat }} % </p>
    </li>
    <li>
        <p class="sizcolo">{{ _lang('app.delivery_cost') }}</p>
        <p class="deyleft"> {{ $order->delivery_cost }} {{ $currency_sign }} </p>
    </li>
</ul>

<!--nameprofile-->

<ul class="nameprofile basket">
    @if ($order->coupon)
    <li>
        <p>{{ _lang('app.coupon') }}</p>
        <br>
        <p>{{ $order->coupon }}</p>
    </li>
    @endif

    <li>
        <p>{{ _lang('app.address') }}</p>
        <br>
        <p>{{ $order->city }},{{ $order->region }} ,{{ $order->sub_region }},  {{ $order->building_number }} {{ $order->street }}</p>
    </li>
</ul>
<div class="total">
    <p>{{ _lang('app.total') }}</p>
    <p>{{ $order->net_cost }} {{ $currency_sign }}</p>
</div>
<!--total-->

<div class="steps"> 

    <a href="" class="righton {{ $order->status == 1 || $order->status == 2 || $order->status == 3 ? 'active' : '' }}"> 
        <img src="{{ url('public/front') }}/images/hand.png">
        <i class="fa fa-circle" aria-hidden="true"></i>
        <p>جارى تجهيز الطلب</p>
    </a> <!--righton--> 

    <a href="" class="righton centerblok {{  $order->status == 2 || $order->status == 3 ? 'active' : '' }}"> <img src="{{ url('public/front') }}/images/order.png"> <i class="fa fa-circle" aria-hidden="true"></i>
        <p>جارى توصيل الطلب</p>
    </a> <!--righton--> 

    <a href="" class="righton leftbox {{ $order->status == 3 ? 'active' : '' }}"> <img src="{{ url('public/front') }}/images/den.png"> <i class="fa fa-circle" aria-hidden="true"></i>
        <p>تم توصيل الطلب</p>
    </a> <!--righton--> 

</div>
@if ($order->status == 3)
<div class="row">

    @if ($order->is_rated == 0)
    <div class="col-md-6 col-xs-6">
        <a href="#myModa2" data-toggle="modal" class="botoom">{{ _lang('app.rate_order') }}</a>
    </div>
    @endif
    <div class="col-md-6 col-xs-6">
        <a href="{{ route('resend_order') }}?resturant_id={{ $order->resturant_id }}&order_id={{ $order->order_id }}" data-order="" data-resturant="" class="botoom">{{ _lang('app.resend_order') }}</a>
    </div>


    @endif
</div>

<div id="myModa2" class="modal fade in" role="dialog" aria-hidden="false">
    <div class="modal-dialog"> 

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn btn-default cols" data-dismiss="modal">×</button>
                <h4 class="modal-title titlpop">{{ _lang('app.rate_order') }}</h4>
            </div>
            <div class="modal-body nonepad">
                <form action="{{ route('rate_order') }}" method="POST" role="form" class="form-search">
                    {{ csrf_field() }}
                    <div class="row"> 
                        <input type="hidden" name="order_id" value="{{ Crypt::encrypt($order->order_id) }}">
                        <input class="rating-input" name="rate" type="text" title="" required />
                        <div class="col-sm-12 inputbox">
                            <textarea class="form-control textarea" name="opinion" placeholder="{{ _lang('app.opinion') }}"></textarea>
                        </div>
                        <div class="col-sm-12 inputbox merges">
                            <button type="submit" class="botoom">{{ _lang('app.save') }}</button>
                        </div>
                    </div>
                    <!--row-->

                </form>
            </div>
        </div>
    </div>
</div>

<script>
    var new_config = {
        minutes: '{{$minutes}}',
        order_minutes_limit: '{{$order_minutes_limit }}',
    }
</script>
@endsection