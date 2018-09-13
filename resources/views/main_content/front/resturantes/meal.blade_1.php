@extends('layouts.front')

@section('pageTitle',$page_title)

@section('js')
<script src=" {{ url('public/front/scripts') }}/main.js"></script>
<script src=" {{ url('public/front/scripts') }}/cart.js"></script>
@endsection

@section('cart')
   <div class="basket-hidden"><a href="{{_url('cart?step=1')}}" style="display: block;"> <i class="fa fa-shopping-cart"></i> </a> </div>
@endsection



@section('content')

<div class="container minhitcon">
    <div class="col-sm-12">
         
        <div class="alert alert-success" id = "alert_success" style="display:{{Session('successMessage')?'block;':'none;'}}; " role="alert">
            <i class="fa fa-check" aria-hidden="true"></i> <span class="message">{{Session::get('successMessage')}}</span>
        </div>
        <div class="alert alert-danger " style="display:{{Session('errorMessage')?'block;':'none;'}}; " role="alert">
            <i class="fa fa-exclamation-triangle" aria-hidden="true"></i> <span class="message">{{Session::get('errorMessage')}}</span>
        </div>


         <div class="col-sm-5">
            <div class="imginner"><img src="{{$meal->image}}"></div>
        </div>


        <div class="col-sm-7">
            <div class="photos">
                <h2 class="title-serch">{{ _lang('app.description') }}</h2>
                <p class="textcont">{{$meal->description}}</p>
            </div>
            <!--photos-->

            <div class="size">
                @if($meal->sizes->count() > 0)
                <h2 class="title-serch">{{ _lang('app.sizes') }}</h2>
                <form action="#">
                    <ul class="nameprofile">
                        @foreach($meal->sizes as $key=> $size)
                        @php $qtyName='sqty['.$key.']' @endphp
                        @php $qtyId='sqty'.$size->id; @endphp
                        <li>
                            <strong>{{$size->size}}</strong>
                            @if($size->discount_price > 0)
                            <p> {{$size->discount_price.' '.$currency_sign}}  </p>
                            <p style="text-decoration: line-through;"> {{$size->price.' '.$currency_sign}}  </p>
                            @else
                            <p> {{$size->discount_price.' '.$currency_sign}}  </p>
                            @endif
                            <div class="input-group"> 
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default btn-number" data-type="plus" data-field="{{$qtyName}}"> <span class="glyphicon glyphicon-plus"></span> </button>
                                </span>
                                <input type="text" name="{{$qtyName}}" class="form-control input-number" value="1" min="1" max="10">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default btn-number" disabled="disabled" data-type="minus" data-field="{{$qtyName}}"> <span class="glyphicon glyphicon-minus"></span> </button>
                                </span> 
                            </div>
                            <!--input-group-->

                            <div class="radio radio-info radio-inline">
                                <input id="{{$qtyId}}" {{$key==0?'checked':''}} name="size" value="{{$size->id}}" type="radio">
                                <label for="{{$qtyId}}">
                                </label>
                            </div>
                        </li>
                        @endforeach

                    </ul>
                </form>
                @else
                <div class="input-group"> 
                    <span class="input-group-btn">
                        <button type="button" class="btn btn-default btn-number" data-type="plus" data-field="mqty"> <span class="glyphicon glyphicon-plus"></span> </button>
                    </span>
                    <input type="text" name="mqty" class="form-control input-number" value="1" min="1" max="10">
                    <span class="input-group-btn">
                        <button type="button" class="btn btn-default btn-number" disabled="disabled" data-type="minus" data-field="mqty"> <span class="glyphicon glyphicon-minus"></span> </button>
                    </span> 
                </div>
                @endif
                <div class="sboto">
                    <button type="button" onclick="Cart.addToCart(this);return false;" data-config="{{json_encode($meal->config)}}" class="botoom addadrs basket"><i class="fa fa-shopping-basket" aria-hidden="true"></i> {{ _lang('app.add_to_cart') }}</button> 
                </div>

                <!--bolink-->


                <!--bolink-->

                <div id="addToCartModal2" class="modal fade" role="dialog">
                    <div class="modal-dialog"> 

                        <!-- Modal content-->
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title titlpop">{{ _lang('app.toppings') }}</h4>
                            </div>
                            <form method="post" id="addToCartForm">
                                {{ csrf_field() }}
                                <div class="modal-body">
                                    <ul class="nameprofile">
                                        @foreach($meal->toppings as $key=> $topping)
                                        @php $qtyName='tqty['.$topping->id.']' @endphp
                                        @php $tId='t'.$topping->id; @endphp
                                        <li>
                                            <strong>{{$topping->topping}}</strong>
                                            @if($topping->price > 0)
                                            <p>{{$topping->price.' '.$currency_sign}}</p>
                                            @endif
                                            <div class="input-group"> 
                                                <span class="input-group-btn">
                                                    <button type="button" class="btn btn-default btn-number" data-type="plus" data-field="{{$qtyName}}"> <span class="glyphicon glyphicon-plus"></span> </button>
                                                </span>
                                                <input type="text" name="{{$qtyName}}" class="form-control input-number" value="1" min="1" max="10">
                                                <span class="input-group-btn">
                                                    <button type="button" class="btn btn-default btn-number" disabled="disabled" data-type="minus" data-field="{{$qtyName}}"> <span class="glyphicon glyphicon-minus"></span> </button>
                                                </span> 
                                            </div>
                                            <!--input-group-->

                                            <div class="checkbox checkbox-danger">
                                                <input id="{{$tId}}" type="checkbox" name="toppings[]" value="{{$topping->id}}">
                                                <label for="{{$tId}}"> </label>
                                            </div>
                                        </li>
                                        @endforeach

                                    </ul>
                                    <div class="col-sm-12 inputbox">
                                        <textarea class="form-control textarea" name="comment" id="comment" placeholder="التعليق"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer textcent bordno">
                                    <button type="button" class="btn btn-default submit-form">{{ _lang('app.add') }}</button>
                                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ _lang('app.cancel') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                  <div id="addToCartModal" class="modal fade popup-basket" role="dialog">
                            <div class="modal-dialog"> 

                                <!-- Modal content-->
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title titlpop">طلبى</h4>
                                    </div>
                                    <form action="#">
                                        <div class="modal-body">
                                            <div class="det-border">
                                                <div class="col-md-12">
                                                    <div class="col-md-2">
                                                        <img src="{{$meal->image}}" alt="" />
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h2>{{$meal->title}}</h2>
                                                        <p>1ساندوتش + بطاطس + كولا + لعبة هدية</p>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <span>عدد</span>
                                                        <input type="text" name="quant[1]" class="form-control input-number" value="1" min="1" max="10">
                                                        <span>20 جنيها</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="det-model">
                                                <div class="col-md-12">
                                                    <div class="title-model">
                                                        <h2>اختر الساندوتش الاول</h2>
                                                        <p>حد ادنى 1 - حد اقصى 1</p>
                                                    </div>
                                                    <ul class="nameprofile">
                                                        <li>
                                                            <p>تشيز برجر </p>
                                                            <div class="radio radio-info radio-inline">
                                                                <input id="inlineRadio11" value="sanwitch1" name="radioInline" type="radio">
                                                                <label for="inlineRadio11"> </label>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <p>بيف برجر</p>
                                                            <div class="radio radio-info radio-inline">
                                                                <input id="inlineRadio12" value="sanwitch2" name="radioInline" checked="" type="radio">
                                                                <label for="inlineRadio12">
                                                                </label>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <p>تشيكن ماكدو</p>
                                                            <div class="radio radio-info radio-inline">
                                                                <input id="inlineRadio13" value="sanwitch3" name="radioInline" type="radio">
                                                                <label for="inlineRadio13"> </label>
                                                            </div>
                                                        </li>
                                                    </ul>

                                                </div>
                                            </div>

                                            <div class="det-model">
                                                <div class="col-md-12">
                                                    <div class="title-model">
                                                        <h2>اختر الساندوتش الثانى</h2>
                                                        <p>حد ادنى 1 - حد اقصى 1</p>
                                                        <p class="help-block">اختياري</p>
                                                    </div>
                                                    <ul class="nameprofile">
                                                        <li>
                                                            <p>تشيز برجر</p>


                                                            <div class="checkbox checkbox-danger ornig">
                                                                <input id="checkbox1" type="checkbox" checked>
                                                                <label for="checkbox1"></label>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <p>بيف برجر</p>


                                                            <div class="checkbox checkbox-danger ornig">
                                                                <input id="checkbox2" type="checkbox" checked>
                                                                <label for="checkbox2"></label>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <p>تشيكن ماكدو</p>


                                                            <div class="checkbox checkbox-danger ornig">
                                                                <input id="checkbox3" type="checkbox" checked>
                                                                <label for="checkbox3"></label>
                                                            </div>
                                                        </li>
                                                    </ul>

                                                </div>
                                            </div>

                                            <div class="det-model">
                                                <div class="col-md-12">
                                                    <div class="title-model">
                                                        <h2>اختر المشروب</h2>
                                                        <p>حد ادنى 1 - حد اقصى 1</p>
                                                    </div>
                                                    <ul class="nameprofile">
                                                        <li>
                                                            <p>بيبسى</p>


                                                            <div class="radio radio-info radio-inline">
                                                                <input id="inlineRadio4" value="pepsi" name="radioInline" type="radio">
                                                                <label for="inlineRadio4"> </label>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <p>كوكاكولا</p>


                                                            <div class="radio radio-info radio-inline">
                                                                <input id="inlineRadio5" value="cola" name="radioInline" type="radio">
                                                                <label for="inlineRadio5"> </label>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <p>سفن اب</p>


                                                            <div class="radio radio-info radio-inline">
                                                                <input id="inlineRadio6" value="7up" name="radioInline" type="radio">
                                                                <label for="inlineRadio6"> </label>
                                                            </div>
                                                        </li>
                                                    </ul>

                                                </div>
                                            </div>
                                            <div class="col-sm-12 inputbox">
                                                <textarea class="form-control textarea" placeholder="التعليق"></textarea>
                                            </div>

                                        </div>
                                        <div class="modal-footer textcent bordno">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">موافق</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!--myModal-->
                <!--myModal-->
                <div id="changeLocationModal" class="modal fade" role="dialog">
                    <div class="modal-dialog modal-lg"> 

                        <!-- Modal content-->
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title titlpop">{{_lang('app.change_location')}}</h4>
                            </div>
                            <form action="{{url('location-suggestions')}}" id="changeLocationForm" method="get" role="form" class="form-search">
                                {{ csrf_field() }}
                                <div class="modal-body">

                                    <div class="form-group col-sm-6">
                                        <select class="form-control" style="height: 53px;" id="city" name="city">
                                            <option value="">{{_lang('app.city')}}</option>
                                            @foreach($cities as $city)
                                            <option value="{{$city->id}}">{{$city->title}}</option>
                                            @endforeach
                                        </select>
                                        <span class="help-block" style="margin: 0px;"></span>
                                    </div>
                                    <!--inpudata-->
                                    <div class="form-group col-sm-6">
                                        <select class="form-control" id="region" style="height: 53px;"  name="region">
                                            <option value="">{{_lang('app.region')}}</option>
                                        </select>
                                        <span class="help-block" style="margin: 0px;"></span>
                                    </div>
                                 
                                </div>
                                <!--inpudata-->
                                <div class="modal-footer textcent bordno">
                                    <button type="button" class="btn btn-default submit-form">{{ _lang('app.ok') }}</button>
                                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ _lang('app.cancel') }}</button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
                <div id="emptyCartModal" class="modal fade" role="dialog">
                    <div class="modal-dialog modal-lg"> 

                        <!-- Modal content-->
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title titlpop">{{_lang('app.message')}}</h4>
                            </div>
                       
                                <div class="modal-body">

                                    <p class="text-center">{{_lang('app.cart_will_be_empty')}}</p>
                                 
                                </div>
                                <!--inpudata-->
                                <div class="modal-footer textcent bordno">
                                    <button type="button" onclick="Cart.emptyCart(this);return false;" class="btn btn-default submit-form">{{ _lang('app.ok') }}</button>
                                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ _lang('app.cancel') }}</button>
                                </div>

                        </div>
                    </div>
                </div>


            </div>
        </div>
        
        <!--size--> 

    </div>
</div>
<!--container-->






@endsection