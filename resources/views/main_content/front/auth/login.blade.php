@extends('layouts.front')

@section('pageTitle',$page_title)

@section('js')
	<script src=" {{ url('public/front/scripts') }}/login.js"></script>
@endsection

@section('content')

  <div class="container">
  <div class="centerbolog">
    <h2 class="title hidden-xs">{{ $page_title }}</h2>
    <form method="POST" action="{{ route('login') }}" id = "login-form">

      {{ csrf_field() }}

     
      <div id="alert-message" class="alert alert-success" style="display:{{ session()->has('msg') ? 'block' : 'none' }};margin-top: 20px;">
        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i> 
        <span class="message">
            @if (session()->has('msg'))
            <strong>{{ session('msg') }}</strong>
            @endif
        </span>
    </div>

      <div class="row">
        <div class="col-sm-12 inputbox form-group {{ $errors->has('username') ? ' has-error' : '' }}">
         <input type="text" class="form-control" name="username" placeholder="{{ _lang('app.email') }} - {{ _lang('app.mobile') }}" id ="username">
          <span class="help-block">
             @if ($errors->has('username'))
                {{ $errors->first('username') }}
              @endif
          </span>
        </div>
        <div class="col-sm-12 inputbox form-group {{ $errors->has('password') ? ' has-error' : '' }}">
          <input type="password" name="password" class="form-control" placeholder="{{ _lang('app.password') }}" id ="password">
          <span class="help-block">
             @if ($errors->has('password'))
                 
              {{ $errors->first('password') }}
             
              @endif
          </span>
        </div>
        <div class="col-sm-12 witlab">
          <div class="checkbox checkbox-danger ornig witnone">
            <input id="checkbox8" type="checkbox" name="remember" {{ old('remember') ? 'checked' : ''}}>
            <label for="checkbox8"> {{ _lang('app.remember_me') }} </label>
          </div>
          <a class="cololink" href="{{ route('password.request') }}">{{ _lang('app.forget_password') }}</a> 
        </div>
        <div class="col-sm-12 inputbox merges">
          <button type="submit" class="botoom submit-form">{{ _lang('app.login') }}</button>
        </div>
      </div>
      <!--row-->
      
      <div class="text-center">
        <p class="textor"> {{ _lang('app.you_donot_have_an_account') }} <a href="{{ route('register') }}">{{ _lang('app.register') }}<br/>
        </a> 

      
      </div>
      <div class="text-center">

        <a href="{{ route('login/facebook') }}" class="bac-color"><i class="fa fa-facebook" style="color: white"> </i>{{ _lang('app.sign_in_with_facebook') }} </a>
        
      </div>
    </form>
  </div>
  <!--centerbolog--> 
  
</div>




	
@endsection