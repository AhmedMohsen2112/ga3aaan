<div id="newMessageModal" class="modal fade in" role="dialog" aria-hidden="false" >
        <div class="modal-dialog"> 
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn btn-default cols" data-dismiss="modal">×</button>
                    <h4 class="modal-title titlpop">{{_lang('app.message')}}</h4>
                </div>
                <div class="modal-body">
                    <p class="text-center bothenk message-content"></p>
                </div>
            </div>
        </div>
    </div>
<footer class="footer">
  <div class="container">
    <div class="row">
      <div class="col-sm-3 boxfot">
        <h6 class="title-fot">جعان</h6>
        <p class="textabut" style="max-height: 100px; overflow: hidden;">{{ $settings->about_us }}</p>
        <a href="{{ route('about_us') }}" class="temore">{{ _lang('app.more') }}</a>
      </div>
      <!--boxfot-->
      
      <div class="col-sm-3 boxfot">
        <h6 class="title-fot">اسماء   مطاعم </h6>
        <nav class="lastmenu"> 
            @foreach($resturantes_footer as $one)
            <a href="{{_url('resturant/'.$one->slug)}}"><i class="fa fa-angle-left" aria-hidden="true"></i> {{$one->title}}</a> 
            @endforeach
        </nav>
      </div>
      <!--boxfot-->
      
      <div class="col-sm-3 boxfot">
        <h6 class="title-fot">انواع المطاعم</h6>
        <nav class="lastmenu"> 
            @foreach($cuisines_footer as $one)
            <a href="{{_url('resturantes/cuisines/'.$one->slug)}}"><i class="fa fa-angle-left" aria-hidden="true"></i> {{$one->title}}</a> 
            @endforeach
        </nav>
      </div>
      <!--boxfot-->
      
      <div class="col-sm-3 boxfot">
        <h6 class="title-fot">{{ _lang('app.communicate_with_us') }}</h6>
        <nav class="lastmenu"> 

          <a href="{{ route('terms_conditions') }}"><i class="fa fa-angle-left" aria-hidden="true"></i> {{ _lang('app.terms_and_conditions') }} </a> 

          <a href="{{ route('usage_conditions') }}"><i class="fa fa-angle-left" aria-hidden="true"></i>  {{ _lang('app.usage_conditions') }} </a>

          <a href="{{ route('contact_us') }}"><i class="fa fa-angle-left" aria-hidden="true"></i>  {{ _lang('app.contact_us') }}</a></nav>

        <nav class="boxicon icfot"> 
          @php
            $social = json_decode($settings->social_media);
          @endphp
           
    
          <a href="{{ $social->facebook }}" class="fa fa-facebook fac-flw" title="فيس بوك"></a>
          <a href="{{ $social->twitter }}" class="fa fa-twitter twi-flw" title="تويتر"></a>
          <a href="{{ $social->google }}" class="fa fa-google-plus plus-flw" title="جوجل بلاس"></a>
          <a href="{{ $social->instagram }}" class="fa fa-instagram inst-flw" title="انستجرام"> </a>
          <a href="{{ $social->linkedin }}" class="fa fa-youtube youtube-flw" title="يوتيوب"></a> </nav>
      </div>
      <!--boxfot--> 
      
    </div>
    <!--row-->
    
    <div class="bwerpy">
      <p>جميع الحقوق محفوظة © 2018 جعان دوت كوم</p>
      <a href="http://www.atiafco.com/" target="_blank">تصميم وتطوير اطياف للحلول البرمجية</a> </div>
  </div>
  <!--//.container--> 
  
  <a href="#" class="fa fa-angle-double-up scrollToTop"></a> 
</footer>

