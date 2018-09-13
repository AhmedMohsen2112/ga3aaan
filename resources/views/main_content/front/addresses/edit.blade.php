@extends('layouts.user_profile')

@section('pageTitle',$page_title)

@section('title')
  {{ $page_title }}
@endsection

@section('js')
<script src=" {{ url('public/front/scripts') }}/addresses.js"></script>
@endsection


@section('content')



<form action="{{ route('user-addresses.update',Crypt::encrypt($address->id)) }}" method="post" class="form-uecl" id="addEditAddress">

	{{ csrf_field() }}
	<div class="row">
	<input type="hidden" name="id" id="id" value="{{ $address->id }}">
		<div class="col-sm-12 input">
			<div class="row form-group">
				<label class="col-sm-4 control-label"> {{ _lang('app.city') }} </label>
				<div class="col-sm-8">
					<input type="text" name="city" class="form-control" placeholder="" value="{{ $address->city }}" id="city">
				</div>
				 <span class="help-block">
		             @if ($errors->has('city'))
		                {{ $errors->first('city') }}
		              @endif
		         </span>
			</div>
		</div>
		<!--input-->

		<div class="col-sm-12 input">
			<div class="row form-group">
				<label class="col-sm-4 control-label">{{ _lang('app.region') }} </label>
				<div class="col-sm-8">
					<input type="text" name="region" class="form-control" placeholder="" value="{{ $address->region }}" id="region">
				</div>
				<span class="help-block">
		             @if ($errors->has('region'))
		                {{ $errors->first('region') }}
		              @endif
		         </span>
			</div>
		</div>
		<!--input-->

		<div class="col-sm-12 input">
			<div class="row form-group">
				<label class="col-sm-4 control-label"> {{ _lang('app.sub_region') }} </label>
				<div class="col-sm-8">
					<input type="text" name="sub_region" class="form-control" placeholder="" value="{{ $address->sub_region }}" id="sub_region">
				</div>
				<span class="help-block">
		             @if ($errors->has('sub_region'))
		                {{ $errors->first('sub_region') }}
		              @endif
		         </span>
			</div>
		</div>
		<!--input-->

		<div class="col-sm-12 input">
			<div class="row form-group">
				<label class="col-sm-4 control-label"> {{ _lang('app.street') }} </label>
				<div class="col-sm-8">
					<input type="text" name="street" class="form-control" placeholder="" value="{{ $address->street }}" id="street">
				</div>
				<span class="help-block">
		             @if ($errors->has('street'))
		                {{ $errors->first('street') }}
		              @endif
		        </span>
			</div>
		</div>
		<!--input--> 

		<div class="col-sm-12 input">
			<div class="row form-group">
				<label class="col-sm-4 control-label"> {{ _lang('app.building_number') }} </label>
				<div class="col-sm-8">
					<input type="text" name="building_number" class="form-control" placeholder="" value="{{ $address->building_number }}" id="building_number">
				</div>
				<span class="help-block">
		             @if ($errors->has('building_number'))
		                {{ $errors->first('building_number') }}
		              @endif
		        </span>
			</div>
		</div>
		<!--input--> 


		<div class="col-sm-12 input">
			<div class="row form-group">
				<label class="col-sm-4 control-label"> {{ _lang('app.floor_number') }} </label>
				<div class="col-sm-8">
					<input type="text" name="floor_number" class="form-control" placeholder="" value="{{ $address->floor_number }}" id="floor_number">
				</div>
				<span class="help-block">
		             @if ($errors->has('floor_number'))
		                {{ $errors->first('floor_number') }}
		              @endif
		        </span>
			</div>
		</div>
		<!--input--> 

		<div class="col-sm-12 input">
			<div class="row form-group">
				<label class="col-sm-4 control-label"> {{ _lang('app.apartment_number') }} </label>
				<div class="col-sm-8">
					<input type="text" name="apartment_number" class="form-control" placeholder="" value="{{ $address->apartment_number }}" id="apartment_number">
				</div>
				<span class="help-block">
		             @if ($errors->has('apartment_number'))
		                {{ $errors->first('apartment_number') }}
		              @endif
		        </span>
			</div>
		</div>
		<!--input--> 

		<div class="col-sm-12 input">
			<div class="row form-group">
				<label class="col-sm-4 control-label"> {{ _lang('app.special_sign') }} </label>
				<div class="col-sm-8">
					<input type="text" name="special_sign" class="form-control" placeholder="" value="{{ $address->special_sign }}">
				</div>
				<span class="help-block">
		             @if ($errors->has('special_sign'))
		                {{ $errors->first('special_sign') }}
		              @endif
		        </span>
			</div>
		</div>
		<!--input--> 


		<div class="col-sm-12 input">
			<div class="row form-group">
				<label class="col-sm-4 control-label"> {{ _lang('app.extra_info') }} </label>
				<div class="col-sm-8">
					<textarea name="extra_info" class="form-control textarea">{{ $address->extra_info }}</textarea>
				</div>
				<span class="help-block">
		             @if ($errors->has('extra_info'))
		                {{ $errors->first('extra_info') }}
		              @endif
		        </span>
			</div>
		</div>
		<!--input--> 

		


		<div class="col-sm-8 input col-sm-offset-4">
			<button class="botoom submit-form" type="submit"> {{ _lang('app.save') }}</button>
		</div>
	</div>
</form>
@endsection