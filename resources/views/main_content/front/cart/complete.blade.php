@extends('layouts.front')

@section('pageTitle',$page_title)

@section('js')
<script src=" {{ url('public/front/scripts') }}/cart.js"></script>


@endsection



@section('content')
<div class="container">
    <div class="alert alert-success" style="display:{{Session('successMessage')?'block;':'none;'}}; " role="alert"><i class="fa fa-check" aria-hidden="true"></i> <span class="message">{{Session::get('successMessage')}}</span></div>
    <div class="alert alert-danger" style="display:{{Session('errorMessage')?'block;':'none;'}}; " role="alert"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> <span class="message">{{Session::get('errorMessage')}}</span></div>
    <form method="POST"  id = "newOrderForm">
        {{ csrf_field() }}
        <div class="centerbolog">
            <div id="cart-content">
                <h2 class="title hidden-xs">{{ _lang('app.complete_order') }}</h2>
                <div class="orderbasc"> 
                    @if(count($addresses) > 0)
                    <a href="#" onclick="Cart.showAddresses(this);return false;" class="nam-tit tit-blog"><i class="fa fa-map-marker" aria-hidden="true"></i> {{_lang('app.edit_address')}}</a>
                    @else
                    <a href="{{_url('user-addresses/create?return='.base64_encode(request()->getPathInfo() . (request()->getQueryString() ? ('?' . request()->getQueryString()) : ''))) }}" class="nam-tit tit-blog"><i class="fa fa-map-marker" aria-hidden="true"></i> {{_lang('app.add_address')}}</a>
                    @endif
                    @if(count($addresses) > 0)
                    <input type="hidden" name="address" value="{{$addresses[0]->id}}">
                    <ul class="nameprofile basket">
                        <li>
                            <p>{{_lang('app.address')}}</p>
                            <br/>
                            <p id="selected-address">{{$addresses[0]->long_address}}</p>
                        </li>
                        <!--                <li>
                                            <p>الهاتف</p>
                                            <br/>
                                            <p>0000000</p>
                                        </li>-->
                    </ul>
                    @endif
                </div>
                <!--orderbasc-->

                <div class="orderbasc">
                    <div class="paycash">
                        <p class="botext">طريقة الدفع</p>
                        @foreach($payment_methods as $key =>$one)
                        @php $pid='p'.$one->id; @endphp
                        <div class="radio radio-info radio-inline">
                            <input type="radio" id="{{$pid}}" value="{{$one->id}}" name="payment_method" {{$key==0?'checked':''}}>
                            <label for="{{$pid}}"> {{$one->title}}</label>
                        </div>
                        @endforeach
                    </div>
                    <div class="total">
                        <p>المجموع</p>
                        <p>{{isset($cart['price_list']['net_price'])?$cart['price_list']['net_price']:$cart['price_list']['total_price'].' '.$currency_sign}}</p>
                    </div>
                    <a  class="botoom submit-form">ارسال  الان</a> </div>
                <!--orderbasc--> 

            </div>
        </div>
    </form>
    <!--centerbolog-->

    <div id="requestMessageModal" class="modal fade in" role="dialog" aria-hidden="false" >
        <div class="modal-dialog"> 

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn btn-default cols" data-dismiss="modal">×</button>
                    <h4 class="modal-title titlpop">تم ارسال الطلب</h4>
                </div>
                <div class="modal-body">
                    <p class="text-center bothenk">شكرا لاستخدامك جعان</p>
                </div>
            </div>
        </div>
    </div>
    <!--myModa2--> 

</div>
<div class="modal fade" id="editAddressModal" role="dialog">
    <div class="modal-dialog modal-lg" style="width:60%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" style="color:#fff;">تعديل العنوان</h4>
            </div>
            <div class="modal-body">
                <div class="col-sm-12" style="margin:10px 0;">
                    @foreach($addresses as $key=> $one)
                    @php $addressId='address'.$one->id; @endphp
                    <div class="col-sm-4">
                        <div class="box" style="border:1px solid #f0c50d; border-radius:5px; padding:5px;">
                            <h1>{{_lang('app.addresss')}}</h1>
                            <p>{{$one->long_address}}</p>

                            <div class="radio radio-info radio-inline" style="float:left;">
                                <input class="address-one" id="{{$addressId}}" value="{{$one->id}}" name="addesss" {{$key==0?'checked':''}} type="radio">
                                <label for="{{$addressId}}"> اختيار</label>
                            </div>
                            <!--<a href="#" style="display:block; border-top:1px solid #eee; text-align:left; color:#d5344a;">تعديل </a>-->
                        </div>
                    </div>
                    @endforeach

                </div>
                <div class="modal-footer" style="border:none;">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{_lang('app.close')}}</button>
                    <button type="button" class="btn btn-default edit-address-submit">{{_lang('app.save')}}</button>
                </div>
            </div>
        </div>
    </div>
</div>






@endsection