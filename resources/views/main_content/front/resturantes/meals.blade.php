@extends('layouts.front')

@section('pageTitle',$page_title)

@section('js')
<script src=" {{ url('public/front/scripts') }}/main.js"></script>
<script type="text/javascript" src="{{url('public/front/js')}}/jquery.jscroll.js"></script>

<script type="text/javascript">
    $('ul.pagination').hide();
    $(function () {
        $('.infinite-scroll').jscroll({
            autoTrigger: true,
            loadingHtml: '<img class="center-block" style="margin-left:35%;" src="{{url('public/front')}}/images/loading.gif" alt="Loading..." />',
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

@section('cart')
   <div class="basket-hidden"><a href="{{_url('cart?step=1')}}" style="display: block;"> <i class="fa fa-shopping-cart"></i> </a> </div>
@endsection

@section('content')


<div class="container">
    <h2 class="title hidden-xs"> {{ $menu_section->title }}</h2>
    <div class="row">
    <div class="infinite-scroll">
        @foreach($meals as $meal)
        <div class="col-sm-3 photbx">
            <div class="in-boxs">


                <a href="{{_url('resturant/'.$meal->resturant_slug.'/'.$meal->menu_section_slug.'/'.$meal->slug)}}" >
                

                <span href="{{ route('add-favourite',$meal->slug) }}" class="property-box {{$meal->is_favourite?'active':''}}" data-config="{{json_encode($meal->config)}}"  onclick="event.preventDefault(); main.handleFavourites(this)">
                    <i class="fa fa-heart-o" aria-hidden="true"></i>
                </span>

                <img src="{{$meal->image}}"> 
                <a href="{{_url('resturant/'.$meal->resturant_slug.'/'.$meal->menu_section_slug.'/'.$meal->slug)}}" class="nam-tit">{{$meal->title}}</a> 
                <span class="namber texmber" style="float:right; padding:0;">
                    @if ($meal->discount_price)
                        <del class="delete">{{ $meal->price }} {{ $currency_sign }}</del>
                     {{ $meal->discount_price }} {{ $currency_sign }}

                    @else
                      {{ $meal->price }} {{ $currency_sign }}
                    @endif
                    
                </span>
                </a>
               
            </div>
        </div>
        @endforeach
        {{ $meals->appends($_GET)->links() }}
    </div>
        <!--photbx-->



    </div>
    <!--row-->

   
    <!--pager--> 

</div>





@endsection