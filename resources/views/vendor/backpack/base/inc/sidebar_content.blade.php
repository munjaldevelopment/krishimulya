<!-- This file is used to store sidebar items, starting with Backpack\Base 0.9.0 -->
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}</a></li>

<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-award"></i> Master</a>
	<ul class="nav-dropdown-items">
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('homeslider') }}'><i class='nav-icon la la-image'></i>Home Slider</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('states') }}'><i class='nav-icon la la-building'></i> States</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('city') }}'><i class='nav-icon la la-city'></i> Cities</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('pincode') }}'><i class='nav-icon la la-user'></i> PinCodes</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('setting') }}'><i class='nav-icon la la-cog'></i> Settings</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('page') }}'><i class='nav-icon la la-file-o'></i> <span>Pages</span></a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('app_popup') }}'><i class='nav-icon la la-user'></i> App Popups</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('vendorservice') }}'><i class='nav-icon la la-user'></i> Vendor Service</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('leadstatus') }}'><i class='nav-icon la la-building'></i> Lead Statuses</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('croptype') }}'><i class='nav-icon la la-building'></i> Crop Type</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('soiltype') }}'><i class='nav-icon la la-building'></i> Soil Type</a></li>

		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('calltype') }}'><i class='nav-icon la la-building'></i> Call Type</a></li>

		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('survey') }}'><i class='nav-icon la la-building'></i> Survey</a></li>

		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('missed-call') }}'><i class='nav-icon la la-building'></i> Missed-calls</a></li>
	</ul>
</li>

<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-award"></i> Check-in</a>
	<ul class="nav-dropdown-items">
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('usercheckinout') }}'><i class='nav-icon la la-building'></i> User Check in-Out</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('usercheckinform') }}'><i class='nav-icon la la-building'></i> User Checkin Forms</a></li>

		
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('checkinlatlong') }}'><i class='nav-icon la la-building'></i> Checkin LatLong</a></li>
	</ul>
</li>

<li class='nav-item'><a class='nav-link' href='{{ backpack_url('customer') }}'><i class='nav-icon la la-user'></i> Customers</a></li>

{{--<li class='nav-item'><a class='nav-link' href='{{ backpack_url('walletpayment') }}'><i class='nav-icon la la-wallet'></i> Wallet Payments</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('partners') }}'><i class='nav-icon la la-users'></i>Partners</a></li>--}}

<li class='nav-item'><a class='nav-link' href='{{ backpack_url('notification') }}'><i class='nav-icon la la-mobile-alt'></i>Notifications</a></li>

<li class='nav-item'><a class='nav-link' href='{{ backpack_url('missedcall') }}'><i class='nav-icon la la-terminal'></i> Missed Calls</a></li>

<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-award"></i> Feed</a>
	<ul class="nav-dropdown-items">
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('feedcategories') }}'><i class='nav-icon la la-file'></i> Feed Categories</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('feeds') }}'><i class='nav-icon la la-newspaper'></i> Feeds</a></li>
	</ul>
</li>

<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-award"></i> Agri Type</a>
	<ul class="nav-dropdown-items">
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('agri_type') }}'><i class='nav-icon la la-pagelines'></i> Agri Type</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('agri_type_enquiry') }}'><i class='nav-icon la la-pagelines'></i> Agri Type Enquiry</a></li>

		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('agri_tool') }}'><i class='nav-icon la la-wrench'></i> Agri Tool</a></li>

		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('agri_tool_enquiry') }}'><i class='nav-icon la la-wrench'></i> Agri Tool Enquiry</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('agri_tool_enquiry_partner') }}'><i class='nav-icon la la-wrench'></i> Agri Tool Enquiry Partner</a></li>
	</ul>
</li>

{{-- <li class='nav-item'><a class='nav-link' href='{{ backpack_url('finance_enquiry') }}'><i class='nav-icon la la-question'></i> Finance Enquiries</a></li> --}}

<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-award"></i> Soil Test</a>
	<ul class="nav-dropdown-items">
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('soiltesttype') }}'><i class='nav-icon la la-building'></i> Soil Test Types</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('soiltestorders') }}'><i class='nav-icon la la-building'></i> Soil Test Orders</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('soiltestorders_partner') }}'><i class='nav-icon la la-building'></i> Soil Test Orders Partner</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('sevakendra')  }}'><i class='nav-icon la la-building'></i> Seva Kendra</a></li>

		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('soiltest-orderdata') }}'><i class='nav-icon la la-building'></i> SoilTest Order Data</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('soiltest-order-cropdata') }}'><i class='nav-icon la la-building'></i> SoilTest Order CropData</a></li>


	</ul>
</li>

<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-award"></i> Labour</a>
	<ul class="nav-dropdown-items">
	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('labourtype') }}'><i class='nav-icon la la-handshake'></i> Labour Type</a></li>
	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('labour_enquiry') }}'><i class='nav-icon la la-people-carry'></i> Labour Enquiries</a></li>
	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('labour_enquiry_partner') }}'><i class='nav-icon la la-people-carry'></i> Labour Enquiries Partner</a></li>

	</ul>
</li>


