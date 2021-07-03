# PHPLinnworksAPI
PHP Linnworks API Access


Usage

	$linnworks = New api_linnworks(); // Start new API Request
	$linnworks->set_credentials( Your_ApplicationId , Your_ApplicationSecret , Your_Token ); // Set API Credentials

	if ( $linnworks->AuthorizeByApplication() == true ) {

		// format youir data as per the linnworks API specifcations
		
		$data = array("DateFrom"=>"08/15/2019", 
			      "DateTo"=>"08/17/2019", 
			      "Status"=>"OPEN", 
			      "ReferenceLike"=>"987", 
			      "EntriesPerPage"=>"100", 
			      "PageNumber"=>"1", 
			      "Location"=>array("3329af95-5329-45f8-beac-e3c5852fc130"), 
			      "Supplier"=>array("313c97d4-3877-4087-b6a9-d475634d0857")
			);
		
		// call the linnworks API with the name of the API + formatted data
		$lapi = $linnworks->call_linnworks_api("FindStockItem", $data );
		
	} else {
		echo "Failed to authenticate";
	}
