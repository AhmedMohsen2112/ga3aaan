
<div class="det-border">
    <div class="col-md-12">
        <div class="col-md-2">
            <img src="{{$meal->image}}" alt="" />
        </div>
        <div class="col-md-6">
            <h2>{{$meal->title}}</h2>
            <p>{{$meal->description}}</p>
        </div>
        <div class="col-md-4">
            <span>{{_lang('quantity')}}</span>
            <input type="text" name="qty" id="qty" class="form-control input-number" value="1" min="1" max="10">
            @if($meal->discount_price > 0)
            <span> {{$meal->discount_price.' '.$currency_sign}}  </span>
            @else
            <span> {{$meal->price.' '.$currency_sign}}  </span>
            @endif
        </div>
    </div>
</div>
@foreach($choices as $choice)
@php $choice_id='ch'.$choice->id @endphp
<div class="det-model">
    <div class="col-md-12">
        <div class="title-model" id="{{$choice_id}}">
            <h2>{{$choice->title}}</h2>
            <p>{{_lang('app.minimum').' '.$choice->min}} - {{_lang('app.maximum').' '.$choice->max}}</p>
            <span class="error help-message" style="display:none;"><i class="fa fa-exclamation-triangle"></i></span>
        </div>
        <ul class="nameprofile">
            @foreach($choice->sub as $one)
            @php $inputId='ch'.$one->id @endphp
            @if($choice->max==1)
            <li>
                <p class="name-size">{{$one->title}}</p>
                @if($one->price > 0)
                <p class="name-size">{{$one->price.' '.$currency_sign}}</p>
                @endif
                <div class="radio radio-info radio-inline">
                    <input id="{{$inputId}}" name="choices[{{$choice->id}}][]" value="{{$one->id}}" type="radio">
                    <label for="{{$inputId}}"> </label>
                </div>
            </li>


            @else
            <li>
                <p class="name-size">{{$one->title}}</p>
                @if($one->price > 0)
                <p class="name-size">{{$one->price.' '.$currency_sign}}</p>
                @endif
                <div class="checkbox checkbox-danger ornig">
                    <input id="{{$inputId}}" name="choices[{{$choice->id}}][]" type="checkbox" value="{{$one->id}}">
                    <label for="{{$inputId}}"></label>
                </div>
            </li>

            @endif
            @endforeach

        </ul>

    </div>
</div>
@endforeach