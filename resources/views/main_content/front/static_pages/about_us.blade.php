@extends('layouts.front')

@section('pageTitle',_lang('app.about_us'))

@section('js')
	
@endsection

@section('content')

  
  <div class="row">
    <div class="col-sm-12 lefttbox">
      <h2 class="title hidden-xs">{{ _lang('app.about_us') }}</h2>
      <p class="textcont">
      
           {{ $settings->about_us }}
      </p>
    </div>
    <!--lefttbox--> 
    
  </div>
  <!--row--> 
  



	
@endsection