@extends('layouts.user_profile')
@section('pageTitle',$page_title)

@section('title')
  {{ $page_title }}
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
	<a href="{{ route('user-addresses.create') }}" class="botoom addadrs"><i class="fa fa-plus" aria-hidden="true"></i>{{ _lang('app.add_address') }}</a>
         <div class="infinite-scroll">
         @foreach ($addresses as $address)
         	<div class="agent"> 

			<a href="#confirm-delete" data-href="{{ route('delete-address',Crypt::encrypt($address->id)) }}" title="{{ _lang('app.delete') }}" data-toggle="modal" class="fa fa-times telibnk"></a>

			 <a href="{{ route('user-addresses.edit',Crypt::encrypt($address->id)) }}" title="{{ _lang('app.edit') }}" class="fa fa-pencil telibnk"></a>

				<div class="col-sm-12 titleagent">

				  <h3 class="nam-tit">{{ $address->city }} - {{ $address->region }}</h3>


				  <p class="textblog">{{ $address->city }} - {{ $address->region }} - {{ $address->sub_region }} - {{ $address->street }}</p>

				</div>
				<!--titleagent--> 
				
		    </div>
         @endforeach
         {{ $addresses->links() }}
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

<div class="clearfix"></div>

		
@endsection