@extends('layouts.front')

@section('pageTitle',_lang('app.page_not_found'))

@section('js')

@endsection



@section('content')

<div class="container">
  <div class="centerbolog">
	<img src="{{url('public/front/images')}}/logo.png" style="margin:40px auto; display:block;" title="جعان">
    <h2 class="title">نأسف لعدم وصولك لهذة الصفحة</h2>
	
	<p>المحتوى الذي تبحث عنه غير متوفر. من الممكن أن يكون هناك تهجئة خاطئة، أو أنك قمت بالنقر على رابط غير متوفر حالياَ. تأكد من الأمر، أو عد إلى الصفحة الرئيسية.</p>
	
	
        <div class="col-sm-12 inputbox merges">
          <a href="{{_url('resturantes')}}" class="botoom">الرجوع لصفحة المطاعم</a>
        </div>
      </div>
      <!--row-->
     

  </div>




@endsection