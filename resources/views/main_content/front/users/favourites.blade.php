@extends('layouts.user_profile')

@section('pageTitle',$page_title)

@section('title')
{{ _lang('app.favourites') }}
@endsection
@section('js')
<script>

    $('#confirm-delete').on('show.bs.modal', function (e) {
        $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
    });



</script>

<script type="text/javascript" src="{{url('public/front/js')}}/jquery.jscroll.js"></script>

<script type="text/javascript">
    $('ul.pagination').hide();
    $(function () {
        $('.infinite-scroll').jscroll({
            autoTrigger: true,
            loadingHtml: '<img class="center-block" style="margin-left:35%;" src="{{url('public/front')}}/images/loading.gif" alt="Loading..." />',
            padding: 0,
            nextSelector: '.pagination li.active + li a',
            contentSelector: 'div.infinite-scroll',
            callback: function () {
                $('ul.pagination').remove();
            }
        });
    });
</script>


@endsection

@section('content')

 <div class="infinite-scroll">
@foreach ($favourites as $favourite)
<div class="agent">  

    <a href="#confirm-delete" data-href="{{ route('add-favourite') }}?meal={{ $favourite->meal_id }}&branch={{ $favourite->branch_id }}" title="{{ _lang('app.delete') }}" data-toggle="modal" class="fa fa-times telibnk">

    </a>
    <a href="{{ _url('resturant/'.$favourite->resturant_slug)}}">
    <div class="col-sm-2 titleagent">
        <img src="{{ url('public/uploads/meals/'.$favourite->image) }}">
    </div>
    <div class="col-sm-10 titleagent">

         <h3 class="nam-tit">{{ $favourite->meal }}</h3>

        <p class="textblog">{{ $favourite->resturant.' - '.$favourite->branch }}</p>

        <span class="namber">{{  $favourite->price }} {{ $currency_sign }}</span>
    </div>
    </a>
    <!--titleagent--> 

</div>

@endforeach
{{ $favourites->links() }}  
</div>

<div id="confirm-delete" class="modal fade"  tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog"> 

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title titlpop">هل انت متأكد من الحذف</h4>
            </div>
            <div class="modal-footer textcent">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ _lang('app.cancel') }}</button>
                <a class="btn btn-danger btn-ok">{{ trans('messages.delete') }}</a>
            </div>
        </div>
    </div>
</div>



@endsection