@extends('layouts.user_profile')
@section('pageTitle',$page_title)

@section('title')
{{ $page_title }}
@endsection


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

<div class="col-sm-12 float-menu">

    <div class="request-responsive" style="margin-top: 40px;">
      <div class="infinite-scroll">
      @foreach ($orders as $order)
      
      <div class="bordlin">
         <a href="{{route('user-orders.show',Crypt::encrypt($order->id))}}">
        <div class="centers">
          <div href="#" class="imgover">
           <img src="{{ $order->resturant_image }}"> 
         </div>
       </div>
       <div class="divtitle">
        <h3 class="nam-tit">{{ $order->resturant }} - {{ $order->region }}</h3>
         <span class="namber">{{ $order->status_text }}</span> </div>
          </a>
       </div>
       
       @endforeach
           {{ $orders->appends($_GET)->links() }} 
     </div>

       
    </div>
  
</div>




@endsection