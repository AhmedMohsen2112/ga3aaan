@extends('layouts.front')

@section('pageTitle',$page_title)

@section('js')
<script src=" {{ url('public/front/scripts') }}/main.js"></script>
@endsection



@section('content')

<style>
   .timeon{
    direction: ltr;
   }
</style>

<div class="container">
  <div class="centerbolog">
        <h2 class="title hidden-xs">{{ $resturant->resturant_title }} - {{ $resturant->branch_title }}</h2>
        <div class="imgprofile neshedo"><img src="{{ url('public/uploads/resturantes').'/'.$resturant->image }}"></div>
        <div class="bordlin marsiz">
          
        </div>
        <h4 class="nam-tit">
       {{ _lang('app.working_hours') }}
        </h4>
        <div class="bordlin marsiz">
          <div class="timeon">
            <p>{{ _lang('app.saturday') }}</p>
            <span>{{ $resturant->working_hours['Sat']['from'] }}</span> -- <span>{{ $resturant->working_hours['Sat']['to'] }}</span></div>
          <div class="timeon">
            <p>{{ _lang('app.sunday') }}</p>
             <span>{{ $resturant->working_hours['Sun']['from'] }}</span> -- <span>{{ $resturant->working_hours['Sun']['to'] }}</span> </div>
        </div>
        <!--woorktime-->
        
        <div class="bordlin marsiz">
          <div class="timeon">
            <p>{{ _lang('app.monday') }}</p>
            <span>{{ $resturant->working_hours['Mon']['from'] }}</span> -- <span>{{ $resturant->working_hours['Mon']['to'] }}</span>  </div>
          <div class="timeon">
            <p>{{ _lang('app.tuesday') }}</p>
            <span>{{ $resturant->working_hours['Tue']['from'] }}</span> -- <span>{{ $resturant->working_hours['Tue']['to'] }}</span> </div>
        </div>
        <!--woorktime-->
        
        <div class="bordlin marsiz">
          <div class="timeon">
            <p>{{ _lang('app.wednesday') }}</p>
             <span>{{ $resturant->working_hours['Wed']['from'] }}</span> -- <span>{{ $resturant->working_hours['Wed']['to'] }}</span>  </div>
          <div class="timeon">
            <p>{{ _lang('app.thursday') }}</p>
             <span>{{ $resturant->working_hours['Thu']['from'] }}</span> -- <span>{{ $resturant->working_hours['Thu']['to'] }}</span> </div>
        </div>
        <!--woorktime-->
        
        <div class="bordlin marsiz">
          <div class="timeon witother">
            <p>{{ _lang('app.friday') }}</p>
            <span>{{ $resturant->working_hours['Fri']['from'] }}</span> -- <span>{{ $resturant->working_hours['Fri']['to'] }}</span>  </div>
        </div>
        <!--woorktime-->
        
            <div class="total marsiz">
              <p>{{ _lang('app.rate') }}</p>
              <span class="namber texcenter">({{ $resturant->num_of_raters }})</span> 
             
              
               <div class="starbox"> 
                    <i class="fa fa-star {{$resturant->rate >= 1?'':'nonbc'}}" aria-hidden="true"></i>
                    <i class="fa fa-star {{$resturant->rate >= 2?'':'nonbc'}}" aria-hidden="true"></i> 
                    <i class="fa fa-star {{$resturant->rate >= 3?'':'nonbc'}}" aria-hidden="true"></i> 
                    <i class="fa fa-star {{$resturant->rate >= 4?'':'nonbc'}}" aria-hidden="true"></i> 
                    <i class="fa fa-star {{$resturant->rate >= 5?'':'nonbc'}}" aria-hidden="true"></i> 
                </div>
            </div>
            
            <div class="scroll">
                @foreach ($resturant_branch_rates as $rate)
                    <div class="agent comments cominner" style="margin-top:15px;">
                      <h4 class="nam-tit">{{ $rate->user }}</h4>
                      <span class="textdeit">{{ $rate->opinion }}</span> 
                      <!--          <input class="rating-input" type="text" title=""/> 
                -->
                      <div class="starbox"> 

                        <i class="fa fa-star {{$rate->rate >= 1?'':'nonbc'}}" aria-hidden="true"></i>
                    <i class="fa fa-star {{$rate->rate >= 2?'':'nonbc'}}" aria-hidden="true"></i> 
                    <i class="fa fa-star {{$rate->rate >= 3?'':'nonbc'}}" aria-hidden="true"></i> 
                    <i class="fa fa-star {{$rate->rate >= 4?'':'nonbc'}}" aria-hidden="true"></i> 
                    <i class="fa fa-star {{$rate->rate >= 5?'':'nonbc'}}" aria-hidden="true"></i> 

                       </div>
                </div>
                @endforeach
                
                <!--cominner-->
                
            </div>
            <!--cominner--> 
  </div>
  <!--centerbolog--> 
  
</div>

@endsection