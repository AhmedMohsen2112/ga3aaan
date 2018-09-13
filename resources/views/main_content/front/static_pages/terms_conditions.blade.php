@extends('layouts.front')

@section('pageTitle', _lang('app.terms_and_conditions'))

@section('js')
	
@endsection

@section('content')

  <div class="container minhitcon">
  <div class="row">
    <div class="col-sm-12 lefttbox">
      <h2 class="title hidden-xs">{{ _lang('app.terms_and_conditions') }}</h2>
      <p class="textcont">
      
           {{ $settings->terms_conditions }}
      </p>
    </div>
    <!--lefttbox--> 
    
  </div>
  <!--row--> 
  
</div>


	
@endsection