<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-award"></i> Insurance</a>
	<ul class="nav-dropdown-items">
	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('insurancetype') }}'><i class='nav-icon la la-house-damage'></i> Insurance Types</a></li>
	 <li class='nav-item'><a class='nav-link' href='{{ backpack_url('insuranceenquiry') }}'><i class='nav-icon la la-headset'></i> Insurance Enquiries</a></li>
	 <li class='nav-item'><a class='nav-link' href='{{ backpack_url('insuranceenquiry_partner') }}'><i class='nav-icon la la-headset'></i> Insurance Enquiries Partner</a></li>
	</ul>
</li>

<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-pagelines"></i> Agri Land</a>
	<ul class="nav-dropdown-items">
	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('landtype') }}'><i class='nav-icon la la-image'></i> Land Type</a></li>
	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('landsize') }}'><i class='nav-icon la la-sort-numeric-up'></i> Land Size</a></li>
	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('renttime') }}'><i class='nav-icon la la-clock'></i> Rent Time</a></li>

	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('agrilandrentenquiry') }}'><i class='nav-icon la la-headset'></i> Rent Enquiry</a></li>
	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('agrilandrentenquiry_partner') }}'><i class='nav-icon la la-headset'></i> Rent Enquiry Partner</a></li>

	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('agrilandsaleenquiry') }}'><i class='nav-icon la la-headset'></i> Sale Enquiry</a></li>
	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('agrilandsaleenquiry_partner') }}'><i class='nav-icon la la-headset'></i> Sale Enquiry Partner</a></li>
	</ul>
</li>

<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-pagelines"></i> Crop Material</a>
	<ul class="nav-dropdown-items">
	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('cropmaterials') }}'><i class='nav-icon la la-pagelines'></i> Materials</a></li>
	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('cropmaterialsenquiry') }}'><i class='nav-icon la la-pagelines'></i> Materials Enquiries</a></li>
	</ul>
</li>

<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-tractor"></i> Tractors</a>
	<ul class="nav-dropdown-items">
	  
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('company') }}'><i class='nav-icon la la-building'></i> Company</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('hoursepower') }}'><i class='nav-icon la la-horse'></i> Horse Powers</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('paymenttype') }}'><i class='nav-icon la la-inr'></i> Payment Types</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('purposetype') }}'><i class='nav-icon la la-envira'></i> Purpose Type</a></li>

		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('tractor_rent_enquiry') }}'><i class='nav-icon la la-headset'></i> Rent Enquiries</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('tractor_rent_enquiry_partner') }}'><i class='nav-icon la la-headset'></i> Rent Enquiries Partner</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('tractor_sell_enquiry') }}'><i class='nav-icon la la-headset'></i> Sale Enquiries</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('tractor_sell_enquiry_partner') }}'><i class='nav-icon la la-headset'></i> Sale Enquiries Partner</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('tractor_purchase_enquiry') }}'><i class='nav-icon la la-headset'></i> Purchase Enquiries</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('tractor_purchase_enquiry_partner') }}'><i class='nav-icon la la-headset'></i> Purchase Enquiries Partner</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('tractor_refinance_enquiry') }}'><i class='nav-icon la la-headset'></i> Refinance Enquiries</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('tractor_refinance_enquiry_partner') }}'><i class='nav-icon la la-headset'></i> Refinance Enquiries Partner</a></li>
	</ul>
</li>

<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-tractor"></i> Feedback</a>
	<ul class="nav-dropdown-items">
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('feedback') }}'><i class='nav-icon la la-support'></i> Feedback</a></li>
		<li class='nav-item'><a class='nav-link' href='{{ backpack_url('feedback_partner') }}'><i class='nav-icon la la-support'></i> Feedback Partner</a></li>
	</ul>
</li>

<li class='nav-item'><a class='nav-link' href='{{ backpack_url('enquirytracking') }}'><i class='nav-icon la la-headset'></i> Enquiry Tracking</a></li>

<li class='nav-item'><a class='nav-link' href='{{ backpack_url('log') }}'><i class='nav-icon la la-terminal'></i> Logs</a></li>


{{-- <li class="nav-item nav-dropdown">
    <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-newspaper-o"></i>Krishi Feeds</a>
    <ul class="nav-dropdown-items">
      <li class="nav-item"><a class="nav-link" href="{{ backpack_url('article') }}"><i class="nav-icon la la-newspaper-o"></i> Articles</a></li>
      <li class="nav-item"><a class="nav-link" href="{{ backpack_url('category') }}"><i class="nav-icon la la-list"></i> Categories</a></li>
      <li class="nav-item"><a class="nav-link" href="{{ backpack_url('tag') }}"><i class="nav-icon la la-tag"></i> Tags</a></li>
    </ul>
</li> --}}

<!-- Users, Roles, Permissions -->
<li class="nav-item nav-dropdown">
	<a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-users"></i> Authentication</a>
	<ul class="nav-dropdown-items">
	  <li class="nav-item"><a class="nav-link" href="{{ backpack_url('user') }}"><i class="nav-icon la la-user"></i> <span>Users</span></a></li>
	  <li class='nav-item'><a class='nav-link' href='{{ backpack_url('vendor') }}'><i class='nav-icon la la-user'></i> Vendors</a></li>
	  {{--<li class="nav-item"><a class="nav-link" href="{{ backpack_url('role') }}"><i class="nav-icon la la-id-badge"></i> <span>Roles</span></a></li>
	  <li class="nav-item"><a class="nav-link" href="{{ backpack_url('permission') }}"><i class="nav-icon la la-key"></i> <span>Permissions</span></a></li>--}}
	</ul>
</li>