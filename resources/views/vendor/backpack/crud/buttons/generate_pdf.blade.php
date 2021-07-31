@if ($crud->hasAccess('create'))
	@php
		use Illuminate\Http\Request;
		
		$soil_test_order_id = $request->soil_test_id;
		$isExists = DB::table('soil_test_order_data')->where('soil_test_order_id', $soil_test_order_id)->count();
		if($isExists == 0):
	@endphp
	<a href="{{ backpack_url('download_soil_test') }}?soil_test_id={{ $entry->getKey() }}" target="_blank" class="btn btn-xs btn-success"><i class="fa fa-cloud"></i> Generate</a>
	@php
		endif;
	@endphp
@endif