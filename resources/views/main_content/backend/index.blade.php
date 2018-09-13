@extends('layouts.backend')

@section('pageTitle', _lang('app.dashboard'))



@section('content')
    <p>{{ Auth::guard('admin')->user()->username }}</p>
@endsection