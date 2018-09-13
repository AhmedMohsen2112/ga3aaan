<header class="header"> 
  
  <div class="container">
    <div class="logo"> <a href="{{_url('')}}"><img src="{{url('public/front/images')}}/logo.png" title="جعان"></a> </div>
    <div class="title-header hidden-lg hidden-md  hidden-sm">
		<h3>{{$page_title}}</h3>
	</div>

  
    <div id='cssmenu'>
      <div id="head-mobile"></div>
      @yield('cart')
	  
      <div class="button"></div>
      <ul>
        <li><a href="{{ route('home') }}" class="{{$page_link_name==''?'active':''}}">{{ _lang('app.home') }} </a></li>
        <li><a href="{{ route('offers') }}" class="{{$page_link_name=='offers'?'active':''}}">{{_lang('app.offers')}}</a></li>
   
        <li> <a href="{{ route('contact_us') }}" class="{{$page_link_name=='contact-us'?'active':''}}">{{ _lang('app.contact_us') }} </a></li>
        @if (Auth::check())
        <li> <a href="#" class="{{in_array($page_link_name,['user-profile','edit-user-profile','user-favourites','user-addresses','user-orders'])?'active':''}}">{{ _lang('app.profile') }}</a>
            <ul>
            <li><a href="{{ route('profile') }}"><i class="fa fa-user" aria-hidden="true"></i> {{ _lang('app.profile') }}</a></li>
            <li> <a href="{{ _url('logout') }}"
           onclick="event.preventDefault();
                   document.getElementById('logout-form').submit();"><i class="fa fa-sign-out" aria-hidden="true"></i> {{ _lang('app.logout') }}</a>
        <form id="logout-form" action="{{ _url('logout') }}" method="POST" style="display: none;">
            {{ csrf_field() }}
        </form></li>
          </ul>
        </li>
        @else
          <li> <a href="{{_url('login?return='.base64_encode(request()->getPathInfo() . (request()->getQueryString() ? ('?' . request()->getQueryString()) : ''))) }}" class="{{$page_link_name=='login'?'active':''}}">{{ _lang('app.login') }}</a>  </li>
        @endif
       
       
            <li><a href="{{url($next_lang_code.'/'.substr(Request()->path(), 3))}}">{{$next_lang_text}} </a></li>
            
         
        <li><a href="{{_url('cart?step=1')}}" class="{{$page_link_name=='cart'?'active':''}}">{{_lang('app.cart')}}</a></li>
      </ul>
    </div>
    
    
    
    @yield('filter') 
  </div>
  
  
</header>