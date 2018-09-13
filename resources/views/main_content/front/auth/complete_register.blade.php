@extends('layouts.front')

@section('pageTitle',_lang('app.complete_register'))

@section('js')
	<script src=" {{ url('public/front/scripts') }}/login.js"></script>
@endsection

@section('content')

 <div class="container">
  <div class="centerbolog">
    <h2 class="title hidden-xs">{{ _lang('app.complete_register') }}</h2>
    <form action="{{ route('register') }}" id="register-form" method="post">
      {{ csrf_field() }}

    <div id="alert-message" class="alert alert-success" style="display:{{ (session()->has('msg')) ? 'block' : 'none' }};margin-top: 20px;">
        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i> 
        <span class="message">
            @if (session()->has('msg'))
            <strong>{{ session('msg') }}</strong>
            @endif
        </span>
    </div>

      <div class="row">

        
          <input type="hidden" class="form-control" name="first_name" id="first_name" value="{{ session()->get('ga3aaan_first_name') }}">
        
          <input type="hidden" class="form-control" name="last_name" id="last_name" value="{{ session()->get('ga3aaan_last_name') }}">
        
          <input type="hidden" class="form-control " name="email" id="email"  value="{{ session()->get('ga3aaan_email') }}">
       

        <div class="col-sm-12 inputbox merges form-group {{ $errors->has('mobile') ? ' has-error' : '' }}">
          <input type="text" class="form-control " name="mobile" id="mobile" placeholder="{{ _lang('app.mobile') }}">
           <span class="help-block">
             @if ($errors->has('mobile'))
                {{ $errors->first('mobile') }}
              @endif
          </span>
        </div>

        <div class="col-sm-12 inputbox merges form-group {{ $errors->has('password') ? ' has-error' : '' }}">
          <input type="password" class="form-control" name="password" id="password" placeholder="{{ _lang('app.password') }}">
           <span class="help-block">
             @if ($errors->has('password'))
                {{ $errors->first('password') }}
              @endif
          </span>
        </div>

        <div class="col-sm-12 inputbox merges form-group {{ $errors->has('confirm_password') ? ' has-error' : '' }}">
          <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="{{ _lang('app.confirm_password') }}">
           <span class="help-block">
             @if ($errors->has('confirm_password'))
                {{ $errors->first('confirm_password') }}
              @endif
          </span>
        </div>

        <div class="col-sm-12 witlab">
          <div class="checkbox checkbox-danger ornig">
            <input id="checkbox1" type="checkbox" name="email_notify" value="1">
            <label for="checkbox1"> {{ _lang('app.subscribe_to_the_e-mail_service') }} </label>
          </div>
          <div class="checkbox checkbox-danger ornig">
            <input id="checkbox2" type="checkbox" name="sms_notify" value="1">
            <label for="checkbox2"> {{ _lang('app.subscribe_to_the_sms_service') }} </label>
          </div>
          <div class="checkbox checkbox-danger ornig form-group">
            <input id="checkbox3" type="checkbox" name="conditions">
            <label for="checkbox3"> {{ _lang('app.approve_on') }} <a href="{{ route('terms_conditions') }}" target="_blank" class="colin">{{ _lang('app.the_terms_and_conditions') }}</a> </label>
             <span class="help-block">
             @if ($errors->has('conditions'))
                {{ $errors->first('conditions') }}
              @endif
          </span>

          </div>
        </div>
        <div class="col-sm-12 inputbox merges form-group">
          <button type="submit" class="botoom submit-form">{{ _lang('app.register') }}</button>
        </div>
      </div>
      <!--row--> 
    </form>
  </div>
  <!--centerbolog--> 
  
</div>




	
@endsection