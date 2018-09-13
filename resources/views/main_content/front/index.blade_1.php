@extends('layouts.front')

@section('pageTitle','Ga3aaan')

@section('js')

<script src=" {{ url('public/front/scripts') }}/main.js"></script>
@endsection



@section('content')

<div class="siderdiv">
    <div id="wowslider-container1">
        <div class="overlay"></div>
        <div class="ws_images">
            <ul>
                @foreach($slider as $one)
                <li><img src="{{$one->image}}" title=""/> </li>
                @endforeach

            </ul>
        </div>
        <!--//.ws_images-->

        <div class="ws_bullets">
            @foreach($slider as $one)
            <a href="{{$one->url}}"></a> 
            @endforeach

        </div>
    </div>
    <div class="container center-serch">
        <h1 class="title-se"> ابحث عن العديد من أفضل المطاعم</h1>
        <form action="{{url('location-suggestions')}}" id="search-form" method="get" role="form" class="form-search form-search-bc">
            {{ csrf_field() }}
            <div class="form-group col-sm-3">
                <select class="form-control" id="category" name="category">
                    <option value="">{{_lang('app.category')}}</option>
                    @foreach($categories as $category)
                    <option value="{{$category->id}}">{{$category->title}}</option>
                    @endforeach
                </select>
                <span class="help-block"></span>
            </div>
            <!--inpudata-->

            <div class="form-group col-sm-3">
                <select class="form-control" id="city" name="city">
                    <option value="">{{_lang('app.city')}}</option>
                    @foreach($cities as $city)
                    <option value="{{$city->id}}">{{$city->title}}</option>
                    @endforeach
                </select>
                <span class="help-block" style="margin: 0px;"></span>
            </div>
            <!--inpudata-->
            <div class="form-group col-sm-3">
                <select class="form-control" id="region" name="region">
                    <option value="">{{_lang('app.region')}}</option>
                </select>
                <span class="help-block" style="margin: 0px;"></span>
            </div>
            <!--inpudata-->

            <div class="col-sm-3  inpudata">
                <button type="submit" class="submit-form botoom cobotm">{{_lang('app.view_resturantes')}}</button>
            </div>
            <!--inpudata-->

        </form>
    </div>
    <!--center-serch--> 

</div>
<!--//.siderdiv-->

<div class="container">
    <h2 class="title">اشهر المطاعم</h2>
    <p class="text-center pdtext">هناك حقيقة مثبتة منذ زمن طويل وهي أن المحتوى المقروء لصفحة ما سيلهي القارئ عن<br/>
        التركيز على الشكل الخارجي للنص أو شكل توضع الفقرات في الصفحة التي يقرأها.</p>
    <div class="row">
        @foreach($famous_resturants as $resturant)
        <div class="col-sm-3 laboxs nnerbo"> 
            <a href="{{_url('resturant/'.$resturant->slug)}}" class="innerboxslin centerlogo"> 
                @if($resturant->is_new)
                <span class="new-bc">{{_lang('app.new')}}</span>
                @endif
                @if($resturant->is_ad)
                <span class="new-bc new-bc2">{{_lang('app.ad')}}</span>
                @endif
                <div class="imgover">
                    <img src="{{$resturant->image}}">
                    @if($resturant->is_open)
                    <span class="overlaytext">{{_lang('app.closed')}}</span> 
                    @endif
                </div>
                <h3 class="nam-tit">{{$resturant->title}}</h3>
            </a><!--innerboxslin--> 

        </div>
        @endforeach


    </div>
    <!--row-->

    <!--<div class="text-center pdtext"> <a href="#" class="botoom"> المزيد من المطاعم</a> </div>-->
</div>
<!--//.container-->

<div class="botbar">
    <div class="container">
        <div class="row">
            <div class="col-sm-8 witrspon witbaner">
                <div class="your-stud sponsor">
                    @foreach($ads as $ad)
                    <a href="{{$ad->url}}"> <img src="{{$ad->image}}"> </a> 
                    @endforeach
                </div>
            </div>
            <!--witrspon-->

          <!--  <div class="col-sm-4 witrspon witbaner">
                <div class="borderbox">
                    <h2 class="title-serch">تحميل التطبيق من على </h2>
                    <div class="row">
                        <div class="col-sm-4 floting"> 
                            <a href="{{$settings->android_url}}" class="icon-go"><img width="200" src="{{url('public/front/images')}}/icon-go.png"></a> 
                            <a href="{{$settings->ios_url}}" class="icon-go"><img width="200" src="{{url('public/front/images')}}/icon-go2.png"></a> </div>

                        <div class="col-sm-8 floting">
                            <h3 class="paddiv">{{ _lang('app.ga3aaan_Food_Order_Online') }}</h3>
                        </div>
                    </div>
                </div>
               

            </div> -->


            <div class="col-sm-3 witrspon witbaner">
                <div class="borderbox">
                  <h2 class="title-serch">تحميل التطبيق من على </h2>
                  <div class="row"> <a href="{{$settings->android_url}}" class="col-sm-6 icon-go"><img src="{{url('public/front/images')}}/icon-go.png"></a> <a href="{{$settings->ios_url}}" class="col-sm-6 icon-go"><img src="{{url('public/front/images')}}/icon-go2.png"></a> </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--//.container-->

<!--//.botbar-->

<div class="botbar baimg">
    <div class="container">
        <h2 class="title-serch">أخر العروض المضافة</h2>
        <div class="row">


            <div class="col-sm-9 witrspon">
                <div class="your-studs">
                    @foreach($offers as $offer)
                    <div class="photbx">
                        <div class="in-boxs"> <a href="{{_url('resturant/'.$offer->resturant_slug)}}" class="phoitm"> <img src="{{$offer->image}}">
                                <div class="overlay">
                                    <div class="text"><i class="fa fa-link" aria-hidden="true"></i>
                                        <p>شاهد العرض</p>
                                    </div>
                                </div>
                            </a><!--phoitm--> 

                            <a href="{{_url('resturant/'.$offer->resturant_slug)}}" class="nam-tit">{{$offer->resturant_title}}</a>
                        </div>
                    </div>
                    @endforeach


                </div>
            </div>
        </div>

        <!--your-studs--> 

    </div>
    <!--container--> 

</div>


@endsection