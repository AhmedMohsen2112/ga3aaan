@extends('layouts.front')

@section('pageTitle',$resturant->title)

@section('js')
<script src=" {{ url('public/front/scripts') }}/main.js"></script>
@endsection

@section('cart')
   <div class="basket-hidden"><a href="{{_url('cart?step=1')}}" style="display: block;"> <i class="fa fa-shopping-cart"></i> </a> </div>
@endsection

@section('content')


<div class="width-img">
    <div class="centerbolog">
        <h2 class="title hidden-xs">{{$resturant->title}}</h2>
        <div class="innerboxslin nerboxs img-margin"> 
            @if($resturant->is_new)
            <span class="new-bc">{{_lang('app.new')}}</span>
            @endif
            @if($resturant->is_ad)
            <span class="new-bc new-bc2">{{_lang('app.ad')}}</span>
            @endif
            <div class="imgover">
                <img src="{{$resturant->image}}">
                @if(!$resturant->is_open)
                <span class="overlaytext">{{_lang('app.closed')}}</span> 
                @endif
            </div>
            <div class="divtitle">
                <h3 class="nam-tit">{{$resturant->title}}</h3>
                <div class="starbox"> 
                    <span class="namber">({{$resturant->num_of_raters}})</span> 
                    <i class="fa fa-star {{$resturant->rate >= 1?'':'nonbc'}}" aria-hidden="true"></i>
                    <i class="fa fa-star {{$resturant->rate >= 2?'':'nonbc'}}" aria-hidden="true"></i> 
                    <i class="fa fa-star {{$resturant->rate >= 3?'':'nonbc'}}" aria-hidden="true"></i> 
                    <i class="fa fa-star {{$resturant->rate >= 4?'':'nonbc'}}" aria-hidden="true"></i> 
                    <i class="fa fa-star {{$resturant->rate >= 5?'':'nonbc'}}" aria-hidden="true"></i> 
                </div>
                <p class="textblog">
                    @foreach($resturant->cuisines as $key=> $cuisine)
                    {{$cuisine->title}}
                    @if(count($resturant->cuisines) != ($key+1))
                    {{' - '}}
                    @endif
                    @endforeach
                </p>
                @if ($resturant->branch_id)
                    <a href="{{ route('resturant_info',$resturant->slug) }}" style="float:left; color:#d5344a;"><i class="fa fa-info-circle" style="font-size:24px;" aria-hidden="true"></i></a>
                @endif
                
            </div>
            <div class="row detbox">
                <div class="boxondet">
                     @if($resturant->has_offer)
                        <p class="colorpris">%{!! $resturant->hasVisa == 1 ? '<del><i class="fa fa-credit-card"></i></del>':'' !!}</p>
                    @endif
                </div>
                <div class="boxondet">
                    <p>{{_lang('app.delivery_time')}}</p>
                    <span>{{$resturant->delivery_time.' '._lang('app.minute')}}</span> </div>
                <div class="boxondet">
                    <p>{{_lang('app.delivery_cost')}}</p>
                    <span>{{$resturant->delivery_cost.' '.$currency_sign}}</span> </div>
                <div class="boxondet">
                    <p>{{_lang('app.minimum_charge')}}</p>
                    <span>{{$resturant->minimum_charge.' '.$currency_sign}}</span> </div>
            </div>
            <!--innerboxslin--> 

        </div>
        <!--innerboxslin-->

        <div class="orderbasc">
            <ul class="nameprofile basket orderlast">
                 @if($resturant->offer)
                <li> 
                    <a href="#" class="active"> <i class="fa fa-bullhorn" aria-hidden="true"></i>
                        <div class="padline">
                            <p>{{$resturant->offer->offer}}</p>
                            @if($resturant->offer->type==2||$resturant->offer->type==3)
                            <p>{{$resturant->offer->detailes}}</p>
                            @endif
                             <span class="textdeit">تنتهى {{$resturant->offer->valid_until}} </span> 
                        </div>
                      
                    </a> 
                </li>
                    @endif
               @foreach($resturant->menu_sections as $key=> $menu_section)
                <li> 
                    <a href="{{_url('resturant/'.$resturant->slug.'/'.$menu_section->slug)}}">
                        <p>{{$menu_section->title}}</p>
                        <i class="fa fa-chevron-circle-left" aria-hidden="true"></i> 
                    </a> 
                </li>
                @endforeach
            </ul>
        </div>
        
    </div>
    <!--centerbolog--> 

</div>





@endsection