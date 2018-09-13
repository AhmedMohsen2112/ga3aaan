@extends('layouts.front')

@section('pageTitle',_lang('app.resturantes'))

@section('js')
<script src=" {{ url('public/front/scripts') }}/main.js"></script>
<script src=" {{ url('public/front/scripts') }}/resturantes.js"></script>

<script type="text/javascript" src="{{url('public/front/js')}}/jquery.jscroll.js"></script>

<script type="text/javascript">
$('ul.pagination').hide();
$(function () {
    $('.infinite-scroll').jscroll({
        autoTrigger: true,
        loadingHtml: '<img class="center-block" style="margin-left:35%;" src="{{url('public / front')}}/images/loading.gif" alt="Loading..." />',
        padding: 0,
        nextSelector: '.pagination li.active + li a',
        contentSelector: 'div.infinite-scroll',
        callback: function () {
            $('ul.pagination').remove();
        }
    });
});
</script>


@endsection

@section('filter')
@include('components.front.filter')
@endsection

@section('header-bottom')
<div class="clearfix"></div>
<div class="banner-bg">
<!--   <img src="images/banner-bg.jpg" alt="">-->
    <div class="banner-bg-content">
        <div class="container">
            <h2>{{_lang('app.choose_the_most_delicious_restaurants')}}</h2>
            @if($region)
            <p>{{_lang('app.your_current_location').' '.$region->title}}</p>
            @else
            <p>{{_lang('app.enter_your_address_to_find_your_local_restaurants')}}</p>
            @endif

            <form action="{{url('location-suggestions')}}" id="search-form" method="get" role="form" class="form-search form-search-bc" novalidate="novalidate">
                {{ csrf_field() }}
                <div class="form-group col-sm-4 inpudata">
                    <select class="form-control" style="height: 53px;" id="city" name="city">
                        <option value="">{{_lang('app.city')}}</option>
                        @foreach($cities as $city)
                        <option value="{{$city->id}}">{{$city->title}}</option>
                        @endforeach
                    </select>
                    <span class="help-block" style="margin: 0px;"></span>
                </div>
                <!--inpudata-->
                <div class="form-group col-sm-4 inpudata">
                    <select class="form-control" id="region" style="height: 53px;"  name="region">
                        <option value="">{{_lang('app.region')}}</option>
                    </select>
                    <span class="help-block" style="margin: 0px;"></span>
                </div>

                <div class="col-sm-4 inpudata">
                    <button type="submit" class="submit-form botoom cobotm"><span class="fa fa-search"></span></button>
                </div>

            </form>


        </div>
    </div>

    <div class="clearfix"></div>
</div>
@endsection
@section('content')

<!--<div class="minhead">
  <div class="container">
    <nav class="regast"> <a href="#"><i class="fa fa-key" aria-hidden="true"></i> تسجيل دخول</a> <a href="#"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> تسجيل جديد </a> <a href="#" class="flag"><i class="fa fa-flag-o" aria-hidden="true"></i> English</a> </nav>
    <nav class="boxicon"> <a href="#" class="fa fa-facebook fac-flw" title="فيس بوك"></a> <a href="#" class="fa fa-twitter twi-flw" title="تويتر"></a> <a href="#" class="fa fa-google-plus plus-flw" title="جوجل بلاس"></a> <a href="#" class="fa fa-snapchat-ghost snapchat" title="سناب شات"> </a> <a href="#" class="fa fa-instagram inst-flw" title="انستجرام"> </a> <a href="#" class="fa fa-youtube youtube-flw" title="يوتيوب"></a> </nav>
  </div>
 
  
</div>--> 
<!--minhead-->



