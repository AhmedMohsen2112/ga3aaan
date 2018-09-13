@extends('layouts.front')

@section('pageTitle','Ga3aaan')

@section('js')
<script src=" {{ url('public/front/scripts') }}/cart.js"></script>


@endsection



@section('content')

<div class="container">
    <div class="centerbolog">
        <h2 class="title">السلة</h2>
        @if(count($cart['items']) > 0)
        <div class="alert alert-success" style="display:{{Session('successMessage')?'block;':'none;'}}; " role="alert"><i class="fa fa-check" aria-hidden="true"></i> <span class="message">{{Session::get('successMessage')}}</span></div>
        <div class="alert alert-danger" style="display:{{Session('errorMessage')?'block;':'none;'}}; " role="alert"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> <span class="message">{{Session::get('errorMessage')}}</span></div>
        <div id="loader" style="z-index:9999;display:none;"></div>
        <div class="col-md-12" id="cart-content">
            <div class="orderbasc">
                <ul class="nameprofile basket">
                    <li class="basket-det">
                        @foreach($cart['items'] as $key=> $one)
                        <div class="row item-row">
                            <div class="top col-sm-10 size">
                                <div class="input-group" style="margin:0 0 0 15px;width: 120px;"> <span class="input-group-btn">
                                        <button type="button" class="btn btn-default btn-number btn-qty-plus" data-index="{{$key}}"   data-type="plus" data-field="qty[{{$key}}]"> <span class="glyphicon glyphicon-plus"></span> </button>
                                    </span>
                                    <input name="qty[{{$key}}]" class="form-control input-number" value="{{$one['quantity']}}" min="1" max="10" type="text">
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default btn-number btn-qty-minus" data-index="{{$key}}" data-type="minus" data-field="qty[{{$key}}]" disabled="disabled"> <span class="glyphicon glyphicon-minus"></span> </button>
                                    </span>
                                </div>
                                <p>{{$one[$title_slug]}}</p>

                            </div>
                            @foreach($one['toppings'] as $topping)
                            <div class="bottom col-sm-10">
                                <p style="font-size:14px; color:#656565;">{{$topping[$title_slug]}}</p>
                            </div>
                            @endforeach
                            <i class="fa fa-times remove-cart" data-index="{{$key}}" aria-hidden="true" id="hide" style="float:left; font-size:22px; cursor: pointer;padding-left:10px; color:#d5344a;"></i>

                        </div>
                        @endforeach
                    </li>
                </ul>
            </div>
            <!--orderbasc-->

            <div class="orderbasc"> <a href="{{_url('resturant/'.$cart['info']['resturant_slug'])}}" class="nam-tit tit-blog"><i class="fa fa-plus" aria-hidden="true"></i> أضف منتج</a>

                <ul class="nameprofile basket addtext">
                    <li>
                        <p class="sizcolo">السعر الاساسى</p>
                        <p class="deyleft" id="primary_price"> {{$cart['price_list']['primary_price'].' '.$currency_sign}}</p>
                    </li>
                    <li>
                        <p class="sizcolo">الاضافات</p>
                        <p class="deyleft" id="toppings_price"> {{$cart['price_list']['toppings_price'].' '.$currency_sign}}</p>
                    </li>
                    <li>
                        <p class="sizcolo">رسوم الخدمة</p>
                        <p class="deyleft" id="service_charge"> {{$cart['price_list']['service_charge'].' '.$currency_sign}}  </p>
                    </li>
                    <li>
                        <p class="sizcolo">قيمة الضريبة المضافة</p>

                        <p class="deyleft" id="vat_cost"> {{$cart['price_list']['vat_cost'].' '.$currency_sign}}  </p>
                    </li>
                    <li>
                        <p class="sizcolo">رسوم خدمة التوصيل</p>
                        <p class="deyleft" id="delivery_cost"> {{$cart['price_list']['delivery_cost'].' '.$currency_sign}}  </p>
                    </li>
                </ul>

            </div>
            <!--orderbasc-->

            <div class="orderbasc ">
                <div class="form-group">
                    <input type="text" id="coupon" class="form-control" placeholder="كود الخصم (أختيارى)">
                    <span class="help-block"></span>
                </div>
                <style>
                    .old-price{
                        text-decoration: line-through;
                    }
                </style>
                <div class="total">
                    <p>المجموع</p>
                    <p id="total_price" class="{{isset($cart['price_list']['coupon_cost'])?'old-price':''}}">{{$cart['price_list']['total_price'].' '.$currency_sign}} </p>
                    <p id="net_price" style="display: {{isset($cart['price_list']['net_price'])?'block':'none'}};">{{isset($cart['price_list']['net_price'])?$cart['price_list']['net_price']:''}} </p>
                </div>

            </div>
            <!--orderbasc--> 
            <button class="botoom btn-step-one" type="submit" onclick="Cart.goToNext(this);return false;"> استكمال الطلب   الان</button>
        </div>


        @else
        <div class="col-md-12">
            <div class="orderbasc">
                <i class="fa fa-shopping-cart" style="font-size:80px; display:block; text-align: center; margin:20px auto; color: #d5344a;" aria-hidden="true"></i>
                <h1 style="text-align:center; color:#000; font-size:20px; margin:30px 0;" >السلة فارغة</h1>
            </div>

        </div>
        @endif

    </div>
    <!--centerbolog--> 

</div>





@endsection