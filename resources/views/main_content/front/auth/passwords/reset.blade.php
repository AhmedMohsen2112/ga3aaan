@extends('layouts.front')

@section('pageTitle',$page_title)

@section('js')
  <script src=" {{ url('public/front/scripts') }}/login.js"></script>
@endsection

@section('content')

<div class="container">
  <h2 class="title hidden-xs">{{ _lang('app.reset_password') }}</h2>
    
  <div class="centerbolog">
    <form class="text-in" method="POST" action="{{ route('password.request') }}" id="changePasswordForm">
      {{ csrf_field() }}
       <input type="hidden" name="token" value="{{ $token }}">

      <div id="alert-message" class="alert alert-success" style="display:{{ ($errors->has('msg')) ? 'block' : 'none' }};margin-top: 20px;">
        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i> 
        <span class="message">
            @if ($errors->has('msg'))
            <strong>{{ $errors->first('msg') }}</strong>
            @endif
        </span>
    </div>

      <div class="row">
      <div class="col-sm-12 inputbox form-group {{ $errors->has('email') ? 'has-error' : '' }}">
        <input type="email" class="form-control" name="email" placeholder="{{ _lang('app.email') }}"  id="email">

        <span class="help-block">
             @if ($errors->has('email')) 
              {{ $errors->first('email') }}
              @endif
        </span>

      </div>

       <div class="col-sm-12 inputbox form-group {{ $errors->has('password') ? ' has-error' : '' }}">
        <input type="password" class="form-control" name="password" placeholder="{{ _lang('app.password') }}"  id="password">

        <span class="help-block">
             @if ($errors->has('password')) 
                {{ $errors->first('password') }}
              @endif
        </span>

      </div>

       <div class="col-sm-12 inputbox form-group {{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
        <input type="password" class="form-control" name="password_confirmation" placeholder="{{ _lang('app.password_confirmation') }}" id="password_confirmation">

        <span class="help-block">
             @if ($errors->has('password_confirmation')) 
              {{ $errors->first('password_confirmation') }}
              @endif
        </span>

      </div>
      <div class="col-sm-12 inputbox mapx">
        <button type="submit" class="botoom submit-form">
        {{ _lang('app.reset_password') }}</button>
      </div>
      </div>
      <!--row-->
    </form>
  </div>
  
</div>




  
@endsection