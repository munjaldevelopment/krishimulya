@extends(backpack_view('blank'))

@section('content')
	<div name="widget_411888894" section="before_content" class="row">
		<div class="col-sm-6 col-lg-3">
			<div class="card border-0 text-white bg-primary">
    			<div class="card-body">
            		<div class="text-value">132</div>
      
            		<div>Registered users.</div>
            
            		<div class="progress progress-white progress-xs my-2">
        				<div class="progress-bar" role="progressbar" style="width: 13.2%" aria-valuenow="13.2" aria-valuemin="0" aria-valuemax="100"></div>
      				</div>
            
            		<small class="text-muted">868 more until next milestone.</small>
          		</div>
      		</div>
		</div>		
			
		<div class="col-sm-6 col-lg-3">
	    	<div class="card border-0 text-white bg-success">
	    		<div class="card-body">
	            	<div class="text-value">1031</div>
	      
	            	<div>Articles.</div>
	            
	            	<div class="progress progress-white progress-xs my-2">
	        			<div class="progress-bar" role="progressbar" style="width: 80%" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100"></div>
	      			</div>
	            
	            	<small class="text-muted">Great! Don't stop.</small>
	          </div>
	    	</div>
		</div>		
			
		<div class="col-sm-6 col-lg-3">
			<div class="card border-0 text-white bg-warning">
    			<div class="card-body">
		            <div class="text-value">24 days</div>
		      
		            <div>Since last article.</div>
		            
		            <div class="progress progress-white progress-xs my-2">
				        <div class="progress-bar" role="progressbar" style="width: 30%" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100"></div>
				      </div>
            
            		<small class="text-muted">Post an article every 3-4 days.</small>
				</div>

			</div>
		</div>		
		
		<div class="col-sm-6 col-lg-3">
			<div class="card border-0 text-white bg-dark">
    			<div class="card-body">
					<div class="text-value">210</div>

					<div>Products.</div>

					<div class="progress progress-white progress-xs my-2">
					<div class="progress-bar" role="progressbar" style="width: 280%" aria-valuenow="280" aria-valuemin="0" aria-valuemax="100"></div>
					</div>

					<small class="text-muted">Try to stay under 75 products.</small>
          		</div>
      		</div>
		</div>
	</div>

	<div class="row" name="widget_100911562" section="before_content">
		<div class="col-md-6">
			<div class="card">
		        <div class="card-header">New Users Past 7 Days</div>
	        	<div class="card-body">
			    	<div class="card-wrapper">
			    		<div id="chart1"></div>

			    		{!! $chart1 !!}
					</div>
				</div>
			</div>
		</div>

		<div class="col-md-6">
			<div class="card">
		        <div class="card-header">New Entries</div>
	        	<div class="card-body">
			    	<div class="card-wrapper">
			    		<div id="chart2"></div>

			    		{!! $chart2 !!}
					</div>
				</div>
			</div>
		</div>
	</div>

	
	<div class="row" name="widget_293385369" section="after_content">
		<div class="col-md-4">
			<div class="card">
        		<div class="card-header">Pie Chart - Chartjs</div>
        		<div class="card-body">
        			<div class="card-wrapper">
        				<div id="chart3"></div>

			    		{!! $chart3 !!}
			    	</div>
			    </div>
			</div>
		</div>

		<div class="col-md-4">
			<div class="card">
        		<div class="card-header">Pie Chart - Echarts</div>
        		<div class="card-body">
        			<div class="card-wrapper">
        				<div id="chart4"></div>

			    		{!! $chart4 !!}
			    	</div>
			    </div>
			</div>
		</div>

		<div class="col-md-4">
			<div class="card">
        		<div class="card-header">Pie Chart - Highcharts</div>
        		<div class="card-body">
        			<div class="card-wrapper">
        				<div id="chart5"></div>

			    		{!! $chart5 !!}
			    	</div>
			    </div>
			</div>
		</div>
	</div>

	<div class="row" name="widget_198974168" section="after_content">
		<div class="col-md-6">
			<div class="card">
        		<div class="card-header">Line Chart - Chartjs</div>
        		<div class="card-body">
        			<div class="card-wrapper">
        				<div id="chart6"></div>

			    		{!! $chart6->render() !!}
			    	</div>
			    </div>
			</div>
		</div>

		<div class="col-md-6">
			<div class="card">
        		<div class="card-header">Line Chart - Echarts</div>
        		<div class="card-body">
        			<div class="card-wrapper">
        				<div id="chart7"></div>

			    		{!! $chart7->render() !!}
			    	</div>
			    </div>
			</div>
		</div>
	</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js"></script>
@endsection