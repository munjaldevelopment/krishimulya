<?php
	$cableData = \DB::table('vendor_services')->where('status', '=', '1')->get();
	if($cableData)
	{
		foreach($cableData as $k => $row)
		{
			$incomingType[$row->id] = $row->name;
		}
	}

	/*$cableData1 = \DB::table('pincodes')->where('active', '=', '1')->get();
	if($cableData1)
	{
		foreach($cableData1 as $k => $row)
		{
			$pincode[$row->id] = $row->zip;
		}
	}*/

	for($count=1;$count<=20;$count++)
	{

		
?>
	<div class="form-group col-sm-4">
	    <label>{!! $field['label'] !!}  #<?php echo $count; ?></label>

	    <select
	        name="{{ $field['name'] }}[]"
	        style="width: 100%"
			class='form-control select2_from_array'
			id="invoice_info_name_<?php echo $count; ?>"
	        >

	        <option value="">-</option>

	        @if (count($incomingType))
	            @foreach ($incomingType as $key => $value)
					<option value="{{ $key }}" data-type="{{ $key }}">{{ $value }}</option>
	            @endforeach
	        @endif
	    </select>
	</div>

	<div class="form-group col-sm-4">
		<label>{!! $field['label'] !!} Zipcode</label>

		<textarea
	        name="{{ $field['name'] }}_zipcode[]"
			class='form-control'
	        ></textarea>
		
	    {{-- HINT --}}
	    @if (isset($field['hint']))
	        <p class="help-block">{!! $field['hint'] !!}</p>
	    @endif
	</div>	

	<div class="form-group col-sm-4">
		<label>{!! $field['label'] !!} Price</label>

		<input type="text"
	        name="{{ $field['name'] }}_price[]"
			class='form-control'
	        >
		
	    {{-- HINT --}}
	    @if (isset($field['hint']))
	        <p class="help-block">{!! $field['hint'] !!}</p>
	    @endif
	</div>	

</div><div class="row">
<?php
	}
?>

@include('crud::fields.inc.wrapper_end')

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->fieldTypeNotLoaded($field))
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
        <!-- include select2 css-->
        <link href="{{ asset('packages/select2/dist/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('packages/select2-bootstrap-theme/dist/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
        <!-- include select2 js-->
        <script src="{{ asset('packages/select2/dist/js/select2.full.min.js') }}"></script>
        @if (app()->getLocale() !== 'en')
        <script src="{{ asset('packages/select2/dist/js/i18n/' . app()->getLocale() . '.js') }}"></script>
        @endif
        <script>
            function bpFieldInitSelect2Element(element) {
                // element will be a jQuery wrapped DOM node
                if (!element.hasClass("select2-hidden-accessible")) {
                    element.select2({
                        theme: "bootstrap"
                    });
                }
            }
        </script>
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}