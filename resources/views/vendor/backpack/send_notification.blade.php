@extends(backpack_view('blank'))

@php
  $defaultBreadcrumbs = [
    trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
    
    trans('Lender Banking') => false,
  ];

  // if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
  $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
@endphp

@section('header')
<div class="container-fluid">
    <h2>
      <span class="text-capitalize">Send Notification</span>
	  <small id="datatable_info_stack"></small>
	</h2>
</div>
@endsection

@section('content')
<div class="row"> 
	<!-- THE ACTUAL CONTENT -->
	<div class="col-md-12">
		@if ($message = Session::get('success'))
		<div class="alert alert-success" role="alert"> {!! Session::get('success') !!} </div>
		@endif
		
		
		@if ($message = Session::get('error'))
		<div class="alert alert-danger" role="alert"> {!! Session::get('error') !!} </div>
		@endif
		<form action="{{ URL(config('backpack.base.route_prefix'), 'sendNotification') }}" method="post" enctype="multipart/form-data">
			{!! csrf_field() !!}
		  	<div class="col-md-12">

		    	<div class="row display-flex-wrap">
			
					<div class="box col-md-12 padding-10 p-t-20"> 
						<!-- load the view from type and view_namespace attribute if set --> 
						
						<!-- text input -->
						<div class="form-group col-xs-12">
							<label>Title</label>
							<input type="text" name="notification_title" class="form-control" />
						</div>

						<div class="form-group col-xs-12">
							<label>Message</label>
							<textarea name="notification_message" class="form-control"></textarea>
						</div>
					</div>
				</div>
				
				<div class="">
					<div id="saveActions" class="form-group">
						<div class="btn-group">
							<button type="submit" class="btn btn-success"><i class="fa fa-cloud"></i> Send Notification</button>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
@endsection