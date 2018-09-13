
<div class="panel panel-default" id="editOtherSettings">
    <div class="panel-heading">
        <h3 class="panel-title">{{ _lang('admin_settings')}}</h3>
    </div>
    <div class="panel-body">
        <form role="form"  id="editOtherSettingsForm"  enctype="multipart/form-data">
            {{ csrf_field() }}
            <input type="hidden" name="id" id="id" value="{{$admin_settings->id}}">


            <div class="form-group form-md-line-input col-md-4">
                <select class="form-control" id="language" name="language">
                    @foreach ($languages as $one)
                        @php 
                        $selected = ($one->code == $admin_settings->language) ? 'selected' : '' 
                        @endphp
                        <option {{$selected}} value="{{$one->code}}">{{$one->title}}</option>
                   @endforeach
                </select>
                <label for="language">{{ _lang('app.language')}}</label>
            </div>
            <div class="clearfix"></div>







        </form>

        <!--Table Wrapper Finish-->
    </div>
    <div class="panel-footer text-center">
        <button type="button" class="btn btn-info submit-form"
                >{{_lang('app.save')}}</button>
    </div>
</div>
<script>
    var new_lang = {
        messages: {
            language: {
                required: lang.required

            },
        }
    };
</script>


<?php
global $_require;
$_require['js'] = array('admin_settings.js');
?>