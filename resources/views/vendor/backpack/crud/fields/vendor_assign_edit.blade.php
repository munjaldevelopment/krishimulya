<?php
	$incomingType = array();
	
	$panelData = \DB::table('banking_arrangment')->orderBy('lft', 'DESC')->get();
	if($panelData)
	{
		foreach($panelData as $k => $row)
		{
			$incomingType[$row->id] = $row->name;
		}
	}
	
	//dd($incomingType);
	
	foreach($incomingType as $k => $v)
	{
		$invoiceInfoArr = array();
		
		$bankData = \DB::table('lender_banking')->where('lender_id', $entry->getKey())->where('banking_arrangment_id', $k)->first();
		if($bankData)
		{
			$invoiceInfoArr[$k] = array('lender_banking_status' => $bankData->lender_banking_status, 'sanction_amount' => $bankData->sanction_amount, 'outstanding_amount' => $bankData->outstanding_amount);
		}
?>
<!-- select2 from array -->

<div class="form-group col-sm-6">
    <label>{{ $v }} Sanction</label>
	
	<input
            type="hidden"
            name="banking_arrangment_id[]"
            value="{{ $k }}"
        >

    <input
            type="hidden"
            name="lender_banking_status_old[]"
            value="@if(isset($invoiceInfoArr[$k])){{$invoiceInfoArr[$k]['lender_banking_status']}}@endif"
        >

    <input
            type="hidden"
            name="{{ $field['name'] }}_sanction_old[]"
            value="@if(isset($invoiceInfoArr[$k])){{$invoiceInfoArr[$k]['sanction_amount']}}@endif"
        >
		
	<input
            type="text"
            class="form-control"
            name="{{ $field['name'] }}_sanction[]"
            value="@if(isset($invoiceInfoArr[$k])){{$invoiceInfoArr[$k]['sanction_amount']}}@endif"
        >
</div>

<div class="form-group col-sm-6">
	<label>{{ $v }} Outstanding</label>

	<input
            type="hidden"
            name="{{ $field['name'] }}_outstanding_old[]"
            value="@if(isset($invoiceInfoArr[$k])){{$invoiceInfoArr[$k]['outstanding_amount']}}@endif"
        >

	<input
            type="text"
            class="form-control"
            name="{{ $field['name'] }}_outstanding[]"
            value="@if(isset($invoiceInfoArr[$k])){{$invoiceInfoArr[$k]['outstanding_amount']}}@endif"
        >
	
    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>
<?php
	}
?>
