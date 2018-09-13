@extends('layouts.user_profile')

@section('pageTitle',$page_title)

@section('title')
  {{ $page_title }}
@endsection


@section('js')
	<script src=" {{ url('public/front/scripts') }}/users.js"></script>
@endsection


@section('content')



<form action="{{ route('update_user') }}" method="post" class="form-uecl" id="editProfile">


	{{ csrf_field() }}
	<div class="row">


		<div class="form-group col-md-6 col-md-offset-4">
            @php
				$image = $User->user_image ? $User->user_image : 'default.png';
			@endphp
            <div class="user_image_box">
                <img src="{{ url('public/uploads/users/'.$image)}}" alt="user image"}}" width="100" height="80" class="user_image" />
            </div>
            <input type="file" name="user_image" id="user_image" style="display:none;">     
             <span class="help-block"></span>             
        </div>

		<div class="col-sm-12">
			<div class="row form-group">
				<label class="col-sm-4 control-label"> {{ _lang('app.first_name') }} </label>
				<div class="col-sm-8">
					<input type="text" name="first_name" class="form-control" placeholder="" value="{{ $User->first_name }}" id="first_name">
				</div>
				 <span class="help-block">
		             @if ($errors->has('first_name'))
		                {{ $errors->first('first_name') }}
		              @endif
		         </span>
			</div>
		</div>
		<!--input-->

		<div class="col-sm-12 input">
			<div class="row form-group">
				<label class="col-sm-4 control-label">{{ _lang('app.last_name') }} </label>
				<div class="col-sm-8">
					<input type="text" name="last_name" class="form-control" placeholder="" value="{{ $User->last_name }}" id="last_name">
				</div>
				<span class="help-block">
		             @if ($errors->has('last_name'))
		                {{ $errors->first('last_name') }}
		              @endif
		         </span>
			</div>
		</div>
		<!--input-->

		<div class="col-sm-12 input">
			<div class="row form-group">
				<label class="col-sm-4 control-label"> {{ _lang('app.email') }} </label>
				<div class="col-sm-8">
					<input type="email" name="email" class="form-control" placeholder="" value="{{ $User->email }}" id="email">
				</div>
				<span class="help-block">
		             @if ($errors->has('email'))
		                {{ $errors->first('email') }}
		              @endif
		         </span>
			</div>
		</div>
		<!--input-->

		<div class="col-sm-12 input">
			<div class="row form-group">
				<label class="col-sm-4 control-label"> {{ _lang('app.mobile') }} </label>
				<div class="col-sm-8">
					<input type="number" name="mobile" class="form-control" placeholder="" value="{{ $User->mobile }}" id="mobile">
				</div>
				<span class="help-block">
		             @if ($errors->has('mobile'))
		                {{ $errors->first('mobile') }}
		              @endif
		        </span>
			</div>
		</div>
		<!--input--> 

		


		<div class="col-sm-12 input">
			<div class="row form-group">
				<label class="col-sm-4 control-label">{{ _lang('app.password') }}</label>
				<div class="col-sm-8">
					<input type="password" name="password" class="form-control" placeholder="" id="password">
				</div>
				<span class="help-block">
		             @if ($errors->has('password'))
		                {{ $errors->first('password') }}
		              @endif
		         </span>
			</div>
		</div>
		<!--input-->

		<div class="col-sm-12 input">
			<div class="row form-group">
				<label class="col-sm-4 control-label">{{ _lang('app.confirm_password') }}</label>
				<div class="col-sm-8">
					<input type="password" name="confirm_password" class="form-control" placeholder="" id="confirm_password">
				</div>
				<span class="help-block">
		             @if ($errors->has('confirm_password'))
		                {{ $errors->first('confirm_password') }}
		              @endif
		         </span>
			</div>
		</div>
		<!--input-->

		<div class="col-sm-8 input col-sm-offset-4">
			<button class="botoom submit-form" type="submit"> {{ _lang('app.edit') }}</button>
		</div>
	</div>
</form>
@endsection