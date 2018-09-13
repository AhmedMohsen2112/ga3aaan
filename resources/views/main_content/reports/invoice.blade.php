


<div style="text-align: right; margin: 0; padding: 0; border: 0; text-decoration: none; list-style: none; direction: rtl; font: 14px/28px Tahoma, Geneva, sans-serif; color: #232323; padding: 0px 15px;">
    <header class="header" style="float: right; width: 100%; padding-bottom: 15px; border-bottom: 2px solid #eaeaea;">
        <div class="container-fluid" style="float: right; width: 100%;">
            <div class="logo" style="float: right; margin-top: 15px;">
                <img style="width: 190px;" src="{{ url('public/backend/images/logo.png')  }}" title="موكا بوك">
            </div>
            <div class="leftexttop" style="float: left; margin-top: 26px;">

                <p> رقم الطلب<span> {{  $order->id  }}</span></p>
                <p> وفت انشاء الطلب: <span> {{  $order->created_at  }}</span></p>
            </div>
        </div>
        <!--/.container-fluid--> 

    </header>
    <!--//.header-->

    <div class="container-fluid" style="float: right; width: 100%;">
        <div class="ribox" style="float: right; width: 50%; margin-top: 30px;">
            <h2 style="margin-bottom: 10px;">تفاصيل الطلب</h2>
            <p>رقم الطلب :<span> {{$order->id}}</span></p>
            <p>المطعم :<span> {{$order->resturant_title}}</span></p>
            <p>اجمالى السعر:<span>  {{$order->total_cost}}</span></p>
            <p>طريقة الدفع :<span> {{$order->payment_method}}</span></p>
        </div>
        <div class="ribox" style="float: right; width: 50%; margin-top: 30px;">
            <h2 style="margin-bottom: 10px;">بيانات العميل</h2>
            <p>الاسم:<span> {{$order->client}}</span></p>
            <p>البريد الإلكترونى:<span> {{$order->email}}</span></p>
            <p>الموبايل:<span> {{$order->mobile}}</span></p>
            <p>العنوان:<span> {{$order->long_address}}</span></p>

        </div>
        <!--ribox-->

        <!--ribox-->




        <table width="100%" border="0" align="center" class="table table-striped"   style=" float:right;margin-top:30px;">
            <thead>
                <tr>
                    <th style="padding: 8px; line-height: 1.42857143; vertical-align: top; border: 1px solid #ddd;">#</th>
                    <th style="padding: 8px; line-height: 1.42857143; vertical-align: top; border: 1px solid #ddd;">االمنتج</th>
                    <th style="padding: 8px; line-height: 1.42857143; vertical-align: top; border: 1px solid #ddd;">السعر</th>
                    <th style="padding: 8px; line-height: 1.42857143; vertical-align: top; border: 1px solid #ddd;">الكمية</th>
                    <th style="padding: 8px; line-height: 1.42857143; vertical-align: top; border: 1px solid #ddd;">الاجمالى</th>
                </tr>
            </thead>
            <tbody>

                @foreach($meals as $key1=> $meal)
                <tr>
                    <td style="padding: 8px; line-height: 1.42857143; vertical-align: top; border: 1px solid #ddd;"> {{$key1+1}} </td>
                    <td style="padding: 8px; line-height: 1.42857143; vertical-align: top; border: 1px solid #ddd;">
                        {{$meal->title}}
                        @foreach($meal->sub_choices as $choice)
                        <p>{{$choice->title}}</p>
                        @endforeach
                    </td>
                    <td style="padding: 8px; line-height: 1.42857143; vertical-align: top; border: 1px solid #ddd;">
                        {{$meal->cost_of_meal+$meal->sub_choices_price}}
                    </td>
                    <td style="padding: 8px; line-height: 1.42857143; vertical-align: top; border: 1px solid #ddd;">
                        {{$meal->quantity}}
                    </td>
                    <td style="padding: 8px; line-height: 1.42857143; vertical-align: top; border: 1px solid #ddd;">
                        {{$meal->cost_of_quantity}}
                    </td>
                </tr>
                @endforeach


            </tbody>
        </table>
        <div class="ribox innerbac" style="float: left; width: 33.333%; margin-top: 30px;background: #f9f9f9; border: 1px solid #ddd; padding: 15px; margin-bottom: 30px;">
            <p>السعر<span> {{ $order->primary_price }} {{ $currency_sign }}</span></p>
            <p>خدمة<span>  {{ $order->service_charge }} %</span></p>
            <p>ضريبة<span>  {{ $order->vat }} %</span></p>
            <p>رسوم التوصيل<span> {{ $order->delivery_cost }} {{ $currency_sign }}</span></p>
            @if ($order->coupon)
            <p>اجمالى السعر<span> {{ $order->coupon }}</span></p>
            @endif
            <p>اجمالى السعر<span> {{ $order->total_cost }} {{ $currency_sign }}</span></p>
            <p>العمولة<span>  {{( $order->total_cost*$order->commission)/100 }} {{ $currency_sign }}</span></p>

        </div>


    </div>
    <!--/.container-fluid-->

</div>

