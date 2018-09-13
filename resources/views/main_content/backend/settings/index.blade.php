@extends('layouts.backend')

@section('pageTitle',_lang('app.settings'))


@section('js')
<script src="{{url('public/backend/js')}}/settings.js" type="text/javascript"></script>
@endsection
@section('content')
    <form role="form"  id="editSettingsForm"  enctype="multipart/form-data">
        {{ csrf_field() }}
    <input type="hidden" name="id" id="id" value="{{ $settings->id }}">
    <div class="panel panel-default" id="editSiteSettings">
        <div class="panel-heading">
            <h3 class="panel-title">{{_lang('app.settings') }}</h3>
        </div>
        <div class="panel-body">


            <div class="form-body">


                <div class="form-group form-md-line-input col-md-6">
                    <input type="text" class="form-control" id="title_ar" name="title_ar" value="{{ $settings->title_ar}}">
                    <label for="title_ar">{{_lang('app.title_ar') }}</label>
                    <span class="help-block"></span>
                </div>
                <div class="form-group form-md-line-input col-md-6">
                    <input type="text" class="form-control" id="title_en" name="title_en" value="{{ $settings->title_en}}">
                    <label for="title_en">{{_lang('app.title_en') }}</label>
                    <span class="help-block"></span>
                </div>
                <div class="form-group form-md-line-input col-md-6">
                    <input type="text" class="form-control" id="address_ar" name="address_ar" value="{{ $settings->address_ar}}">
                    <label for="address_ar">{{_lang('app.address_ar') }}</label>
                    <span class="help-block"></span>
                </div>
                <div class="form-group form-md-line-input col-md-6">
                    <input type="text" class="form-control" id="address_en" name="address_en" value="{{ $settings->address_en }}">
                    <label for="address_en">{{_lang('app.address_en') }}</label>
                    <span class="help-block"></span>
                </div>
                <div class="form-group form-md-line-input col-md-6">
                    <input type="number" class="form-control" id="phone" name="phone" value="{{ $settings->phone }}">
                    <label for="phone">{{_lang('app.phone') }}</label>
                    <span class="help-block"></span>
                </div>
                <div class="form-group form-md-line-input col-md-6">
                    <input type="text" class="form-control" id="email" name="email" value="{{ $settings->email }}">
                    <label for="email">{{_lang('app.email') }}</label>
                    <span class="help-block"></span>
                </div>
                <div class="form-group form-md-line-input col-md-6">
                    <input type="text" class="form-control" id="android_url" name="android_url" value="{{ $settings->android_url }}">
                    <label for="android_url">{{_lang('app.android_url') }}</label>
                    <span class="help-block"></span>
                </div>
                <div class="form-group form-md-line-input col-md-6">
                    <input type="text" class="form-control" id="ios_url" name="ios_url" value="{{ $settings->ios_url }}">
                    <label for="ios_url">{{_lang('app.ios_url') }}</label>
                    <span class="help-block"></span>
                </div>
                <div class="clearfix"></div>




            </div>




            <!--Table Wrapper Finish-->
        </div>

    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">{{_lang('app.about_us') }}</h3>
        </div>
        <div class="panel-body">

            <div class="form-body">
       
                <div class="form-group form-md-line-input col-md-6">
                    <textarea rows="5" class="form-control" name="about_us_ar" id="about_us_ar">{{ $settings->about_us_ar }}</textarea>
                    <label for="about_us_ar">{{_lang('app.about_us_ar') }}</label>
                    <span class="help-block"></span>
                </div>
                <div class="form-group form-md-line-input col-md-6">
                    <textarea rows="5" class="form-control" name="about_us_en" id="about_us_en">{{ $settings->about_us_en }}</textarea>
                    <label for="about_us_en">{{_lang('app.about_us_en') }}</label>
                    <span class="help-block"></span>
                </div>
            </div>




            <!--Table Wrapper Finish-->
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">{{_lang('app.terms_conditions') }}</h3>
        </div>
        <div class="panel-body">

            <div class="form-body">
                <div class="form-group form-md-line-input col-md-6">
                    <textarea rows="5" class="form-control" name="terms_conditions_ar" id="terms_conditions_ar">{{ $settings->terms_conditions_ar }}</textarea>
                    <label for="terms_conditions_ar">{{_lang('app.terms_conditions_ar') }}</label>
                    <span class="help-block"></span>
                </div>
                <div class="form-group form-md-line-input col-md-6">
                    <textarea rows="5" class="form-control" name="terms_conditions_en" id="terms_conditions_en">{{ $settings->terms_conditions_en }}</textarea>
                    <label for="terms_conditions_en">{{_lang('app.terms_conditions_en') }}</label>
                    <span class="help-block"></span>
                </div>
            </div>




            <!--Table Wrapper Finish-->
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">{{_lang('app.usage_conditions') }}</h3>
        </div>
        <div class="panel-body">

            <div class="form-body">
                <div class="form-group form-md-line-input col-md-6">
                    <textarea rows="5" class="form-control" name="usage_conditions_ar" id="usage_conditions_ar">{{ $settings->usage_conditions_ar }}</textarea>
                    <label for="usage_conditions_ar">{{_lang('app.usage_conditions_ar') }}</label>
                    <span class="help-block"></span>
                </div>
                <div class="form-group form-md-line-input col-md-6">
                    <textarea rows="5" class="form-control" name="usage_conditions_en" id="usage_conditions_en">{{ $settings->usage_conditions_en }}</textarea>
                    <label for="usage_conditions_en">{{_lang('app.usage_conditions_en') }}</label>
                    <span class="help-block"></span>
                </div>
            </div>




            <!--Table Wrapper Finish-->
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">{{_lang('app.social_media')}}</h3>
        </div>
        <div class="panel-body">

            <div class="form-body">
                <div class="form-group form-md-line-input col-md-6">
                    <input type="text" class="form-control" name="social_media[facebook]" value="{{ isset($settings->social_media->facebook) ? $settings->social_media->facebook :'' }}">
                    <label for="social_media_facebook">{{_lang('app.facebook') }}</label>
                    <span class="help-block"></span>
                </div>
                <div class="form-group form-md-line-input col-md-6">
                    <input type="text" class="form-control" name="social_media[twitter]" value="{{ isset($settings->social_media->twitter) ? $settings->social_media->twitter :'' }}">
                    <label for="social_media_twitter">{{_lang('app.twitter') }}</label>
                    <span class="help-block"></span>
                </div>
                <div class="form-group form-md-line-input col-md-6">
                    <input type="text" class="form-control" name="social_media[instagram]" value="{{ isset($settings->social_media->instagram) ? $settings->social_media->instagram :'' }}">
                    <label for="social_media_instagram">{{_lang('app.instagram') }}</label>
                    <span class="help-block"></span>
                </div>
                <div class="form-group form-md-line-input col-md-6">
                    <input type="text" class="form-control" name="social_media[google]" value="{{ isset($settings->social_media->google) ? $settings->social_media->google :'' }}">
                    <label for="social_media_google">{{_lang('app.google') }}</label>
                    <span class="help-block"></span>
                </div>
                <div class="form-group form-md-line-input col-md-6">
                    <input type="text" class="form-control" name="social_media[linkedin]" value="{{ isset($settings->social_media->linkedin) ? $settings->social_media->linkedin :'' }}">
                    <label for="social_media_linkedin">{{_lang('app.youtube') }}</label>
                    <span class="help-block"></span>
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
@endsection