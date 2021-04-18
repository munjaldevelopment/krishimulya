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
	
	foreach($incomingType as $k => $v)
	{
		$invoiceInfoArr = array();

		
?>
<div class="form-group col-sm-6">
    <label>{{ $v }} Sanction</label>
	
	<input
            type="hidden"
            name="banking_arrangment_id[]"
            value="{{ $k }}"
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
