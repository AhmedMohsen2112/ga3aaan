@extends('layouts.backend')

@section('pageTitle',_lang('app.edit_resturant_branch'))


@section('js')
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDWYbhmg32SNq225SO1jRHA2Bj6ukgAQtA&libraries=places&language={{App::getLocale()}}"></script>

<script src="{{url('public/backend/js')}}/map.js" type="text/javascript"></script>

<script src="{{url('public/backend/js')}}/resturant_branches.js" type="text/javascript"></script>
@endsection
@section('content')

<form role="form"  id="addEditResturantBranchesForm" enctype="multipart/form-data">
    {{ csrf_field() }}

    <div class="panel panel-default" id="addEditResturantBranches">
        <div class="panel-heading">
            <h3 class="panel-title">{{_lang('app.branch_info') }}</h3>
        </div>
        <div class="panel-body">


            <div class="form-body">

                <input type="hidden" name="id" id="id" value="{{ $resturant_branch->id }}">
                <input type="hidden" name="resturant" id="resturant" value="{{ $resturant->id }}">

                <div class="form-group form-md-line-input col-md-6">
                    <input type="text" class="form-control" id="title_ar" name="title_ar" value="{{ $resturant_branch->title_ar }}">
                    <label for="title_ar">{{_lang('app.title_ar') }}</label>
                    <span class="help-block"></span>
                </div>
                
                <div class="form-group form-md-line-input col-md-6">
                    <input type="text" class="form-control" id="title_en" name="title_en" value="{{ $resturant_branch->title_en }}">
                    <label for="title_en">{{_lang('app.title_en') }}</label>
                    <span class="help-block"></span>
                </div>

   
                 <div class = "form-group form-md-line-input col-md-3">
                            <select class = "form-control edited" id = "city" name = "city">
                                <option value="">{{ _lang('app.choose') }}</option>
                                @foreach ($cities as $city)
                                <option value ="{{ $city->id }}" {{ $city->id == $resturant_branch->city_id ? 'selected' : '' }}>{{ $city->title}}</option>
                                @endforeach

                            </select>
                            <label for="city">{{_lang('app.city')}}</label>

                </div>
                <div class = "form-group form-md-line-input col-md-3">
                            <select class = "form-control edited" id = "region" name = "region">
                                <option value="">{{ _lang('app.choose') }}</option>
                                 @foreach ($regions as $region)
                                <option value ="{{ $region->id }}" {{ $region->id == $resturant_branch->region_id ? 'selected' : '' }}>{{ $region->title}}</option>
                                @endforeach

                            </select>
                            <label for="region">{{_lang('app.region')}}</label>

                </div>

                <div class="form-group form-md-line-input col-md-3">
                    <select class="form-control edited" id="active" name="active">
                        <option {{ $resturant_branch->active == 1 ? 'selected' : '' }} value="1">{{ _lang('app.active') }}</option>
                        <option  {{ $resturant_branch->active == 0 ? 'selected' : '' }} value="0">{{ _lang('app.not_active') }}</option>
                    </select>
                     <label for="status">{{_lang('app.status') }}</label>
                    <span class="help-block"></span>
                </div> 


                <input value="{{ $resturant_branch->lat }}" type="hidden" id="lat" name="lat">
                <input value="{{ $resturant_branch->lng }}" type="hidden" id="lng" name="lng">
                    <span class="help-block utbox"></span>
                <div class="maplarger">
                            <input id="pac-input" class="controls" type="text"
                                   placeholder="Enter a location">
                            <div id="map" style="height: 500px; width:100%;"></div>
                            <div id="infowindow-content">
                                <span id="place-name"  class="title"></span><br>
                                <span id="place-address"></span>
                            </div>
                </div>


            </div>

            <!--Table Wrapper Finish-->
        </div>

    </div>
    <div class="panel panel-default">

        <div class="panel-heading">
            <h3 class="panel-title">{{_lang('app.delivery_places') }}</h3>
        </div>
        <div class="panel-body">
            <a class="btn btn-primary add-delivery-place">{{_lang('app.add')}}</a>

            <div class="form-body">

                <div class="table-scrollable" style="border:none;"> 
                    <table class="table" id="delivery-places-table">
                        <tbody>

                        @php $count=0 @endphp

                            @foreach($branch_delivery_places as $branch_delivery_place)
                            <tr class="delivery-one">
                                <td>
                                    <select class="form-control edited" name="delivery_places[{{ $count }}][region_id]">
                                        @foreach ($regions as $region)
                                        <option {{ $region->id == $branch_delivery_place->region_id ? 'selected' : '' }} value="{{ $region->id }}">{{ $region->title }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-filter input-sm" style="width:25%;" name="delivery_places[{{ $count }}][delivery_cost]" value="{{ $branch_delivery_place->delivery_cost }}">
                                </td>
                                <td>
                                    <a class="btn btn-danger remove-delivery-place">{{_lang('app.remove')}}</a>
                                </td>
                            </tr>
                            @php $count++ @endphp
                            @endforeach            

                        </tbody>
                    </table>
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
<script>
var new_lang = {

};
var new_config = {
   regions : '{!!$regions!!}',
   city : '{{ $resturant_branch->city_id }}'
}
</script>
@endsection