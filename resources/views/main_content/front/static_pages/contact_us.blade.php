@extends('layouts.front')

@section('pageTitle',_lang('app.contact_us') )

@section('js')
	<script src=" {{ url('public/front/scripts') }}/contact.js"></script>
@endsection

@section('content')


  <div class="centerbolog">
      <h2 class="title hidden-xs">{{ _lang('app.contact_us') }}</h2>

    <form action="{{ route('contact') }}" method="post" role="form" class="form-search" id="contactus_form">

   <div id="alert-message" class="alert alert-success" style="display:{{ ($errors->has('msg')) ? 'block' : 'none' }};margin-top: 20px;">
        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i> 
        <span class="message">
            @if ($errors->has('msg'))
            <strong>{{ $errors->first('msg') }}</strong>
            @endif
        </span>
    </div>

      {{ csrf_field() }}
      <div class="row">

        <div class="col-sm-12 inputbox form-group">
          <input type="text" name="email" class="form-control" id="email" placeholder=" {{ _lang('app.email') }} ">
          <span class="help-block">
             @if ($errors->has('email'))
                {{ $errors->first('email') }}
              @endif
          </span>
        </div>
 

        <div class="col-sm-12 inputbox selctcon form-group">
          <select name="type" id="type" class="form-control">
            <option value="">{{ _lang('app.type') }}</option>
            <option value="1">{{ _lang('app.message') }}</option>
            <option value="2">{{ _lang('app.complaint') }}</option>
            <option value="3">{{ _lang('app.owner') }}</option>
          </select>
          <span class="help-block">
             @if ($errors->has('type'))
                {{ $errors->first('type') }}
              @endif
          </span>
        </div>

        <div class="col-sm-12 inputbox form-group">
          <input type="text" id="subject" name="subject" class="form-control " placeholder=" {{ _lang('app.subject') }} ">
          <span class="help-block">
             @if ($errors->has('subject'))
                {{ $errors->first('subject') }}
              @endif
          </span>
        </div>

        <div class="col-sm-12 inputbox form-group">
          <textarea class="form-control textarea" id="message" placeholder="{{ _lang('app.message') }} " name="message"></textarea>
          <span class="help-block">
             @if ($errors->has('message'))
                {{ $errors->first('message') }}
              @endif
          </span>
        </div>

        <div class="col-sm-12 inputbox merges">
          <button type="button" class="botoom submit-form">{{ _lang('app.send') }}</button>
        </div>
      </div>
      <!--row-->
      
    </form>
  </div>



	
@endsection