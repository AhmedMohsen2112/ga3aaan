@extends('layouts.user_profile')

@section('pageTitle',$page_title)

@section('title')
{{ $page_title }}
@endsection

@section('content')
	 <ul class="nameprofile">
	 	@php
			$image = $User->user_image ? $User->user_image : 'default.png';
		@endphp

	 	 <div class="imgprofile"><img src="{{ url('public/uploads/users/'.$image)}}"></div>
				<li><strong>{{ _lang('app.first_name') }} : </strong>
				  <p> {{ $User->first_name }} </p>
				</li>
				<li><strong>{{ _lang('app.last_name') }} :</strong>
				  <p> {{ $User->last_name }}</p>
				</li>
				<li><strong>{{ _lang('app.email') }} :</strong>
				  <p> {{ $User->email }}</p>
				</li>
				<li><strong>{{ _lang('app.mobile') }} :</strong>
				  <p>{{ $User->mobile }}</p>
				</li>
	</ul>
@endsection