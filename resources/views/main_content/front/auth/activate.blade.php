@extends('layouts.front')

@section('pageTitle',$page_title)

@section('js')
  <script src=" {{ url('public/front/scripts') }}/login.js"></script>
@endsection

@section('content')

<div class="container">
  <div class="centerbolog">

    <h2 class="title" >{{ _lang('app.activation_code_sent_to') }}<br><span class="nemtext" style="margin-top:30px">{{ session()->get('ga3an_data')['mobile'] }}</span></h2>

    <div class="ptex"><img src="{{ url('public/front') }}/images/code.png"></div>

    <form class="text-in" method="post" action="{{ route('activationuser') }}" id = "activationForm">
     {{ csrf_field() }}
    <div id="alert-message" class="alert alert-success" style="display:{{ ($errors->has('msg')) ? 'block' : 'none' }};margin-top: 20px;">
        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i> 
        <span class="message">
            @if ($errors->has('msg'))
            <strong>{{ $errors->first('msg') }}</strong>
            @endif
        </span>
    </div>
      
      <div class="row activation">

        <div class="col-sm-3 inputbox">
          <input type="text" class="form-control" name="activation[]" placeholder="0" maxlength="1">
        </div>
        <div class="col-sm-3 inputbox">
          <input type="text" class="form-control" name="activation[]" placeholder="0" maxlength="1">
        </div>
        <div class="col-sm-3 inputbox">
          <input type="text" class="form-control" name="activation[]" placeholder="0" maxlength="1">
        </div>
        <div class="col-sm-3 inputbox">
          <input type="text" class="form-control" name="activation[]" placeholder="0" maxlength="1">
        </div>
        <span class="help-block"></span>
        <div class="col-sm-12 inputbox mapx" style="width:100%;">
          <button type="submit" class="botoom submit-form">{{ _lang('app.confirm_activation_code') }}</button>
        </div>

        
      </div>
      <!--row--> 
      
      <a class="cololink text-center" href="{{ route('edit-phone') }}">{{ _lang('app.edit_mobile_number') }}</a> <a class="cololink text-center" href="#">{{ _lang('app.resend_activation_code') }}</a>
    </form>
  </div>
  <!--centerbolog--> 
  
</div>




  
@endsection