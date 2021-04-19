<?php
	$incomingType = array();
	
	$cableData = \DB::table('vendor_services')->where('status', '=', '1')->get();
    if($cableData)
    {
        foreach($cableData as $k => $row)
        {
            $incomingType[$row->id] = $row->name;
        }
    }
	
	//dd($incomingType);
	
	$invoiceInfoArr = array();
		
	$bankData = \DB::table('vendor_service_assign')->where('vendor_id', $entry->getKey())->get();
	if($bankData)
	{
        foreach($bankData as $row1)
        {
            $invoiceInfoArr[] = array('vendor_service_id' => $row1->vendor_service_id, 'zip_code' => $row1->zip_code, 'price' => $row1->price);
        }
	}

    //dd($invoiceInfoArr);

    for($count=1;$count<=20;$count++)
    {
?>
<!-- select2 from array -->

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
                    <option value="{{ $key }}" data-type="{{ $key }}" @if(isset($invoiceInfoArr[$count-1]) && ($invoiceInfoArr[$count-1]['vendor_service_id'] == $key)) selected @endif>{{ $value }}</option>
                @endforeach
            @endif
        </select>
    </div>

    <div class="form-group col-sm-4">
        <label>{!! $field['label'] !!} Zipcode</label>

        <textarea
            name="{{ $field['name'] }}_zipcode[]"
            class='form-control'
            >@if(isset($invoiceInfoArr[$count-1]['zip_code'])) {{ $invoiceInfoArr[$count-1]['zip_code'] }} @endif</textarea>
        
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
            value="@if(isset($invoiceInfoArr[$count-1]['price'])) {{ $invoiceInfoArr[$count-1]['price'] }} @endif"
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
