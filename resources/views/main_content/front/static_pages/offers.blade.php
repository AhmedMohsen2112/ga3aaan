@extends('layouts.front')

@section('pageTitle',$page_title)

@section('js')

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

@section('content')
<div class="container img-size text">
    
        <h2 class="title hidden-xs">{{ _lang('app.offers') }}</h2>
         <div class="infinite-scroll">
        @foreach($offers as $offer)
        <div class="bordlin">
            <div class="centers"><a href="{{_url('resturant/'.$offer->resturant_slug)}}" class="imgover"><img src="{{$offer->image}}"> </a></div>
            <div class="divtitle">
                <a href="{{_url('resturant/'.$offer->resturant_slug)}}" class="nam-tit tit-blog">{{$offer->offer}}</a>
                <p class="textblog">{{$offer->resturant_title}} </p>
                <span class="namber">العرض سارى حتى {{$offer->available_until}}</span> </div>
        </div>
        @endforeach
        {{ $offers->links() }} 
    </div>
        <!--bordlin-->

        

</div>
<!--container-->



@endsection