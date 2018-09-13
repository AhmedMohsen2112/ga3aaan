 <!--//.cssmenu--> 




      <div id='cssmenu' style="margin-left:20px">

        <div class="dropdown show hidden-lg hidden-md" style="margin-top:-3px">
        
        <a class="btn btn-secondary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

            <i class="fa fa-search"></i>

        </a>

        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
            <div class="search-header">
                <form class="example" style="margin-top:10px;">
                    <input type="text" placeholder="{{ _lang('search') }}" name="query">
                    <button type="submit"><i class="fa fa-search"></i></button>
                </form>
                <div class="col-md-12">
                    <h2 style="font-size: 16px;color: #d5344a;margin: 10px 0 0 0;text-align:center;">الأكثر بحثا</h2>
                    <ul class="filter-ul" style="text-align:center;">
                        @foreach($categories as $one)
                            <li><a href="{{_url('resturantes?cat='.$one->id)}}"  {{isset($filter['cat'])&&$filter['cat']==$one->id?'color':''}}">{{$one->title}}</a></li>
                        @endforeach
                    </ul>
                </div>

            </div>
        </div>
    </div>


       <form>
      <div class="basket-hidden"><a class="accordion"><i class="fa fa-sliders"></i></a>
        <div class="panel">
            <div class="filter">
                <h2 style="font-size: 16px;color: #d5344a;margin: 10px 0 0 0; text-align:center;">فلتر - اظهر حسب</h2>
                <div class="col-md-12 check" style="margin:10px 0; border-bottom:1px solid #eee;">
                    <div class="row">
                        <div class="col-md-8">  
                            <p>{{ _lang('app.rate') }}</p>
                        </div>
                          <div class="col-md-4">
                            <label style="text-align:left;">
                              <input type="checkbox" {{isset($filter['rating'])&&$filter['rating']==1?'checked':''}} name="rating" value="1">
                              <span class="checkmark"></span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 check" style="margin:10px 0; border-bottom:1px solid #eee;">  
                    <div class="row">
                        <div class="col-md-8">  
                            <p>{{ _lang('app.offers') }}</p>
                        </div>
                          <div class="col-md-4">
                            <label style="text-align:left;">
                              <input type="checkbox" {{isset($filter['has_offer'])&&$filter['has_offer']==1?'checked':''}} name="has_offer" value="1">
                              <span class="checkmark"></span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 check" style="margin:10px 0; border-bottom:1px solid #eee;">  
                    <div class="row">
                        <div class="col-md-8">  
                            <p>{{ _lang('app.delivery_cost') }}</p>
                        </div>
                          <div class="col-md-4">
                            <label style="text-align:left;">
                              <input type="checkbox" name="fast_delivery" {{isset($filter['fast_delivery'])&&$filter['fast_delivery']==1?'checked':''}} value="1">
                              <span class="checkmark"></span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="">
                
                <button class="dropdown-btn" style="color: #d5344a;border:none;" type="button">
                    {{ _lang('app.cuisine_type') }}
                    <i class="fa fa-angle-down" aria-hidden="true"></i>
                </button>
                  <div class="dropdown-container">
                     @foreach($cuisines as $one)
                                <div class="check">
                                    <div class="col-md-8">  
                                        <p>{{$one->title}}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <label style="text-align:left;">
                                            <input type="checkbox" name="cuisines[]" {{(isset($filter['cuisines'])&&in_array($one->id,$filter['cuisines']))||(isset($cuisine)&&$cuisine->id==$one->id)?'checked':''}} value="{{$one->id}}">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                    @endforeach
                  </div>
            </div>
            <button type="submit" class="botoom submit-form" style="margin: 5px 0;float:right;padding:5px;font-size: 14px;" >فلتر</button>
            <button type="reset" value="Reset" class="botoom" style="margin: 5px 0;float:right;padding:5px;font-size: 14px;" >مسح</button>
            </div>
        </div>
      </div>
    </form>
      
 
      <script>
        var acc = document.getElementsByClassName("accordion");
        var i;

        for (i = 0; i < acc.length; i++) {
            acc[i].addEventListener("click", function() {
                this.classList.toggle("active");
                var panel = this.nextElementSibling;
                if (panel.style.display === "block") {
                    panel.style.display = "none";
                } else {
                    panel.style.display = "block";
                }
            });
        }
        </script>
    </div>