@if ($crud->hasAccess('create'))
	<a href="{{ backpack_url('download_report') }}?soil_test_id={{ $entry->getKey() }}" target="_blank" class="btn btn-xs btn-success"><i class="fa fa-cloud"></i> Download</a>
@endif