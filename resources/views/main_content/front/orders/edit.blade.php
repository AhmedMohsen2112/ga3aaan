@extends('layouts.user_profile')

@section('pageTitle','Ga3aaan')
@section('title')
{{ _lang('app.edit_order') }}
@endsection

@section('js')
<script src=" {{ url('public/front/scripts') }}/orders.js"></script>


@endsection



@section('content')


<div class="alert alert-success" style="display:{{Session('successMessage')?'block;':'none;'}}; " role="alert"><i class="fa fa-check" aria-hidden="true"></i> <span class="message">{{Session::get('successMessage')}}</span></div>
<div class="alert alert-danger" style="display:{{Session('errorMessage')?'block;':'none;'}}; " role="alert"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> <span class="message">{{Session::get('errorMessage')}}</span></div>
<div id="loader" style="z-index:9999;display:none;"></div>
<div class="col-md-12" id="cart-content">
    <div class="orderbasc">
        <ul class="nameprofile basket">
            <li class="basket-det">
                @foreach($order_meals as $one)
                <div class="row item-row">
                    <div class="top col-sm-10 size">
                        <div class="input-group" style="margin:0 0 0 15px;width: 120px;"> <span class="input-group-btn">
                                <button type="button" class="btn btn-default btn-number btn-qty-plus"   data-type="plus" data-id="{{$one->id}}" data-field="qty[{{$one->id}}]"> <span class="glyphicon glyphicon-plus"></span> </button>
                            </span>
                            <input name="qty[{{$one->id}}]" class="form-control input-number" value="{{$one->quantity}}" min="1" max="10" type="text">
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default btn-number btn-qty-minus" data-type="minus" data-id="{{$one->id}}" data-field="qty[{{$one->id}}]" disabled="disabled"> <span class="glyphicon glyphicon-minus"></span> </button>
                            </span>
                        </div>
                        <p>{{$one->meal_title}}</p>

                    </div>
                    @foreach($one->toppings as $topping)
                    <div class="bottom col-sm-10">
                        <p style="font-size:14px; color:#656565;">{{$topping->title}}</p>
                    </div>
                    @endforeach
                    <i class="fa fa-times remove-cart" aria-hidden="true" data-id="{{$one->id}}" id="hide" style="float:left; font-size:22px; cursor: pointer;padding-left:10px; color:#d5344a;"></i>

                </div>
                @endforeach
            </li>
        </ul>
    </div>
    <!--orderbasc-->

    <div class="orderbasc">

        <ul class="nameprofile basket addtext">
            <li>
                <p class="sizcolo">السعر الاساسى</p>
                <p class="deyleft" id="primary_price"> {{$order->primary_price.' '.$currency_sign}}</p>
            </li>
            <li>
                <p class="sizcolo">رسوم الخدمة</p>
                <p class="deyleft"> {{$order->service_charge}}  </p>
            </li>
            <li>
                <p class="sizcolo">قيمة الضريبة المضافة</p>

                <p class="deyleft"> {{$order->vat}}  </p>
            </li>
            <li>
                <p class="sizcolo">رسوم خدمة التوصيل</p>
                <p class="deyleft"> {{$order->delivery_cost.' '.$currency_sign}}  </p>
            </li>
        </ul>

    </div>
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
        <p>المجموع</p>
        <p id="total_price">{{ $order->total_cost }} {{ $currency_sign }}</p>
    </div>
    <!--total-->

</div>
<div id="requestMessageModal" class="modal fade in" role="dialog" aria-hidden="false" >
        <div class="modal-dialog"> 

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn btn-default cols" data-dismiss="modal">×</button>
                    <h4 class="modal-title titlpop">رسالة</h4>
                </div>
                <div class="modal-body">
                    <p class="text-center bothenk">تم مسح طلبك</p>
                </div>
            </div>
        </div>
    </div>
    <!--myModa2--> 


<script>
var new_config = {

}
</script>



@endsection