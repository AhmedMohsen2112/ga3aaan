<htmlpageheader name="page-header">
    <div class="head">
        <div  class="logo" style="float: left; width: 28%;">
            <img src="{{ url('public/backend/images/logo.png')  }}">
        </div>
      
 
    </div>




</htmlpageheader>

<htmlpagefooter name="page-footer">
    <p style="text-align: center;">{PAGENO}</p>
</htmlpagefooter>
<style>
    @page {
        header: page-header;
        footer: page-footer;
    }
table {
    border-collapse: collapse;
    width: 100%;
}

th, td {
    text-align: left;
    padding: 8px;
}
tr:nth-child(even){background-color: #f2f2f2}

th {
    background-color: #4CAF50;
    color: white;
}

</style>
<!--<style>
    .header,
.footer {
    width: 100%;
    text-align: center;
    position: fixed;
}
.header {
    top: 0px;
}
.footer {
    bottom: 0px;
}
.pagenum:before {
    content: counter(page);
}
</style>-->

<div class="row">
    @if($orders->count()>0)
    <div class="col-sm-12">
        <table class = "table table-responsive table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th>{{_lang('app.order_no')}}</th>
                    <th>{{_lang('app.client')}}</th>
                    <th>{{_lang('app.resturant')}}</th>
                    <th>{{_lang('app.total_cost')}}</th>

                    <th>{{_lang('app.commission_cost')}}</th>

                    <th>{{_lang('app.payment_method')}}</th>
                    <th colspan="2">{{_lang('app.date')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $one)
                <tr>
                    <td>{{$one->id}}</td>
                    <td>{{$one->client_name}}</td>
                    <td>{{$one->resturant_title}}</td>
                    <td>{{$one->total_cost}}</td>

                    <td>{{round($one->commission_cost,2)}}</td>

                    <td>{{$one->payment_method}}</td>
                    <td>{{$one->date}}</td>

                </tr>
                @endforeach
            </tbody>
  
        </table>
    </div>
    <div class="text-center">

    </div>
    @else
    <p class="text-center">{{_lang('app.no_results')}}</p>
    @endif


</div>