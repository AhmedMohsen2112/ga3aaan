@extends('layouts.backend')

@section('pageTitle',_lang('app.edit_offer'))


@section('js')
<script src="{{url('public/backend/js')}}/offers.js" type="text/javascript"></script>
@endsection
@section('content')
<form role="form"  id="addEditOffersForm" enctype="multipart/form-data">
    {{ csrf_field() }}

    <div class="panel panel-default" id="addEditOffers">
        <div class="panel-heading">
            <h3 class="panel-title">{{_lang('app.Offer') }}</h3>
        </div>
        <div class="panel-body">


            <div class="form-body">
                <input type="hidden" name="id" id="id" value="{{ $offer->id }}">


                <div class="form-group form-md-radios col-md-12">


                    <div class="md-radio col-md-3">
                        <input type="radio" id="resturant" name="type" class="md-radiobtn" value= "1" {{ $offer->type == 1 ? 'checked' : '' }}>
                        <label for="resturant" >
                            <span class="inc"></span>
                            <span class="check"></span>
                            <span class="box"></span>{{ _lang('app.resturant') }} </label>
                    </div>
                    <div class="md-radio col-md-3">
                        <input type="radio" id="all_except" name="type" class="md-radiobtn" value= "2" {{ $offer->type == 2 ? 'checked' : '' }}>
                        <label for="all_except">
                            <span class="inc"></span>
                            <span class="check"></span>
                            <span class="box"></span> {{ _lang('app.all_except') }} </label>
                    </div>
                    <div class="md-radio col-md-3">
                        <input type="radio" id="specific_menu_sections" name="type" class="md-radiobtn" value="3" {{ $offer->type == 3 ? 'checked' : '' }}>
                        <label for="specific_menu_sections">
                            <span class="inc"></span>
                            <span class="check"></span>
                            <span class="box"></span> {{ _lang('app.specific_menu_sections') }} </label>
                    </div>
                    <div class="md-radio col-md-3">
                        <input type="radio" id="written_only" name="type" class="md-radiobtn" value="4" {{ $offer->type == 4 ? 'checked' : '' }}>
                        <label for="written_only">
                            <span class="inc"></span>
                            <span class="check"></span>
                            <span class="box"></span> {{ _lang('app.written_only') }} </label>
                    </div>


                </div>

                <div class="form-group form-md-line-input col-md-4">
                    <select class="form-control edited" id="resturnantes" name="resturant">
                        <option value="">{{ _lang('app.select') }}</option>
                        @foreach ($resturantes as $resturant)
                        <option {{ $offer->resturant_id == $resturant->id ? 'selected' : '' }}  value="{{ $resturant->id }}">{{ $resturant->title }}</option>
                        @endforeach 
                    </select>
                    <label>{{ _lang('app.resturant') }}</label>
                    <span class="help-block"></span>
                </div>


                <div class="form-group form-md-checkboxes col-md-6" id="menu_sections" {{ $offer->type == 2 || $offer->type == 3 ? 'style="display:none;"':'' }}>

                    <label>{{ _lang('app.menu_sections') }}</label>
                    <div class="md-checkbox-inline" id="menu_section">

                        @foreach ($menu_sections as $menu_section) 
                        @php
                        $s_open_id = $menu_section->title . '_' . $menu_section->id;
                        @endphp
                        <div class="md-checkbox has-success">

                            <input type="checkbox" {{in_array($menu_section->id,$offer_menu_sections) ?'checked':''}} id="{{ $s_open_id }}" name="menu_sections[]" value="{{ $menu_section->id }}" class="md-check">
                            <label for="{{ $s_open_id }}">
                                <span class="inc"></span>
                                <span class="check"></span>
                                <span class="box"></span>{{ $menu_section->title }} </label>
                        </div>
                        @endforeach

                    </div>
                    <div class="clearfix"></div>
                    <span class="help-block"></span>

                </div>
                <div class="clearfix"></div>

                <div class="form-group form-md-line-input col-md-3">
                    <input type="date" class="form-control" id="available_until" name="available_until" value="{{ $offer->available_until }}">
                    <label for="available_until">{{_lang('app.available_until') }}</label>
                    <span class="help-block"></span>
                </div>

                <div class="form-group form-md-line-input col-md-3">
                    <input type="number" class="form-control" id="this_order" name="this_order" value="{{ $offer->this_order }}">
                    <label for="this_order">{{_lang('app.this_order') }}</label>
                    <span class="help-block"></span>
                </div>

                <div class="form-group form-md-line-input col-md-3" id="discount_v">
                    <input type="number" class="form-control" id="discount" name="discount" value="{{ $offer->discount }}">
                    <label for="discount">{{_lang('app.discount') }}</label>
                    <span class="help-block"></span>
                </div>




                <div class="form-group form-md-line-input col-md-3">
                    <select class="form-control edited" id="active" name="active">
                        <option {{ $offer->active == true ? 'selected' : '' }} value="1">{{ _lang('app.active') }}</option>
                        <option {{ $offer->active == false ? 'selected' : '' }} value="0">{{ _lang('app.not_active') }}</option>
                    </select>
                    <span class="help-block"></span>
                </div> 
                <div class="form-group col-md-4">
                    <label class="control-label">{{_lang('app.image')}}</label>     
                    <div class="image_box">
                        <img src="{{url('public/uploads/offers/'.$offer->image)}}" alt="resturant image"}}" width="100" height="80" class="image" />
                    </div>
                    <input type="file" name="image" id="image" style="display:none;">     
                    <span class="help-block"></span>             
                </div>

            </div>

            <!--Table Wrapper Finish-->
        </div>
        <div class="panel-footer text-center">
            <button type="button" class="btn btn-info submit-form"
                    >{{_lang('app.save') }}</button>
        </div>

    </div>


</form>
@endsection