@extends('layouts.front')

@section('pageTitle','Ga3aaan')

@section('js')
<script src=" {{ url('public/front/scripts') }}/main.js"></script>

<script type="text/javascript" src="{{url('public/front/js')}}/jquery.jscroll.js"></script>

<script type="text/javascript">
    $('ul.pagination').hide();
    $(function() {
        $('.infinite-scroll').jscroll({
            autoTrigger: true,
            loadingHtml: '<img class="center-block" src="{{ url('public/front')}}/images/loading.gif" alt="Loading..." />',
            padding: 0,
            nextSelector: '.pagination li.active + li a',
            contentSelector: 'div.infinite-scroll',
            callback: function() {
                $('ul.pagination').remove();
            }
        });
    });
</script>


@endsection



@section('content')

<div class="container">
    <h2 class="title">المطاعم</h2>
    <p class="text-center pdtext">هناك حقيقة مثبتة منذ زمن طويل وهي أن المحتوى المقروء لصفحة ما سيلهي القارئ عن<br>
        التركيز على الشكل الخارجي للنص أو شكل توضع الفقرات في الصفحة التي يقرأها.</p>
    <div class="row">
    <div class="infinite-scroll">
        @foreach($resturantes as $resturant)
      <!--  <div class="col-sm-6 laboxs"> -->
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
                </div>
                <div class="row detbox">
                    <div class="boxondet">
                        @if($resturant->has_offer)
                        <p class="colorpris">%</p>
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

       <!-- </div> -->
        @endforeach

        
            {{ $resturantes->links() }}  
        
    </div>

    </div>
    <!--row-->

   
    <!--pager--> 

</div>
<!--container-->





@endsection