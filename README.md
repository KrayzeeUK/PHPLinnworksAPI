# PHPLinnworksAPI
PHP Linnworks API Access


Usage

	$linnworks = New api_linnworks(); // Start new API Request
	$linnworks->set_credentials( Your_ApplicationId , Your_ApplicationSecret , Your_Token ); // Set API Credentials

	if ( $linnworks->AuthorizeByApplication() == true ) {

		// format youir data as per the linnworks API specifcations
		
		$data = array( "countries" => array(
							"CountryId" => "10f312d9-3438-4720-96c8-931bb5828487",
							"CountryName" => "sample string 2",
							"CountryCode" => "sample string 3",
							"Continent" => "sample string 4",
							"Currency" => "sample string 5",
							"CustomsRequired" => true,
							"TaxRate" => 1.1,
							"AddressFormat" => "sample string 7",
							"Regions" => array(
								"pkRegionRowId" => 1,
								"RegionCode" => "sample string 2",
								"RegionName" => "sample string 3",
								"TaxRate" => 1.1,
								"fkCountryId" => "1a3d5910-b30f-40f5-9d4a-bf2a3f9bcbbe",
								"ReplaceWith" => 0,
								"IsHomeRegion" => true,
								"TagsCount" => 6
							),
							"RegionsCount" => 8
						)
				);
		
		// call the linnworks API with the name of the API + formatted data
		$lapi = $linnworks->call_linnworks_api("CreateCountries", $data );
		
		
		
	} else {
		echo "Failed to authenticate";
	}
