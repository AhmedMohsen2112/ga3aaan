@extends('layouts.backend')

@section('pageTitle', _lang('app.Coupons'))

@section('js')
<script src="{{url('public/backend/js')}}/coupons.js" type="text/javascript"></script>

<script type="text/javascript">
    $(document).ready(function () {

        $("#datetimepicker").datetimepicker({ autoclose: true,  format: "yyyy-mm-dd", pickerPosition: "bottom-left",minView: 2 });
    });
</script>

@endsection
@section('content')

<form role="form"  id="addCouponsForm"  enctype="multipart/form-data">
        {{ csrf_field() }}
    <div class="panel panel-default" id="addCoupons">
        <div class="panel-heading">
            <h3 class="panel-title">{{_lang('app.coupons') }}</h3>
        </div>
        <div class="panel-body">
            <div class="form-body">

                <div class="form-group form-md-line-input col-md-6">
                    <input type="text" class="form-control" id="coupon" name="coupon" value="">
                    <label for="coupon">{{_lang('app.coupon') }}</label>
                    <span class="help-block"></span>
                </div>

               <div class="form-group form-md-line-input col-md-6 {{ $errors->has('date') ? ' has-error' : '' }} ">

                <input type='text' class="form-control" id='datetimepicker'  value="{{ !empty($appointment) ? $appointment->date : old('date') }}" name="available_until"  />
                <label class="control-label" >{{_lang('app.available_until')}}</label>
                 <span class="help-block"></span>
            </div>
               
                <div class="form-group form-md-line-input col-md-6">
                    <input type="number" class="form-control" id="discount" name="discount" value="">
                    <label for="discount_percentage">{{_lang('app.discount_percentage') }}</label>
                    <span class="help-block"></span>
                </div>

                <div class="form-group form-md-line-input col-md-6">
                        <select class="form-control edited" id="resturant_id" name="resturant_id">
                                <option value="">{{ _lang('app.choose') }}</option>
                                
                                @foreach ($resturantes as $resturante)

                                 <option value="{{ $resturante->id }}">{{ $resturante->title }}</option>
                
                                @endforeach
                                   

                        </select>

                </div>

                <div class="form-group form-md-line-input col-md-6">
                        <label class="col-sm-3 inputbox utbox control-label">{{_lang('app.branches')}}</label>
                        <div class="col-sm-9 inputbox">
                            <select class="form-control" name="resturant_branch" id="branch">
                                <option value="">{{_lang('app.choose')}}</option>
                            </select>
                        </div>
                </div>
             
                
                <div class="clearfix"></div>

            </div>
            <!--Table Wrapper Finish-->
        </div>
         <div class="panel-footer text-center">
            <button type="button" class="btn btn-info submit-form"
                    >{{_lang('app.save') }}</button>
        </div>

    </div>

</form>

<div class = "panel panel-default">
    <div class = "panel-heading">
        <a class="panel-title data-box" data-where="outTable"  data-id="0" data-level="0">{{_lang('app.all')}}</a>
    </div>
    <div class = "panel-body">
        <!--Table Wrapper Start-->
        <table class = "table table-striped table-bordered table-hover table-checkable order-column dataTable no-footer">
            <thead>
                <tr>
                    <th>{{_lang('app.coupon')}}</th>
                    <th>{{_lang('app.resturant')}}</th>
                    <th>{{_lang('app.resturant_branch')}}</th>
                    <th>{{_lang('app.expiration_date')}}</th>
                    <th>{{_lang('app.discount_percentage')}}</th>
                    <th>{{_lang('app.options')}}</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>

        <!--Table Wrapper Finish-->
    </div>
</div>

@endsection