<div class="resturant-filter">
    <h2 class="title hidden-xs">{{ _lang('app.resturantes') }}</h2>
    <div class="col-md-12">
        <div class="col-md-3 hidden-sm hidden-xs" style="background: #fbfbfb;margin-top:30px;">
            <div class="row">

                <div class="col-md-12">
                    <form class="example" style="margin-top:30px;">
                        <button type="submit" class="search-btn"><i class="fa fa-search"></i></button>
                        <input type="text" id="query" placeholder="بحث" name="query" style="background:#fff;">

                    </form>
                    <form id="filter-form" style="margin-top:30px;">
                        <div class="filter">
                            <h2 style="font-size: 18px;color: #d5344a;margin: 10px 0 0 0; text-align:center;border-top:1px solid #ccc;padding-top: 10px;">فلتر - اظهر حسب</h2>
                            <div class="col-md-12 check" style="margin:10px 0; border-bottom:1px solid #eee;">
                                <div class="row">
                                    <div class="col-md-8">	
                                        <p>{{ _lang('app.rate') }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <label style="text-align:left;">
                                            <input type="checkbox" {{isset($filter['rating'])&&$filter['rating']==1?'checked':''}} name="rating" value="1">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 check" style="margin:10px 0; border-bottom:1px solid #eee;">	
                                <div class="row">
                                    <div class="col-md-8">	
                                        <p>{{ _lang('app.offers') }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <label style="text-align:left;">
                                            <input type="checkbox" {{isset($filter['has_offer'])&&$filter['has_offer']==1?'checked':''}} name="has_offer" value="1">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 check" style="margin:10px 0; border-bottom:1px solid #eee;">	
                                <div class="row">
                                    <div class="col-md-8">	
                                        <p>{{ _lang('app.delivery_cost') }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <label style="text-align:left;">
                                            <input type="checkbox" name="fast_delivery" {{isset($filter['fast_delivery'])&&$filter['fast_delivery']==1?'checked':''}} value="1">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="">

                            <a class="dropdown-btn ">
                                {{ _lang('app.cuisine_type') }}
                                <i class="fa fa-angle-down" aria-hidden="true"></i>
                            </a>
                            <div class="dropdown-container" style="display: block;">
                                @foreach($cuisines as $one)
                                <div class="check">
                                    <div class="col-md-8">	
                                        <p>{{$one->title}}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <label style="text-align:left;">
                                            <input type="checkbox" name="cuisines[]" {{(isset($filter['cuisines'])&&in_array($one->id,$filter['cuisines']))||(isset($cuisine)&&$cuisine->id==$one->id)?'checked':''}} value="{{$one->id}}">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                                @endforeach

                            </div>
                        </div>

                        <button type="submit" class="botoom submit-form" style="margin:20px 5px 0 5px;width:45%;float:right;" >فلتر</button>
                        <button type="reset" value="Reset" class="botoom" style="margin:20px 5px 0 5px;width:45%;float:right;" >مسح</button>
                    </form>
                    <div class="col-md-12" style="padding-bottom:10px;">
                        <div class="row">
                            <h2 style="margin: 20px 0 0 0;padding:10px 0;font-size: 20px;text-align:center;border-top:1px solid #eee;color: #d5344a;">كلمات ذات بحث</h2>
                            @foreach($categories as $one)
                            <a href="{{_url('resturantes?cat='.$one->id)}}" class="btn btn-default btn-lg {{isset($filter['cat'])&&$filter['cat']==$one->id?'color':''}}">{{$one->title}}</a>
                            @endforeach

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="row">
                <div class="infinite-scroll">
                    @foreach($resturantes as $resturant)
                    <div class="col-sm-6 laboxs width"> 
                        <a href="{{_url('resturant/'.$resturant->slug)}}" class="innerboxslin"> 

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
                                <h3 class="nam-tit" style="font-size: 1rem">{{$resturant->title}}</h3>
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
                        </a><!--innerboxslin--> 

                    </div>
                    @endforeach
                    {{ $resturantes->appends($_GET)->links() }} 
                </div>


                <div class="col-md-12">
                    <div class="add-res">
                        <form id="suggestions-form" action="{{ _url('resturantes/suggest') }}" method="post">
                            {{ csrf_field() }}
                            <h2 style="margin:50px 0 20px 0;font-size: 20px;text-align:center;">لم تجد مطعمك المفضل ؟ قم بترشيحه الآن</h2>
                            <div class="form-group {{ $errors->has('resturant_name') ? ' has-error' : '' }}">
                                <input id="resturant_name" name="resturant_name" class="form-control " placeholder=" اسم المطعم " type="text">
                                <span class="help-block">{{ $errors->has('resturant_name') ? $errors->first('resturant_name') : '' }}</span>
                            </div>
                            <div class="form-group {{ $errors->has('resturant_region') ? ' has-error' : '' }}">
                                <input id="resturant_resgion" name="resturant_region" class="form-control " placeholder=" المنطقة" type="text">
                                <span class="help-block">{{ $errors->has('resturant_region') ? $errors->first('resturant_region') : '' }}</span>
                            </div>
                            <button type="submit" class="botoom submit-form" >اضافة</button>
                        </form>
                    </div>
                </div>

                <div class="alert alert-success" style="display:{{Session('successMessage')?'block;':'none;'}}; " role="alert"><i class="fa fa-check" aria-hidden="true"></i> <span class="message">{{Session::get('successMessage')}}</span></div>
                <div class="alert alert-danger" style="display:{{Session('errorMessage')?'block;':'none;'}}; " role="alert"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> <span class="message">{{Session::get('errorMessage')}}</span></div>
            </div>
        </div>
    </div>



</div>
<!--container-->





@endsection