@extends('layouts.front')

@section('pageTitle',$page_title)

@section('js')
<script src=" {{ url('public/front/scripts') }}/login.js"></script>  
@endsection

@section('content')

<div class="container">
  <h2 class="title hidden-xs">{{ _lang('app.forget_password') }}</h2>
    
  <div class="centerbolog">
    <form class="text-in" method="POST" action="{{ route('password.email') }}" id="forgotPasswordForm">
      {{ csrf_field() }}

      <div id="alert-message" class="alert alert-success" style="display:{{ (session()->has('status')) ? 'block' : 'none' }};margin-top: 20px;">
        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i> 
        <span class="message">
            @if (session()->has('status'))
            <strong>{{ session('status') }}</strong>
            @endif
        </span>
    </div>

      <div class="row">
      <div class="col-sm-12 inputbox form-group {{ $errors->has('email') ? ' has-error' : '' }}">
        <input type="text" class="form-control" name="email" placeholder="{{ _lang('app.email') }}" id="email">

        <span class="help-block">
             @if ($errors->has('email')) 
              {{ $errors->first('email') }}
              @endif
        </span>

      </div>
      <div class="col-sm-12 inputbox mapx">
        <button type="submit" class="botoom submit-form">{{ _lang('app.send_request') }}</button>
      </div>
      </div>
      <!--row-->
    </form>
  </div>
  
</div>




  
@endsection