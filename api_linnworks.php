<?php
	/*
		Writen & Designed by KrayZee Tech(Martin Roberts) & remains his Intellectual Property.
		© Copyright KrayZee Tech(Martin Roberts)  2015
		
		KrayZee Tech(Martin Roberts) may allow this software to be used for free for a period of time.  This can be retracted at anytime without notification.
		KrayZee Tech(Martin Roberts) will allow brading of his product during the usage period of the software but remains the property of KrayZee Tech(Martin Roberts)

		https://apps.linnworks.net/Api

		Linnworks API Intergration	
	*/
	
	class api_linnworks {
		
		private $curl_handle = NULL; 		// Pointer to curl process to be set later
		private $linn_app_id = NULL; 		// Linnworks App ID
		private $linn_app_secret = NULL;	// Linnworks App Secret Key
		private $linn_app_token = NULL; 	// Linnworks App Token
		private $linn_auth_data = false; 	// Authorisation data
		private $linn_auth_token = NULL;	// API Token
		private $linn_auth_server = NULL; 	// API Server
		public $linn_error = NULL; 			// Last Error Message
		private $debug = false; 			// Enable debug mode
		
		function __construct() {
			// initialize an object's properties upon creation

			try{
				$this->curl_handle = curl_init(); // Initate New curl sessionv
			} catch( Exception $e ) {
				echo "<pre>" . print_r($e,1) . "</pre>";
			}
		}
		function __destruct() {
			//  object is destructed or the script is stopped or exited

			curl_close( $this->curl_handle ); // Closes connection to the server
		}

		//  Debug Functions

		function enable_debug() {
			$this->debug = true;
		}
		protected function debug_display( $vals, $title = "Debug Info", $style='display: block; margin-left: auto; margin-right: auto; width: 90%; Height: 25%' ) {
			$html = "<br><hr>" . $title . "<hr><pre>";
			$html .= "<textarea style=\"" . $style . "\">" . print_r( $vals,true ) . "</textarea>";
			$html .= "<pre><hr><br>";
			
			return $html;
		}

		// Main API Calling Routine

		protected function api_call( $type, $api_url, $api_params=NULL, $api_headers=NULL, $api_options=NULL ) {
			/*
				Set all require headers for API Authorisation
			*/
			
			$d_header[] = "Connection: keep-alive";
			$d_header[] = "Accept: application/json";
			$d_header[] = "Content-Type: application/x-www-form-urlencoded; charset=UTF-8";
			$d_header[] = "Accept-Encoding: gzip, deflate";

			if ( $this->linn_auth_token != NULL ) {
				$d_header[] = "Authorization: " . $this->linn_auth_token ;
				
				$api_url = $this->linn_auth_server . $api_url;
			}
			
			if ( $api_headers != NULL AND !empty($api_headers) ) {
				$d_header = array_merge( $d_header, $api_headers ); // Merge Default Headers with additional headers
			}
			
			// Roll everything up into final parameters for curl

			$d_options = array(
				CURLOPT_RETURNTRANSFER => true,
				CURLINFO_HEADER_OUT => true,
				CURLOPT_URL => $api_url,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_HTTPHEADER => $d_header
			);
			
			if ( $type == "POST" ) {
				 curl_setopt($this->curl_handle, CURLOPT_POST, 1);
				 if ( !empty($api_params) AND $api_params != NULL) {
					curl_setopt($this->curl_handle, CURLOPT_POSTFIELDS, $api_params);
				 }
			} elseif ( $type == "PUT" ) {
				curl_setopt($this->curl_handle, CURLOPT_CUSTOMREQUEST, "PUT");
				 if ( !empty($api_params) AND $api_params != NULL) {
					curl_setopt($this->curl_handle, CURLOPT_POSTFIELDS, $api_params);
				}
			} else {
				
			}
			
			if ( $this->debug ) {
				echo "URL: " . $api_url . "<hr>";
				/* echo $this->debug_display( $d_options, "Options" );
				echo $this->debug_display( $d_header, "Headers" );
				echo $this->debug_display( $api_params, "parameters" ); */
			}

			curl_setopt_array($this->curl_handle, $d_options); // Set all options

			$session_data = json_decode( curl_exec($this->curl_handle), true ); // Execute Curl function and store return & decode json return

			if ( $this->debug ) {
				//echo $this->debug_display( curl_getinfo($this->curl_handle), "CURL Info" );
				echo $this->debug_display( $session_data, "Session Data" );
			}
			
			if ( !empty( $session_data["Code"] ) ) {
				$this->linn_error = $session_data;
				error_log("Linnworks API:api_call>Session Data." . print_r($session_data,true), 0);
				return false;
			} else {
				$this->linn_error = NULL;
				return $session_data; // Assign Season data
			}				
		}

		function api_call_names( $apiname ) {

			$api_calls = array(
					"GetServerUTCTime" => array( "type" => "POST", "url" => "/Api/Auth/GetServerUTCTime", "noparams" => 1),
					"CreateNewCustomer" => array( "type" => "POST", "url" => "/Api/Customer/CreateNewCustomer", "noparams" => 1),
					"ExecuteCustomPagedScript" => array( "type" => "POST", "url" => "/Api/Dashboards/ExecuteCustomPagedScript", "noparams" => 1),
					"ExecuteCustomPagedScript_Customer" => array( "type" => "POST", "url" => "/Api/Dashboards/ExecuteCustomPagedScript_Customer", "noparams" => 1),
					"ExecuteCustomScriptQuery" => array( "type" => "POST", "url" => "/Api/Dashboards/ExecuteCustomScriptQuery", "noparams" => 1),
					"GetInventoryLocationCategoriesData" => array( "type" => "POST", "url" => "/Api/Dashboards/GetInventoryLocationCategoriesData", "noparams" => 1),
					"GetInventoryLocationData" => array( "type" => "POST", "url" => "/Api/Dashboards/GetInventoryLocationData", "noparams" => 1),
					"GetInventoryLocationProductsData" => array( "type" => "POST", "url" => "/Api/Dashboards/GetInventoryLocationProductsData", "noparams" => 1),
					"GetLowStockLevel" => array( "type" => "POST", "url" => "/Api/Dashboards/GetLowStockLevel", "noparams" => 1),
					"GetPerformanceDetail" => array( "type" => "POST", "url" => "/Api/Dashboards/GetPerformanceDetail", "noparams" => 1),
					"GetPerformanceTableData" => array( "type" => "POST", "url" => "/Api/Dashboards/GetPerformanceTableData", "noparams" => 1),
					"GetTopProducts" => array( "type" => "POST", "url" => "/Api/Dashboards/GetTopProducts", "noparams" => 1),
					"GenerateAdhocEmail" => array( "type" => "POST", "url" => "/Api/Email/GenerateAdhocEmail", "noparams" => 1),
					"GenerateFreeTextEmail" => array( "type" => "POST", "url" => "/Api/Email/GenerateFreeTextEmail", "noparams" => 1),
					"GetEmailTemplate" => array( "type" => "POST", "url" => "/Api/Email/GetEmailTemplate", "noparams" => 1),
					"GetEmailTemplates" => array( "type" => "POST", "url" => "/Api/Email/GetEmailTemplates", "noparams" => 1),
					"DeleteSetting" => array( "type" => "POST", "url" => "/Api/Extensions/DeleteSetting", "noparams" => 1),
					"GetSetting" => array( "type" => "POST", "url" => "/Api/Extensions/GetSetting", "noparams" => 1),
					"GetSettingKeys" => array( "type" => "POST", "url" => "/Api/Extensions/GetSettingKeys", "noparams" => 1),
					"GetSettings" => array( "type" => "POST", "url" => "/Api/Extensions/GetSettings", "noparams" => 1),
					"SetSetting" => array( "type" => "POST", "url" => "/Api/Extensions/SetSetting", "noparams" => 1),
					"DeleteExport" => array( "type" => "POST", "url" => "/Api/ImportExport/DeleteExport", "noparams" => 1),
					"DeleteImport" => array( "type" => "POST", "url" => "/Api/ImportExport/DeleteImport", "noparams" => 1),
					"DownloadImportedFile" => array( "type" => "POST", "url" => "/Api/ImportExport/DownloadImportedFile", "noparams" => 1),
					"EnableExport" => array( "type" => "POST", "url" => "/Api/ImportExport/EnableExport", "noparams" => 1),
					"EnableImport" => array( "type" => "POST", "url" => "/Api/ImportExport/EnableImport", "noparams" => 1),
					"GetExport" => array( "type" => "POST", "url" => "/Api/ImportExport/GetExport", "noparams" => 1),
					"GetExportList" => array( "type" => "POST", "url" => "/Api/ImportExport/GetExportList", "noparams" => 1),
					"GetFullfilmentCenterSettings" => array( "type" => "POST", "url" => "/Api/ImportExport/GetFullfilmentCenterSettings", "noparams" => 1),
					"GetImport" => array( "type" => "POST", "url" => "/Api/ImportExport/GetImport", "noparams" => 1),
					"GetImportList" => array( "type" => "POST", "url" => "/Api/ImportExport/GetImportList", "noparams" => 1),
					"RunNowExport" => array( "type" => "POST", "url" => "/Api/ImportExport/RunNowExport", "noparams" => 1),
					"RunNowImport" => array( "type" => "POST", "url" => "/Api/ImportExport/RunNowImport", "noparams" => 1),
					"AddImageToInventoryItem" => array( "type" => "POST", "url" => "/Api/Inventory/AddImageToInventoryItem", "noparams" => 1),
					"AddInventoryItem" => array( "type" => "POST", "url" => "/Api/Inventory/AddInventoryItem", "noparams" => 1),
					"AddItemLocations" => array( "type" => "POST", "url" => "/Api/Inventory/AddItemLocations", "noparams" => 1),
					"AddProductIdentifiers" => array( "type" => "POST", "url" => "/Api/Inventory/AddProductIdentifiers", "noparams" => 1),
					"AddScrapCategories" => array( "type" => "POST", "url" => "/Api/Inventory/AddScrapCategories", "noparams" => 1),
					"AddScrapItem" => array( "type" => "POST", "url" => "/Api/Inventory/AddScrapItem", "noparams" => 1),
					"AddSupplier" => array( "type" => "POST", "url" => "/Api/Inventory/AddSupplier", "noparams" => 1),
					"AdjustEbayTemplatesDispatchLMS" => array( "type" => "POST", "url" => "/Api/Inventory/AdjustEbayTemplatesDispatchLMS", "noparams" => 1),
					"AdjustEbayTemplatesInstantLMS" => array( "type" => "POST", "url" => "/Api/Inventory/AdjustEbayTemplatesInstantLMS", "noparams" => 1),
					"AdjustTemplatesInstant" => array( "type" => "POST", "url" => "/Api/Inventory/AdjustTemplatesInstant", "noparams" => 1),
					"ArchiveInventoryItems" => array( "type" => "POST", "url" => "/Api/Inventory/ArchiveInventoryItems", "noparams" => 1),
					"BatchGetInventoryItemChannelSKUs" => array( "type" => "POST", "url" => "/Api/Inventory/BatchGetInventoryItemChannelSKUs", "noparams" => 1),
					"BulkScrapBatchedItems" => array( "type" => "POST", "url" => "/Api/Inventory/BulkScrapBatchedItems", "noparams" => 1),
					"CreateBatches" => array( "type" => "POST", "url" => "/Api/Inventory/CreateBatches", "noparams" => 1),
					"CreateCategory" => array( "type" => "POST", "url" => "/Api/Inventory/CreateCategory", "noparams" => 1),
					"CreateCountries" => array( "type" => "POST", "url" => "/Api/Inventory/CreateCountries", "noparams" => 1),
					"CreateCountryRegions" => array( "type" => "POST", "url" => "/Api/Inventory/CreateCountryRegions", "noparams" => 1),
					"CreateInventoryItemChannelSKUs" => array( "type" => "POST", "url" => "/Api/Inventory/CreateInventoryItemChannelSKUs", "noparams" => 1),
					"CreateInventoryItemCompositions" => array( "type" => "POST", "url" => "/Api/Inventory/CreateInventoryItemCompositions", "noparams" => 1),
					"CreateInventoryItemDescriptions" => array( "type" => "POST", "url" => "/Api/Inventory/CreateInventoryItemDescriptions", "noparams" => 1),
					"CreateInventoryItemExtendedProperties" => array( "type" => "POST", "url" => "/Api/Inventory/CreateInventoryItemExtendedProperties", "noparams" => 1),
					"CreateInventoryItemPrices" => array( "type" => "POST", "url" => "/Api/Inventory/CreateInventoryItemPrices", "noparams" => 1),
					"CreateInventoryItemPricingRules" => array( "type" => "POST", "url" => "/Api/Inventory/CreateInventoryItemPricingRules", "noparams" => 1),
					"CreateInventoryItemTitles" => array( "type" => "POST", "url" => "/Api/Inventory/CreateInventoryItemTitles", "noparams" => 1),
					"CreateStockSupplierStat" => array( "type" => "POST", "url" => "/Api/Inventory/CreateStockSupplierStat", "noparams" => 1),
					"CreateUserSpecificView" => array( "type" => "POST", "url" => "/Api/Inventory/CreateUserSpecificView", "noparams" => 1),
					"DeleteBatchesByStockItemId" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteBatchesByStockItemId", "noparams" => 1),
					"DeleteBatchInventoryInBulk" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteBatchInventoryInBulk", "noparams" => 1),
					"DeleteCategoryById" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteCategoryById", "noparams" => 1),
					"DeleteCountries" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteCountries", "noparams" => 1),
					"DeleteEbayCompatibilityList" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteEbayCompatibilityList", "noparams" => 1),
					"DeleteImagesFromInventoryItem" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteImagesFromInventoryItem", "noparams" => 1),
					"DeleteInventoryItemChannelSKUs" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteInventoryItemChannelSKUs", "noparams" => 1),
					"DeleteInventoryItemCompositions" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteInventoryItemCompositions", "noparams" => 1),
					"DeleteInventoryItemDescriptions" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteInventoryItemDescriptions", "noparams" => 1),
					"DeleteInventoryItemExtendedProperties" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteInventoryItemExtendedProperties", "noparams" => 1),
					"DeleteInventoryItemPrices" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteInventoryItemPrices", "noparams" => 1),
					"DeleteInventoryItemPricingRules" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteInventoryItemPricingRules", "noparams" => 1),
					"DeleteInventoryItems" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteInventoryItems", "noparams" => 1),
					"DeleteInventoryItemTitles" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteInventoryItemTitles", "noparams" => 1),
					"DeleteItemLocations" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteItemLocations", "noparams" => 1),
					"DeleteProductIdentifiers" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteProductIdentifiers", "noparams" => 1),
					"DeleteScrapCategories" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteScrapCategories", "noparams" => 1),
					"DeleteStockSupplierStat" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteStockSupplierStat", "noparams" => 1),
					"DeleteSuppliers" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteSuppliers", "noparams" => 1),
					"DeleteUserSpecificView" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteUserSpecificView", "noparams" => 1),
					"DuplicateInventoryItem" => array( "type" => "POST", "url" => "/Api/Inventory/DuplicateInventoryItem", "noparams" => 1),
					"GetAllExtendedPropertyNames" => array( "type" => "POST", "url" => "/Api/Inventory/GetAllExtendedPropertyNames", "noparams" => 1),
					"GetBatchAudit" => array( "type" => "POST", "url" => "/Api/Inventory/GetBatchAudit", "noparams" => 1),
					"GetBatchesByStockItemId" => array( "type" => "POST", "url" => "/Api/Inventory/GetBatchesByStockItemId", "noparams" => 1),
					"GetBatchInventoryById" => array( "type" => "POST", "url" => "/Api/Inventory/GetBatchInventoryById", "noparams" => 1),
					"GetCategories" => array( "type" => "POST", "url" => "/Api/Inventory/GetCategories", "noparams" => 0),
					"GetChannels" => array( "type" => "POST", "url" => "/Api/Inventory/GetChannels", "noparams" => 1),
					"GetChannelsBySource" => array( "type" => "POST", "url" => "/Api/Inventory/GetChannelsBySource", "noparams" => 1),
					"GetCountries" => array( "type" => "POST", "url" => "/Api/Inventory/GetCountries", "noparams" => 1),
					"GetCountryCodes" => array( "type" => "POST", "url" => "/Api/Inventory/GetCountryCodes", "noparams" => 1),
					"GetEbayCompatibilityList" => array( "type" => "POST", "url" => "/Api/Inventory/GetEbayCompatibilityList", "noparams" => 1),
					"GetExtendedPropertyNames" => array( "type" => "POST", "url" => "/Api/Inventory/GetExtendedPropertyNames", "noparams" => 1),
					"GetExtendedPropertyTypes" => array( "type" => "POST", "url" => "/Api/Inventory/GetExtendedPropertyTypes", "noparams" => 1),
					"GetImagesInBulk" => array( "type" => "POST", "url" => "/Api/Inventory/GetImagesInBulk", "noparams" => 1),
					"GetInventoryBatchTypes" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryBatchTypes", "noparams" => 1),
					"GetInventoryItemAuditTrail" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryItemAuditTrail", "noparams" => 1),
					"GetInventoryItemBatchInformation" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryItemBatchInformation", "noparams" => 1),
					"GetInventoryItemBatchInformationByIds" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryItemBatchInformationByIds", "noparams" => 1),
					"GetInventoryItemById" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryItemById", "noparams" => 1),
					"GetInventoryItemChannelSKUs" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryItemChannelSKUs", "noparams" => 1),
					"GetInventoryItemChannelSKUsWithLocation" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryItemChannelSKUsWithLocation", "noparams" => 1),
					"GetInventoryItemCompositions" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryItemCompositions", "noparams" => 1),
					"GetInventoryItemDescriptions" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryItemDescriptions", "noparams" => 1),
					"GetInventoryItemExtendedProperties" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryItemExtendedProperties", "noparams" => 1),
					"GetInventoryItemImages" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryItemImages", "noparams" => 1),
					"GetInventoryItemLocations" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryItemLocations", "noparams" => 1),
					"GetInventoryItemPriceChannelSuffixes" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryItemPriceChannelSuffixes", "noparams" => 1),
					"GetInventoryItemPriceRulesById" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryItemPriceRulesById", "noparams" => 1),
					"GetInventoryItemPriceRulesBySource" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryItemPriceRulesBySource", "noparams" => 1),
					"GetInventoryItemPrices" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryItemPrices", "noparams" => 1),
					"GetInventoryItemPriceTags" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryItemPriceTags", "noparams" => 1),
					"GetInventoryItemsCompositionByIds" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryItemsCompositionByIds", "noparams" => 1),
					"GetInventoryItemsCount" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryItemsCount", "noparams" => 1),
					"GetInventoryItemTitles" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryItemTitles", "noparams" => 1),
					"GetNewItemNumber" => array( "type" => "POST", "url" => "/Api/Inventory/GetNewItemNumber", "noparams" => 1),
					"GetPackageGroups" => array( "type" => "POST", "url" => "/Api/Inventory/GetPackageGroups", "noparams" => 1),
					"GetPostalServices" => array( "type" => "POST", "url" => "/Api/Inventory/GetPostalServices", "noparams" => 1),
					"GetPreDefinedViews" => array( "type" => "POST", "url" => "/Api/Inventory/GetPreDefinedViews", "noparams" => 1),
					"GetProductIdentifiersBulkByStockItemId" => array( "type" => "POST", "url" => "/Api/Inventory/GetProductIdentifiersBulkByStockItemId", "noparams" => 1),
					"GetProductIdentifiersByStockItemId" => array( "type" => "POST", "url" => "/Api/Inventory/GetProductIdentifiersByStockItemId", "noparams" => 1),
					"GetProductIdentifierTypes" => array( "type" => "POST", "url" => "/Api/Inventory/GetProductIdentifierTypes", "noparams" => 1),
					"GetScrapCategories" => array( "type" => "POST", "url" => "/Api/Inventory/GetScrapCategories", "noparams" => 1),
					"GetScrapHistory" => array( "type" => "POST", "url" => "/Api/Inventory/GetScrapHistory", "noparams" => 1),
					"GetStockItemBatchesByLocation" => array( "type" => "POST", "url" => "/Api/Inventory/GetStockItemBatchesByLocation", "noparams" => 1),
					"GetStockItemIdsBySKU" => array( "type" => "POST", "url" => "/Api/Inventory/GetStockItemIdsBySKU", "noparams" => 1),
					"GetStockItemLabels" => array( "type" => "POST", "url" => "/Api/Inventory/GetStockItemLabels", "noparams" => 1),
					"GetStockLocations" => array( "type" => "POST", "url" => "/Api/Inventory/GetStockLocations", "noparams" => 1),
					"GetStockSupplierStat" => array( "type" => "POST", "url" => "/Api/Inventory/GetStockSupplierStat", "noparams" => 1),
					"GetSupplierDetails" => array( "type" => "POST", "url" => "/Api/Inventory/GetSupplierDetails", "noparams" => 1),
					"GetSuppliers" => array( "type" => "POST", "url" => "/Api/Inventory/GetSuppliers", "noparams" => 1),
					"GetUserSpecificViews" => array( "type" => "POST", "url" => "/Api/Inventory/GetUserSpecificViews", "noparams" => 1),
					"HasStockItemBatches" => array( "type" => "POST", "url" => "/Api/Inventory/HasStockItemBatches", "noparams" => 1),
					"HasStockItemStockLevel" => array( "type" => "POST", "url" => "/Api/Inventory/HasStockItemStockLevel", "noparams" => 1),
					"InsertUpdateEbayCompatibilityList" => array( "type" => "POST", "url" => "/Api/Inventory/InsertUpdateEbayCompatibilityList", "noparams" => 1),
					"IsInventoryItemChannelSKULinked" => array( "type" => "POST", "url" => "/Api/Inventory/IsInventoryItemChannelSKULinked", "noparams" => 1),
					"IsOwnedStockLocation" => array( "type" => "POST", "url" => "/Api/Inventory/IsOwnedStockLocation", "noparams" => 1),
					"ScrapBatchedItem" => array( "type" => "POST", "url" => "/Api/Inventory/ScrapBatchedItem", "noparams" => 1),
					"SetInventoryItemImageAsMain" => array( "type" => "POST", "url" => "/Api/Inventory/SetInventoryItemImageAsMain", "noparams" => 1),
					"UnarchiveInventoryItems" => array( "type" => "POST", "url" => "/Api/Inventory/UnarchiveInventoryItems", "noparams" => 1),
					"UnlinkChannelListing" => array( "type" => "POST", "url" => "/Api/Inventory/UnlinkChannelListing", "noparams" => 1),
					"UpdateBatchDetails" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateBatchDetails", "noparams" => 1),
					"UpdateBatchesWithInventory" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateBatchesWithInventory", "noparams" => 1),
					"UpdateCategory" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateCategory", "noparams" => 1),
					"UpdateCompositeParentStockLevel" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateCompositeParentStockLevel", "noparams" => 1),
					"UpdateCountries" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateCountries", "noparams" => 1),
					"UpdateCountryRegions" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateCountryRegions", "noparams" => 1),
					"UpdateImages" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateImages", "noparams" => 1),
					"UpdateInventoryItem" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateInventoryItem", "noparams" => 1),
					"UpdateInventoryItemChannelSKUs" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateInventoryItemChannelSKUs", "noparams" => 1),
					"UpdateInventoryItemChannelSKUsWithLocation" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateInventoryItemChannelSKUsWithLocation", "noparams" => 1),
					"UpdateInventoryItemCompositions" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateInventoryItemCompositions", "noparams" => 1),
					"UpdateInventoryItemDescriptions" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateInventoryItemDescriptions", "noparams" => 1),
					"UpdateInventoryItemExtendedProperties" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateInventoryItemExtendedProperties", "noparams" => 1),
					"UpdateInventoryItemField" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateInventoryItemField", "noparams" => 1),
					"UpdateInventoryItemLevels" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateInventoryItemLevels", "noparams" => 1),
					"UpdateInventoryItemLocationField" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateInventoryItemLocationField", "noparams" => 1),
					"UpdateInventoryItemPrices" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateInventoryItemPrices", "noparams" => 1),
					"UpdateInventoryItemPricingRules" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateInventoryItemPricingRules", "noparams" => 1),
					"UpdateInventoryItemStockField" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateInventoryItemStockField", "noparams" => 1),
					"UpdateInventoryItemTitles" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateInventoryItemTitles", "noparams" => 1),
					"UpdateItemLocations" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateItemLocations", "noparams" => 1),
					"UpdateProductIdentifiers" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateProductIdentifiers", "noparams" => 1),
					"UpdateScrapCategories" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateScrapCategories", "noparams" => 1),
					"UpdateStockSupplierStat" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateStockSupplierStat", "noparams" => 1),
					"UpdateSupplier" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateSupplier", "noparams" => 1),
					"UpdateUserSpecificView" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateUserSpecificView", "noparams" => 1),
					"UploadImagesToInventoryItem" => array( "type" => "POST", "url" => "/Api/Inventory/UploadImagesToInventoryItem", "noparams" => 1),
					"EndListingsPendingRelist" => array( "type" => "POST", "url" => "/Api/Listings/EndListingsPendingRelist", "noparams" => 1),
					"GetEbayListingAudit" => array( "type" => "POST", "url" => "/Api/Listings/GetEbayListingAudit", "noparams" => 1),
					"SetListingStrikeOffState" => array( "type" => "POST", "url" => "/Api/Listings/SetListingStrikeOffState", "noparams" => 1),
					"AddLocation" => array( "type" => "POST", "url" => "/Api/Locations/AddLocation", "noparams" => 1),
					"DeleteLocation" => array( "type" => "POST", "url" => "/Api/Locations/DeleteLocation", "noparams" => 1),
					"DeleteWarehouseTOTE" => array( "type" => "POST", "url" => "/Api/Locations/DeleteWarehouseTOTE", "noparams" => 1),
					"GetLocation" => array( "type" => "POST", "url" => "/Api/Locations/GetLocation", "noparams" => 1),
					"GetWarehouseTOTEs" => array( "type" => "POST", "url" => "/Api/Locations/GetWarehouseTOTEs", "noparams" => 1),
					"UpdateLocation" => array( "type" => "POST", "url" => "/Api/Locations/UpdateLocation", "noparams" => 1),
					"GetInstalledMacros" => array( "type" => "POST", "url" => "/Api/Macro/GetInstalledMacros", "noparams" => 1),
					"GetMacroConfigurations" => array( "type" => "POST", "url" => "/Api/Macro/GetMacroConfigurations", "noparams" => 1),
					"AddCoupon" => array( "type" => "POST", "url" => "/Api/Orders/AddCoupon", "noparams" => 1),
					"AddOrderItem" => array( "type" => "POST", "url" => "/Api/Orders/AddOrderItem", "noparams" => 1),
					"AddOrderService" => array( "type" => "POST", "url" => "/Api/Orders/AddOrderService", "noparams" => 1),
					"AssignOrderItemBatches" => array( "type" => "POST", "url" => "/Api/Orders/AssignOrderItemBatches", "noparams" => 1),
					"AssignStockToOrder" => array( "type" => "POST", "url" => "/Api/Orders/AssignStockToOrder", "noparams" => 1),
					"AssignToFolder" => array( "type" => "POST", "url" => "/Api/Orders/AssignToFolder", "noparams" => 1),
					"CancelOrder" => array( "type" => "POST", "url" => "/Api/Orders/CancelOrder", "noparams" => 1),
					"ChangeOrderTag" => array( "type" => "POST", "url" => "/Api/Orders/ChangeOrderTag", "noparams" => 1),
					"ChangeShippingMethod" => array( "type" => "POST", "url" => "/Api/Orders/ChangeShippingMethod", "noparams" => 1),
					"ChangeStatus" => array( "type" => "POST", "url" => "/Api/Orders/ChangeStatus", "noparams" => 1),
					"ClearInvoicePrinted" => array( "type" => "POST", "url" => "/Api/Orders/ClearInvoicePrinted", "noparams" => 1),
					"ClearPickListPrinted" => array( "type" => "POST", "url" => "/Api/Orders/ClearPickListPrinted", "noparams" => 1),
					"ClearShippingLabelInfo" => array( "type" => "POST", "url" => "/Api/Orders/ClearShippingLabelInfo", "noparams" => 1),
					"CompleteOrder" => array( "type" => "POST", "url" => "/Api/Orders/CompleteOrder", "noparams" => 1),
					"CreateNewItemAndLink" => array( "type" => "POST", "url" => "/Api/Orders/CreateNewItemAndLink", "noparams" => 1),
					"CreateNewOrder" => array( "type" => "POST", "url" => "/Api/Orders/CreateNewOrder", "noparams" => 1),
					"CreateOrders" => array( "type" => "POST", "url" => "/Api/Orders/CreateOrders", "noparams" => 1),
					"CustomerLookUp" => array( "type" => "POST", "url" => "/Api/Orders/CustomerLookUp", "noparams" => 1),
					"DeleteOrder" => array( "type" => "POST", "url" => "/Api/Orders/DeleteOrder", "noparams" => 1),
					"Get_OpenOrderBasicInfoFromItems" => array( "type" => "POST", "url" => "/Api/Orders/Get_OpenOrderBasicInfoFromItems", "noparams" => 1),
					"GetAllAvailableOrderItemBatchsByOrderId" => array( "type" => "POST", "url" => "/Api/Orders/GetAllAvailableOrderItemBatchsByOrderId", "noparams" => 1),
					"GetAllOpenOrders" => array( "type" => "POST", "url" => "/Api/Orders/GetAllOpenOrders", "noparams" => 1),
					"GetAllOpenOrdersBetweenIndex" => array( "type" => "POST", "url" => "/Api/Orders/GetAllOpenOrdersBetweenIndex", "noparams" => 1),
					"GetAssignedOrderItemBatches" => array( "type" => "POST", "url" => "/Api/Orders/GetAssignedOrderItemBatches", "noparams" => 1),
					"GetAvailableFolders" => array( "type" => "POST", "url" => "/Api/Orders/GetAvailableFolders", "noparams" => 1),
					"GetBatchPilots" => array( "type" => "POST", "url" => "/Api/Orders/GetBatchPilots", "noparams" => 1),
					"GetCountries" => array( "type" => "POST", "url" => "/Api/Orders/GetCountries", "noparams" => 1),
					"GetDefaultPaymentMethodIdForNewOrder" => array( "type" => "POST", "url" => "/Api/Orders/GetDefaultPaymentMethodIdForNewOrder", "noparams" => 1),
					"GetDraftOrders" => array( "type" => "POST", "url" => "/Api/Orders/GetDraftOrders", "noparams" => 1),
					"GetExtendedProperties" => array( "type" => "POST", "url" => "/Api/Orders/GetExtendedProperties", "noparams" => 1),
					"GetExtendedPropertyNames" => array( "type" => "POST", "url" => "/Api/Orders/GetExtendedPropertyNames", "noparams" => 1),
					"GetExtendedPropertyTypes" => array( "type" => "POST", "url" => "/Api/Orders/GetExtendedPropertyTypes", "noparams" => 1),
					"GetLinkedItems" => array( "type" => "POST", "url" => "/Api/Orders/GetLinkedItems", "noparams" => 1),
					"GetOpenOrderIdByOrderOrReferenceId" => array( "type" => "POST", "url" => "/Api/Orders/GetOpenOrderIdByOrderOrReferenceId", "noparams" => 1),
					"GetOpenOrderItemsSuppliers" => array( "type" => "POST", "url" => "/Api/Orders/GetOpenOrderItemsSuppliers", "noparams" => 1),
					"GetOpenOrders" => array( "type" => "POST", "url" => "/Api/Orders/GetOpenOrders", "noparams" => 1),
					"GetOpenOrdersByItemBarcode" => array( "type" => "POST", "url" => "/Api/Orders/GetOpenOrdersByItemBarcode", "noparams" => 1),
					"GetOrder" => array( "type" => "POST", "url" => "/Api/Orders/GetOrder", "noparams" => 1),
					"GetOrderAuditTrail" => array( "type" => "POST", "url" => "/Api/Orders/GetOrderAuditTrail", "noparams" => 1),
					"GetOrderAuditTrailsByIds" => array( "type" => "POST", "url" => "/Api/Orders/GetOrderAuditTrailsByIds", "noparams" => 1),
					"GetOrderById" => array( "type" => "POST", "url" => "/Api/Orders/GetOrderById", "noparams" => 1),
					"GetOrderDetailsByNumOrderId" => array( "type" => "POST", "url" => "/Api/Orders/GetOrderDetailsByNumOrderId", "noparams" => 1),
					"GetOrderDetailsByReferenceId" => array( "type" => "POST", "url" => "/Api/Orders/GetOrderDetailsByReferenceId", "noparams" => 1),
					"GetOrderItemBatchesByOrderIds" => array( "type" => "POST", "url" => "/Api/Orders/GetOrderItemBatchesByOrderIds", "noparams" => 1),
					"GetOrderItemBatchsByOrderId" => array( "type" => "POST", "url" => "/Api/Orders/GetOrderItemBatchsByOrderId", "noparams" => 1),
					"GetOrderItemComposition" => array( "type" => "POST", "url" => "/Api/Orders/GetOrderItemComposition", "noparams" => 1),
					"GetOrderItems" => array( "type" => "POST", "url" => "/Api/Orders/GetOrderItems", "noparams" => 1),
					"GetOrderNotes" => array( "type" => "POST", "url" => "/Api/Orders/GetOrderNotes", "noparams" => 1),
					"GetOrderNoteTypes" => array( "type" => "POST", "url" => "/Api/Orders/GetOrderNoteTypes", "noparams" => 1),
					"GetOrderPackagingCalculation" => array( "type" => "POST", "url" => "/Api/Orders/GetOrderPackagingCalculation", "noparams" => 1),
					"GetOrderPackagingSplit" => array( "type" => "POST", "url" => "/Api/Orders/GetOrderPackagingSplit", "noparams" => 1),
					"GetOrderRelations" => array( "type" => "POST", "url" => "/Api/Orders/GetOrderRelations", "noparams" => 1),
					"GetOrders" => array( "type" => "POST", "url" => "/Api/Orders/GetOrders", "noparams" => 1),
					"GetOrdersById" => array( "type" => "POST", "url" => "/Api/Orders/GetOrdersById", "noparams" => 1),
					"GetOrderView" => array( "type" => "POST", "url" => "/Api/Orders/GetOrderView", "noparams" => 1),
					"GetOrderViews" => array( "type" => "POST", "url" => "/Api/Orders/GetOrderViews", "noparams" => 1),
					"GetOrderXml" => array( "type" => "POST", "url" => "/Api/Orders/GetOrderXml", "noparams" => 1),
					"GetOrderXmlJSTree" => array( "type" => "POST", "url" => "/Api/Orders/GetOrderXmlJSTree", "noparams" => 1),
					"GetPackagingGroups" => array( "type" => "POST", "url" => "/Api/Orders/GetPackagingGroups", "noparams" => 1),
					"GetPaymentMethods" => array( "type" => "POST", "url" => "/Api/Orders/GetPaymentMethods", "noparams" => 1),
					"GetShippingMethods" => array( "type" => "POST", "url" => "/Api/Orders/GetShippingMethods", "noparams" => 1),
					"GetUserLocationId" => array( "type" => "POST", "url" => "/Api/Orders/GetUserLocationId", "noparams" => 1),
					"LockOrder" => array( "type" => "POST", "url" => "/Api/Orders/LockOrder", "noparams" => 1),
					"MergeOrders" => array( "type" => "POST", "url" => "/Api/Orders/MergeOrders", "noparams" => 1),
					"MoveToFulfilmentCenter" => array( "type" => "POST", "url" => "/Api/Orders/MoveToFulfilmentCenter", "noparams" => 1),
					"MoveToLocation" => array( "type" => "POST", "url" => "/Api/Orders/MoveToLocation", "noparams" => 1),
					"ProcessFulfilmentCentreOrder" => array( "type" => "POST", "url" => "/Api/Orders/ProcessFulfilmentCentreOrder", "noparams" => 1),
					"ProcessOrder" => array( "type" => "POST", "url" => "/Api/Orders/ProcessOrder", "noparams" => 1),
					"ProcessOrder_RequiredBatchScans" => array( "type" => "POST", "url" => "/Api/Orders/ProcessOrder_RequiredBatchScans", "noparams" => 1),
					"ProcessOrderByOrderOrReferenceId" => array( "type" => "POST", "url" => "/Api/Orders/ProcessOrderByOrderOrReferenceId", "noparams" => 1),
					"ProcessOrdersInBatch" => array( "type" => "POST", "url" => "/Api/Orders/ProcessOrdersInBatch", "noparams" => 1),
					"RecalculateSingleOrderPackaging" => array( "type" => "POST", "url" => "/Api/Orders/RecalculateSingleOrderPackaging", "noparams" => 1),
					"RemoveOrderItem" => array( "type" => "POST", "url" => "/Api/Orders/RemoveOrderItem", "noparams" => 1),
					"RunRulesEngine" => array( "type" => "POST", "url" => "/Api/Orders/RunRulesEngine", "noparams" => 1),
					"SaveOrderView" => array( "type" => "POST", "url" => "/Api/Orders/SaveOrderView", "noparams" => 1),
					"SetAdditionalInfo" => array( "type" => "POST", "url" => "/Api/Orders/SetAdditionalInfo", "noparams" => 1),
					"SetAvailableFolders" => array( "type" => "POST", "url" => "/Api/Orders/SetAvailableFolders", "noparams" => 1),
					"SetDefaultPaymentMethodIdForNewOrder" => array( "type" => "POST", "url" => "/Api/Orders/SetDefaultPaymentMethodIdForNewOrder", "noparams" => 1),
					"SetExtendedProperties" => array( "type" => "POST", "url" => "/Api/Orders/SetExtendedProperties", "noparams" => 1),
					"SetInvoicesPrinted" => array( "type" => "POST", "url" => "/Api/Orders/SetInvoicesPrinted", "noparams" => 1),
					"SetLabelsPrinted" => array( "type" => "POST", "url" => "/Api/Orders/SetLabelsPrinted", "noparams" => 1),
					"SetOrderCustomerInfo" => array( "type" => "POST", "url" => "/Api/Orders/SetOrderCustomerInfo", "noparams" => 1),
					"SetOrderGeneralInfo" => array( "type" => "POST", "url" => "/Api/Orders/SetOrderGeneralInfo", "noparams" => 1),
					"SetOrderNotes" => array( "type" => "POST", "url" => "/Api/Orders/SetOrderNotes", "noparams" => 1),
					"SetOrderPackaging" => array( "type" => "POST", "url" => "/Api/Orders/SetOrderPackaging", "noparams" => 1),
					"SetOrderPackagingSplit" => array( "type" => "POST", "url" => "/Api/Orders/SetOrderPackagingSplit", "noparams" => 1),
					"SetOrderShippingInfo" => array( "type" => "POST", "url" => "/Api/Orders/SetOrderShippingInfo", "noparams" => 1),
					"SetOrderSplitPackagingManualOverwrite" => array( "type" => "POST", "url" => "/Api/Orders/SetOrderSplitPackagingManualOverwrite", "noparams" => 1),
					"SetOrderTotalsInfo" => array( "type" => "POST", "url" => "/Api/Orders/SetOrderTotalsInfo", "noparams" => 1),
					"SetPaymentMethods" => array( "type" => "POST", "url" => "/Api/Orders/SetPaymentMethods", "noparams" => 1),
					"SetPickListPrinted" => array( "type" => "POST", "url" => "/Api/Orders/SetPickListPrinted", "noparams" => 1),
					"SplitOrder" => array( "type" => "POST", "url" => "/Api/Orders/SplitOrder", "noparams" => 1),
					"UnassignToFolder" => array( "type" => "POST", "url" => "/Api/Orders/UnassignToFolder", "noparams" => 1),
					"UpdateAdditionalInfo" => array( "type" => "POST", "url" => "/Api/Orders/UpdateAdditionalInfo", "noparams" => 1),
					"UpdateBillingAddress" => array( "type" => "POST", "url" => "/Api/Orders/UpdateBillingAddress", "noparams" => 1),
					"UpdateLinkItem" => array( "type" => "POST", "url" => "/Api/Orders/UpdateLinkItem", "noparams" => 1),
					"UpdateOrderItem" => array( "type" => "POST", "url" => "/Api/Orders/UpdateOrderItem", "noparams" => 1),
					"ValidateCoupon" => array( "type" => "POST", "url" => "/Api/Orders/ValidateCoupon", "noparams" => 1),
					"CheckinUser" => array( "type" => "POST", "url" => "/Api/OrderWorkflow/CheckinUser", "noparams" => 1),
					"DeallocateOrderFromJob" => array( "type" => "POST", "url" => "/Api/OrderWorkflow/DeallocateOrderFromJob", "noparams" => 1),
					"GetGroup" => array( "type" => "POST", "url" => "/Api/OrderWorkflow/GetGroup", "noparams" => 1),
					"GetGroupList" => array( "type" => "POST", "url" => "/Api/OrderWorkflow/GetGroupList", "noparams" => 1),
					"GetJob" => array( "type" => "POST", "url" => "/Api/OrderWorkflow/GetJob", "noparams" => 1),
					"GetJobAudit" => array( "type" => "POST", "url" => "/Api/OrderWorkflow/GetJobAudit", "noparams" => 1),
					"GetJobByName" => array( "type" => "POST", "url" => "/Api/OrderWorkflow/GetJobByName", "noparams" => 1),
					"GetJobErrors" => array( "type" => "POST", "url" => "/Api/OrderWorkflow/GetJobErrors", "noparams" => 1),
					"GetPrintAttachment" => array( "type" => "POST", "url" => "/Api/OrderWorkflow/GetPrintAttachment", "noparams" => 1),
					"GetWorkflow" => array( "type" => "POST", "url" => "/Api/OrderWorkflow/GetWorkflow", "noparams" => 1),
					"Run" => array( "type" => "POST", "url" => "/Api/OrderWorkflow/Run", "noparams" => 1),
					"UpdateGroup" => array( "type" => "POST", "url" => "/Api/OrderWorkflow/UpdateGroup", "noparams" => 1),
					"CheckAllocatableToPickwave" => array( "type" => "POST", "url" => "/Api/Picking/CheckAllocatableToPickwave", "noparams" => 1),
					"DeleteOrdersFromPickingWaves" => array( "type" => "POST", "url" => "/Api/Picking/DeleteOrdersFromPickingWaves", "noparams" => 1),
					"GeneratePickingWave" => array( "type" => "POST", "url" => "/Api/Picking/GeneratePickingWave", "noparams" => 1),
					"GetAllPickingWaveHeaders" => array( "type" => "POST", "url" => "/Api/Picking/GetAllPickingWaveHeaders", "noparams" => 1),
					"GetAllPickingWaves" => array( "type" => "POST", "url" => "/Api/Picking/GetAllPickingWaves", "noparams" => 1),
					"GetItemBinracks" => array( "type" => "POST", "url" => "/Api/Picking/GetItemBinracks", "noparams" => 1),
					"GetMyPickingWaveHeaders" => array( "type" => "POST", "url" => "/Api/Picking/GetMyPickingWaveHeaders", "noparams" => 1),
					"GetMyPickingWaves" => array( "type" => "POST", "url" => "/Api/Picking/GetMyPickingWaves", "noparams" => 1),
					"GetPickingWave" => array( "type" => "POST", "url" => "/Api/Picking/GetPickingWave", "noparams" => 1),
					"GetPickwaveUsersWithSummary" => array( "type" => "POST", "url" => "/Api/Picking/GetPickwaveUsersWithSummary", "noparams" => 1),
					"UpdatePickedItemDelta" => array( "type" => "POST", "url" => "/Api/Picking/UpdatePickedItemDelta", "noparams" => 1),
					"UpdatePickingWaveHeader" => array( "type" => "POST", "url" => "/Api/Picking/UpdatePickingWaveHeader", "noparams" => 1),
					"UpdatePickingWaveItem" => array( "type" => "POST", "url" => "/Api/Picking/UpdatePickingWaveItem", "noparams" => 1),
					"UpdatePickingWaveItemWithNewBinrack" => array( "type" => "POST", "url" => "/Api/Picking/UpdatePickingWaveItemWithNewBinrack", "noparams" => 1),
					"CreatePostalService" => array( "type" => "POST", "url" => "/Api/PostalServices/CreatePostalService", "noparams" => 1),
					"DeletePostalService" => array( "type" => "POST", "url" => "/Api/PostalServices/DeletePostalService", "noparams" => 1),
					"GetChannelLinks" => array( "type" => "POST", "url" => "/Api/PostalServices/GetChannelLinks", "noparams" => 1),
					"GetPostalServices" => array( "type" => "POST", "url" => "/Api/PostalServices/GetPostalServices", "noparams" => 1),
					"UpdatePostalService" => array( "type" => "POST", "url" => "/Api/PostalServices/UpdatePostalService", "noparams" => 1),
					"CreateCancellation" => array( "type" => "POST", "url" => "/Api/PostSale/CreateCancellation", "noparams" => 1),
					"CreatePDFfromJobForceTemplate" => array( "type" => "POST", "url" => "/Api/PrintService/CreatePDFfromJobForceTemplate", "noparams" => 1),
					"CreatePDFfromJobForceTemplateStockIn" => array( "type" => "POST", "url" => "/Api/PrintService/CreatePDFfromJobForceTemplateStockIn", "noparams" => 1),
					"CreatePDFfromJobForceTemplateWithQuantities" => array( "type" => "POST", "url" => "/Api/PrintService/CreatePDFfromJobForceTemplateWithQuantities", "noparams" => 1),
					"CreateReturnShippingLabelsPDF" => array( "type" => "POST", "url" => "/Api/PrintService/CreateReturnShippingLabelsPDF", "noparams" => 1),
					"CreateReturnShippingLabelsPDFWithSKUs" => array( "type" => "POST", "url" => "/Api/PrintService/CreateReturnShippingLabelsPDFWithSKUs", "noparams" => 1),
					"GetTemplateList" => array( "type" => "POST", "url" => "/Api/PrintService/GetTemplateList", "noparams" => 1),
					"GetUsersForPrinterConfig" => array( "type" => "POST", "url" => "/Api/PrintService/GetUsersForPrinterConfig", "noparams" => 1),
					"PrintTemplatePreview" => array( "type" => "POST", "url" => "/Api/PrintService/PrintTemplatePreview", "noparams" => 1),
					"VP_GetPrinters" => array( "type" => "POST", "url" => "/Api/PrintService/VP_GetPrinters", "noparams" => 1),
					"AddOrderNote" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/AddOrderNote", "noparams" => 1),
					"AddReturnCategory" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/AddReturnCategory", "noparams" => 1),
					"ChangeOrderNote" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/ChangeOrderNote", "noparams" => 1),
					"CheckOrderFullyReturned" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/CheckOrderFullyReturned", "noparams" => 1),
					"CreateExchange" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/CreateExchange", "noparams" => 1),
					"CreateFullResend" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/CreateFullResend", "noparams" => 1),
					"CreateResend" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/CreateResend", "noparams" => 1),
					"CreateReturn" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/CreateReturn", "noparams" => 1),
					"DeleteOrderNote" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/DeleteOrderNote", "noparams" => 1),
					"DeleteReturnCategory" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/DeleteReturnCategory", "noparams" => 1),
					"DownloadOrdersToCSV" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/DownloadOrdersToCSV", "noparams" => 1),
					"GetChannelRefundReasons" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/GetChannelRefundReasons", "noparams" => 1),
					"GetOrderInfo" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/GetOrderInfo", "noparams" => 1),
					"GetPackageSplit" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/GetPackageSplit", "noparams" => 1),
					"GetProcessedAuditTrail" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/GetProcessedAuditTrail", "noparams" => 1),
					"GetProcessedOrderExtendedProperties" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/GetProcessedOrderExtendedProperties", "noparams" => 1),
					"GetProcessedOrderNotes" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/GetProcessedOrderNotes", "noparams" => 1),
					"GetProcessedRelatives" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/GetProcessedRelatives", "noparams" => 1),
					"GetRefundableServiceItems" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/GetRefundableServiceItems", "noparams" => 1),
					"GetRefunds" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/GetRefunds", "noparams" => 1),
					"GetRefundsOptions" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/GetRefundsOptions", "noparams" => 1),
					"GetReturnCategories" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/GetReturnCategories", "noparams" => 1),
					"GetReturnItemsInfo" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/GetReturnItemsInfo", "noparams" => 1),
					"GetReturnOrderInfo" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/GetReturnOrderInfo", "noparams" => 1),
					"GetReturnsExchanges" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/GetReturnsExchanges", "noparams" => 1),
					"GetTotalRefunds" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/GetTotalRefunds", "noparams" => 1),
					"IsRefundValid" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/IsRefundValid", "noparams" => 1),
					"IsRefundValidationRequiredByOrderId" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/IsRefundValidationRequiredByOrderId", "noparams" => 1),
					"MarkManualRefundsAsActioned" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/MarkManualRefundsAsActioned", "noparams" => 1),
					"RefundFreeText" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/RefundFreeText", "noparams" => 1),
					"RefundServices" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/RefundServices", "noparams" => 1),
					"RefundShipping" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/RefundShipping", "noparams" => 1),
					"RenameReturnCategory" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/RenameReturnCategory", "noparams" => 1),
					"SearchProcessedOrders" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/SearchProcessedOrders", "noparams" => 1),
					"SearchProcessedOrdersPaged" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/SearchProcessedOrdersPaged", "noparams" => 1),
					"ValidateCompleteOrderRefund" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/ValidateCompleteOrderRefund", "noparams" => 1),
					"Add_AdditionalCostTypes" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Add_AdditionalCostTypes", "noparams" => 1),
					"Add_PurchaseOrderExtendedProperty" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Add_PurchaseOrderExtendedProperty", "noparams" => 1),
					"Add_PurchaseOrderItem" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Add_PurchaseOrderItem", "noparams" => 1),
					"Add_PurchaseOrderNote" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Add_PurchaseOrderNote", "noparams" => 1),
					"Change_PurchaseOrderStatus" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Change_PurchaseOrderStatus", "noparams" => 1),
					"Create_PurchaseOrder_Initial" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Create_PurchaseOrder_Initial", "noparams" => 1),
					"Delete_AdditionalCostTypes" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Delete_AdditionalCostTypes", "noparams" => 1),
					"Delete_PurchaseOrder" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Delete_PurchaseOrder", "noparams" => 1),
					"Delete_PurchaseOrderExtendedProperty" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Delete_PurchaseOrderExtendedProperty", "noparams" => 1),
					"Delete_PurchaseOrderItem" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Delete_PurchaseOrderItem", "noparams" => 1),
					"Delete_PurchaseOrderNote" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Delete_PurchaseOrderNote", "noparams" => 1),
					"Deliver_PurchaseItem" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Deliver_PurchaseItem", "noparams" => 1),
					"Deliver_PurchaseItemAll" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Deliver_PurchaseItemAll", "noparams" => 1),
					"Deliver_PurchaseItemAll_ExceptBatchItems" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Deliver_PurchaseItemAll_ExceptBatchItems", "noparams" => 1),
					"Deliver_PurchaseItems_WithQuantity" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Deliver_PurchaseItems_WithQuantity", "noparams" => 1),
					"FindStockItem" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/FindStockItem", "noparams" => 1),
					"Get_Additional_Cost" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Get_Additional_Cost", "noparams" => 1),
					"Get_AdditionalCostTypes" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Get_AdditionalCostTypes", "noparams" => 1),
					"Get_DeliveredRecords" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Get_DeliveredRecords", "noparams" => 1),
					"Get_EmailCSVFile" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Get_EmailCSVFile", "noparams" => 1),
					"Get_EmailsSent" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Get_EmailsSent", "noparams" => 1),
					"Get_Payment_Statement" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Get_Payment_Statement", "noparams" => 1),
					"Get_PurchaseOrder" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Get_PurchaseOrder", "noparams" => 1),
					"Get_PurchaseOrderAudit" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Get_PurchaseOrderAudit", "noparams" => 1),
					"Get_PurchaseOrderExtendedProperty" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Get_PurchaseOrderExtendedProperty", "noparams" => 1),
					"Get_PurchaseOrderItem_OpenOrders" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Get_PurchaseOrderItem_OpenOrders", "noparams" => 1),
					"Get_PurchaseOrderNote" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Get_PurchaseOrderNote", "noparams" => 1),
					"GetPurchaseOrderStatusList" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/GetPurchaseOrderStatusList", "noparams" => 1),
					"GetPurchaseOrdersWithStockItems" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/GetPurchaseOrdersWithStockItems", "noparams" => 1),
					"Modify_AdditionalCost" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Modify_AdditionalCost", "noparams" => 1),
					"Modify_AdditionalCostAllocation" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Modify_AdditionalCostAllocation", "noparams" => 1),
					"Modify_PaymentStatement" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Modify_PaymentStatement", "noparams" => 1),
					"Modify_PurchaseOrderItems_Bulk" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Modify_PurchaseOrderItems_Bulk", "noparams" => 1),
					"Search_PurchaseOrders" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Search_PurchaseOrders", "noparams" => 1),
					"Update_AdditionalCostTypes" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Update_AdditionalCostTypes", "noparams" => 1),
					"Update_PurchaseOrderExtendedProperty" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Update_PurchaseOrderExtendedProperty", "noparams" => 1),
					"Update_PurchaseOrderHeader" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Update_PurchaseOrderHeader", "noparams" => 1),
					"Update_PurchaseOrderItem" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Update_PurchaseOrderItem", "noparams" => 1),
					"AcknowledgeRefundErrors" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/AcknowledgeRefundErrors", "noparams" => 1),
					"AcknowledgeRMAErrors" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/AcknowledgeRMAErrors", "noparams" => 1),
					"ActionBookedOrder" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/ActionBookedOrder", "noparams" => 1),
					"ActionRefund" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/ActionRefund", "noparams" => 1),
					"ActionRMABooking" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/ActionRMABooking", "noparams" => 1),
					"CreateRefund" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/CreateRefund", "noparams" => 1),
					"CreateReturnsRefundsCSV" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/CreateReturnsRefundsCSV", "noparams" => 1),
					"CreateRMABooking" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/CreateRMABooking", "noparams" => 1),
					"DeleteBookedItem" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/DeleteBookedItem", "noparams" => 1),
					"DeleteBookedOrder" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/DeleteBookedOrder", "noparams" => 1),
					"DeletePendingRefundItem" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/DeletePendingRefundItem", "noparams" => 1),
					"DeleteRefund" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/DeleteRefund", "noparams" => 1),
					"DeleteRMA" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/DeleteRMA", "noparams" => 1),
					"EditBookedItemInfo" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/EditBookedItemInfo", "noparams" => 1),
					"GetActionableRefundHeaders" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/GetActionableRefundHeaders", "noparams" => 1),
					"GetActionableRMAHeaders" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/GetActionableRMAHeaders", "noparams" => 1),
					"GetBookedReturnsExchangeOrders" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/GetBookedReturnsExchangeOrders", "noparams" => 1),
					"GetProcessedOrAckedErrorRefundHeaders" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/GetProcessedOrAckedErrorRefundHeaders", "noparams" => 1),
					"GetProcessedOrAckedErrorRMAHeaders" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/GetProcessedOrAckedErrorRMAHeaders", "noparams" => 1),
					"GetRefundHeadersByOrderId" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/GetRefundHeadersByOrderId", "noparams" => 1),
					"GetRefundLinesByHeaderId" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/GetRefundLinesByHeaderId", "noparams" => 1),
					"GetRefundOptions" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/GetRefundOptions", "noparams" => 1),
					"GetRefundOrders" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/GetRefundOrders", "noparams" => 1),
					"GetReturnOptions" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/GetReturnOptions", "noparams" => 1),
					"GetRMAHeadersByOrderId" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/GetRMAHeadersByOrderId", "noparams" => 1),
					"GetSearchTypes" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/GetSearchTypes", "noparams" => 1),
					"GetTotalRefunds" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/GetTotalRefunds", "noparams" => 1),
					"GetWarehouseLocations" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/GetWarehouseLocations", "noparams" => 1),
					"RefundOrder" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/RefundOrder", "noparams" => 1),
					"SearchReturnsRefundsPaged" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/SearchReturnsRefundsPaged", "noparams" => 1),
					"UpdateRefund" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/UpdateRefund", "noparams" => 1),
					"UpdateRMABooking" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/UpdateRMABooking", "noparams" => 1),
					"AddAction" => array( "type" => "POST", "url" => "/Api/RulesEngine/AddAction", "noparams" => 1),
					"CheckConditionNameExists" => array( "type" => "POST", "url" => "/Api/RulesEngine/CheckConditionNameExists", "noparams" => 1),
					"CopyAction" => array( "type" => "POST", "url" => "/Api/RulesEngine/CopyAction", "noparams" => 1),
					"CopyCondition" => array( "type" => "POST", "url" => "/Api/RulesEngine/CopyCondition", "noparams" => 1),
					"CreateDraftFromExisting" => array( "type" => "POST", "url" => "/Api/RulesEngine/CreateDraftFromExisting", "noparams" => 1),
					"CreateNewCondition" => array( "type" => "POST", "url" => "/Api/RulesEngine/CreateNewCondition", "noparams" => 1),
					"CreateNewDraft" => array( "type" => "POST", "url" => "/Api/RulesEngine/CreateNewDraft", "noparams" => 1),
					"CreateNewDraftFromExisting" => array( "type" => "POST", "url" => "/Api/RulesEngine/CreateNewDraftFromExisting", "noparams" => 1),
					"DeleteAction" => array( "type" => "POST", "url" => "/Api/RulesEngine/DeleteAction", "noparams" => 1),
					"DeleteCondition" => array( "type" => "POST", "url" => "/Api/RulesEngine/DeleteCondition", "noparams" => 1),
					"DeleteRuleById" => array( "type" => "POST", "url" => "/Api/RulesEngine/DeleteRuleById", "noparams" => 1),
					"GetActionOptions" => array( "type" => "POST", "url" => "/Api/RulesEngine/GetActionOptions", "noparams" => 1),
					"GetActionTypes" => array( "type" => "POST", "url" => "/Api/RulesEngine/GetActionTypes", "noparams" => 1),
					"GetConditionWeb" => array( "type" => "POST", "url" => "/Api/RulesEngine/GetConditionWeb", "noparams" => 1),
					"GetEvaluationFields" => array( "type" => "POST", "url" => "/Api/RulesEngine/GetEvaluationFields", "noparams" => 1),
					"GetEvaluatorTypes" => array( "type" => "POST", "url" => "/Api/RulesEngine/GetEvaluatorTypes", "noparams" => 1),
					"GetKeyOptions" => array( "type" => "POST", "url" => "/Api/RulesEngine/GetKeyOptions", "noparams" => 1),
					"GetMultiKeyOptions" => array( "type" => "POST", "url" => "/Api/RulesEngine/GetMultiKeyOptions", "noparams" => 1),
					"GetMultiOptions" => array( "type" => "POST", "url" => "/Api/RulesEngine/GetMultiOptions", "noparams" => 1),
					"GetOptions" => array( "type" => "POST", "url" => "/Api/RulesEngine/GetOptions", "noparams" => 1),
					"GetRequiredFieldsByRuleId" => array( "type" => "POST", "url" => "/Api/RulesEngine/GetRequiredFieldsByRuleId", "noparams" => 1),
					"GetRequiredFieldsByType" => array( "type" => "POST", "url" => "/Api/RulesEngine/GetRequiredFieldsByType", "noparams" => 1),
					"GetRuleConditionNodes" => array( "type" => "POST", "url" => "/Api/RulesEngine/GetRuleConditionNodes", "noparams" => 1),
					"GetRules" => array( "type" => "POST", "url" => "/Api/RulesEngine/GetRules", "noparams" => 1),
					"GetRulesByType" => array( "type" => "POST", "url" => "/Api/RulesEngine/GetRulesByType", "noparams" => 1),
					"GetValuesFromExisting" => array( "type" => "POST", "url" => "/Api/RulesEngine/GetValuesFromExisting", "noparams" => 1),
					"SaveConditionChanges" => array( "type" => "POST", "url" => "/Api/RulesEngine/SaveConditionChanges", "noparams" => 1),
					"SetConditionEnabled" => array( "type" => "POST", "url" => "/Api/RulesEngine/SetConditionEnabled", "noparams" => 1),
					"SetDraftLive" => array( "type" => "POST", "url" => "/Api/RulesEngine/SetDraftLive", "noparams" => 1),
					"SetRuleEnabled" => array( "type" => "POST", "url" => "/Api/RulesEngine/SetRuleEnabled", "noparams" => 1),
					"SetRuleName" => array( "type" => "POST", "url" => "/Api/RulesEngine/SetRuleName", "noparams" => 1),
					"SwapConditions" => array( "type" => "POST", "url" => "/Api/RulesEngine/SwapConditions", "noparams" => 1),
					"SwapRules" => array( "type" => "POST", "url" => "/Api/RulesEngine/SwapRules", "noparams" => 1),
					"TestEvaluateRule" => array( "type" => "POST", "url" => "/Api/RulesEngine/TestEvaluateRule", "noparams" => 1),
					"UpdateAction" => array( "type" => "POST", "url" => "/Api/RulesEngine/UpdateAction", "noparams" => 1),
					"DeleteCurrencyConversionRates" => array( "type" => "POST", "url" => "/Api/Settings/DeleteCurrencyConversionRates", "noparams" => 1),
					"GetAvailableTimeZones" => array( "type" => "POST", "url" => "/Api/Settings/GetAvailableTimeZones", "noparams" => 1),
					"GetCurrencyConversionRates" => array( "type" => "POST", "url" => "/Api/Settings/GetCurrencyConversionRates", "noparams" => 1),
					"GetLatestCurrencyRate" => array( "type" => "POST", "url" => "/Api/Settings/GetLatestCurrencyRate", "noparams" => 1),
					"GetMeasures" => array( "type" => "POST", "url" => "/Api/Settings/GetMeasures", "noparams" => 1),
					"InsertCurrencyConversionRates" => array( "type" => "POST", "url" => "/Api/Settings/InsertCurrencyConversionRates", "noparams" => 1),
					"UpdateCurrencyConversionRates" => array( "type" => "POST", "url" => "/Api/Settings/UpdateCurrencyConversionRates", "noparams" => 1),
					"AddRollingStockTake" => array( "type" => "POST", "url" => "/Api/Stock/AddRollingStockTake", "noparams" => 1),
					"AddVariationItems" => array( "type" => "POST", "url" => "/Api/Stock/AddVariationItems", "noparams" => 1),
					"BatchStockLevelDelta" => array( "type" => "POST", "url" => "/Api/Stock/BatchStockLevelDelta", "noparams" => 1),
					"BookInStockBatch" => array( "type" => "POST", "url" => "/Api/Stock/BookInStockBatch", "noparams" => 1),
					"BookInStockItem" => array( "type" => "POST", "url" => "/Api/Stock/BookInStockItem", "noparams" => 1),
					"CheckVariationParentSKUExists" => array( "type" => "POST", "url" => "/Api/Stock/CheckVariationParentSKUExists", "noparams" => 1),
					"CreateStockBatches" => array( "type" => "POST", "url" => "/Api/Stock/CreateStockBatches", "noparams" => 1),
					"CreateVariationGroup" => array( "type" => "POST", "url" => "/Api/Stock/CreateVariationGroup", "noparams" => 1),
					"CreateWarehouseMove" => array( "type" => "POST", "url" => "/Api/Stock/CreateWarehouseMove", "noparams" => 1),
					"DeleteVariationGroup" => array( "type" => "POST", "url" => "/Api/Stock/DeleteVariationGroup", "noparams" => 1),
					"DeleteVariationGroups" => array( "type" => "POST", "url" => "/Api/Stock/DeleteVariationGroups", "noparams" => 1),
					"DeleteVariationItem" => array( "type" => "POST", "url" => "/Api/Stock/DeleteVariationItem", "noparams" => 1),
					"DeleteVariationItems" => array( "type" => "POST", "url" => "/Api/Stock/DeleteVariationItems", "noparams" => 1),
					"GetItemChangesHistory" => array( "type" => "POST", "url" => "/Api/Stock/GetItemChangesHistory", "noparams" => 1),
					"GetItemChangesHistoryCSV" => array( "type" => "POST", "url" => "/Api/Stock/GetItemChangesHistoryCSV", "noparams" => 1),
					"GetSoldStat" => array( "type" => "POST", "url" => "/Api/Stock/GetSoldStat", "noparams" => 1),
					"GetStockConsumption" => array( "type" => "POST", "url" => "/Api/Stock/GetStockConsumption", "noparams" => 1),
					"GetStockDuePO" => array( "type" => "POST", "url" => "/Api/Stock/GetStockDuePO", "noparams" => 1),
					"GetStockItemReturnStat" => array( "type" => "POST", "url" => "/Api/Stock/GetStockItemReturnStat", "noparams" => 1),
					"GetStockItems" => array( "type" => "POST", "url" => "/Api/Stock/GetStockItems", "noparams" => 1),
					"GetStockItemsByIds" => array( "type" => "POST", "url" => "/Api/Stock/GetStockItemsByIds", "noparams" => 1),
					"GetStockItemsByKey" => array( "type" => "POST", "url" => "/Api/Stock/GetStockItemsByKey", "noparams" => 1),
					"GetStockItemScrapStat" => array( "type" => "POST", "url" => "/Api/Stock/GetStockItemScrapStat", "noparams" => 1),
					"GetStockItemsFull" => array( "type" => "POST", "url" => "/Api/Stock/GetStockItemsFull", "noparams" => 1),
					"GetStockItemsFullByIds" => array( "type" => "POST", "url" => "/Api/Stock/GetStockItemsFullByIds", "noparams" => 1),
					"GetStockItemTypeInfo" => array( "type" => "POST", "url" => "/Api/Stock/GetStockItemTypeInfo", "noparams" => 1),
					"GetStockLevel" => array( "type" => "POST", "url" => "/Api/Stock/GetStockLevel", "noparams" => 1),
					"GetStockLevel_Batch" => array( "type" => "POST", "url" => "/Api/Stock/GetStockLevel_Batch", "noparams" => 1),
					"GetStockLevelByLocation" => array( "type" => "POST", "url" => "/Api/Stock/GetStockLevelByLocation", "noparams" => 1),
					"GetStockSold" => array( "type" => "POST", "url" => "/Api/Stock/GetStockSold", "noparams" => 1),
					"GetVariationGroupByName" => array( "type" => "POST", "url" => "/Api/Stock/GetVariationGroupByName", "noparams" => 1),
					"GetVariationGroupByParentId" => array( "type" => "POST", "url" => "/Api/Stock/GetVariationGroupByParentId", "noparams" => 1),
					"GetVariationGroupSearchTypes" => array( "type" => "POST", "url" => "/Api/Stock/GetVariationGroupSearchTypes", "noparams" => 1),
					"GetVariationItems" => array( "type" => "POST", "url" => "/Api/Stock/GetVariationItems", "noparams" => 1),
					"RenameVariationGroup" => array( "type" => "POST", "url" => "/Api/Stock/RenameVariationGroup", "noparams" => 1),
					"SearchVariationGroups" => array( "type" => "POST", "url" => "/Api/Stock/SearchVariationGroups", "noparams" => 1),
					"SetStockLevel" => array( "type" => "POST", "url" => "/Api/Stock/SetStockLevel", "noparams" => 1),
					"SKUExists" => array( "type" => "POST", "url" => "/Api/Stock/SKUExists", "noparams" => 1),
					"Update_StockItemPartial" => array( "type" => "POST", "url" => "/Api/Stock/Update_StockItemPartial", "noparams" => 1),
					"UpdateSkuGroupIdentifier" => array( "type" => "POST", "url" => "/Api/Stock/UpdateSkuGroupIdentifier", "noparams" => 1),
					"UpdateStockLevelsBulk" => array( "type" => "POST", "url" => "/Api/Stock/UpdateStockLevelsBulk", "noparams" => 1),
					"UpdateStockLevelsBySKU" => array( "type" => "POST", "url" => "/Api/Stock/UpdateStockLevelsBySKU", "noparams" => 1),
					"UpdateStockMinimumLevel" => array( "type" => "POST", "url" => "/Api/Stock/UpdateStockMinimumLevel", "noparams" => 1),
					"AddItemsToTransfer" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/AddItemsToTransfer", "noparams" => 1),
					"AddItemToTransfer" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/AddItemToTransfer", "noparams" => 1),
					"AddTransferBinNote" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/AddTransferBinNote", "noparams" => 1),
					"AddTransferItemNote" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/AddTransferItemNote", "noparams" => 1),
					"AddTransferNote" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/AddTransferNote", "noparams" => 1),
					"AddTransferProperty" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/AddTransferProperty", "noparams" => 1),
					"AllocateItemToBin" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/AllocateItemToBin", "noparams" => 1),
					"ChangeBinDetails" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/ChangeBinDetails", "noparams" => 1),
					"ChangeTransferFromLocation" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/ChangeTransferFromLocation", "noparams" => 1),
					"ChangeTransferItemReceivedQuantity" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/ChangeTransferItemReceivedQuantity", "noparams" => 1),
					"ChangeTransferItemRequestQuantity" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/ChangeTransferItemRequestQuantity", "noparams" => 1),
					"ChangeTransferItemSentQuantity" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/ChangeTransferItemSentQuantity", "noparams" => 1),
					"ChangeTransferLocations" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/ChangeTransferLocations", "noparams" => 1),
					"ChangeTransferProperty" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/ChangeTransferProperty", "noparams" => 1),
					"ChangeTransferStatus" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/ChangeTransferStatus", "noparams" => 1),
					"ChangeTransferToLocation" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/ChangeTransferToLocation", "noparams" => 1),
					"CheckForDraftTransfer" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/CheckForDraftTransfer", "noparams" => 1),
					"CreateNewBin" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/CreateNewBin", "noparams" => 1),
					"CreateTransferFromDescrepancies" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/CreateTransferFromDescrepancies", "noparams" => 1),
					"CreateTransferRequestWithReturn" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/CreateTransferRequestWithReturn", "noparams" => 1),
					"DeleteEmptyDraftTransfer" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/DeleteEmptyDraftTransfer", "noparams" => 1),
					"DeleteTransfer" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/DeleteTransfer", "noparams" => 1),
					"DeleteTransferProperty" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/DeleteTransferProperty", "noparams" => 1),
					"GetActiveTransfersAllLocations" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/GetActiveTransfersAllLocations", "noparams" => 1),
					"GetActiveTransfersForLocation" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/GetActiveTransfersForLocation", "noparams" => 1),
					"GetArchivedTransfers" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/GetArchivedTransfers", "noparams" => 1),
					"GetArchivedTransfersBetweenArchivedDates" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/GetArchivedTransfersBetweenArchivedDates", "noparams" => 1),
					"GetArchivedTransfersBetweenDates" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/GetArchivedTransfersBetweenDates", "noparams" => 1),
					"GetArchivedTransfersFiltered" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/GetArchivedTransfersFiltered", "noparams" => 1),
					"GetDiscrepancyItems" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/GetDiscrepancyItems", "noparams" => 1),
					"GetListTransfers" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/GetListTransfers", "noparams" => 1),
					"GetModifiedBasic" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/GetModifiedBasic", "noparams" => 1),
					"GetServerTime" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/GetServerTime", "noparams" => 1),
					"GetStockAvailability" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/GetStockAvailability", "noparams" => 1),
					"GetTransferAudit" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/GetTransferAudit", "noparams" => 1),
					"GetTransferBinNotes" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/GetTransferBinNotes", "noparams" => 1),
					"GetTransferItemNotes" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/GetTransferItemNotes", "noparams" => 1),
					"GetTransferItems" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/GetTransferItems", "noparams" => 1),
					"GetTransferNotes" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/GetTransferNotes", "noparams" => 1),
					"GetTransferProperties" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/GetTransferProperties", "noparams" => 1),
					"GetTransferWithItems" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/GetTransferWithItems", "noparams" => 1),
					"GetTransferWithNotes" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/GetTransferWithNotes", "noparams" => 1),
					"IsDraftTransferChanged" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/IsDraftTransferChanged", "noparams" => 1),
					"PrintTransfer" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/PrintTransfer", "noparams" => 1),
					"RemoveAllEmptyBins" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/RemoveAllEmptyBins", "noparams" => 1),
					"RemoveItemFromTransfer" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/RemoveItemFromTransfer", "noparams" => 1),
					"SearchTransfersAllLocations" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/SearchTransfersAllLocations", "noparams" => 1),
					"SearchTransfersByLocation" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/SearchTransfersByLocation", "noparams" => 1),
					"SetReferenceNumber" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/SetReferenceNumber", "noparams" => 1),
					"AddWarehouseZone" => array( "type" => "POST", "url" => "/Api/Wms/AddWarehouseZone", "noparams" => 1),
					"AddWarehouseZoneType" => array( "type" => "POST", "url" => "/Api/Wms/AddWarehouseZoneType", "noparams" => 1),
					"DeleteWarehouseZone" => array( "type" => "POST", "url" => "/Api/Wms/DeleteWarehouseZone", "noparams" => 1),
					"DeleteWarehouseZoneType" => array( "type" => "POST", "url" => "/Api/Wms/DeleteWarehouseZoneType", "noparams" => 1),
					"GetBinrackZonesByBinrackIdOrName" => array( "type" => "POST", "url" => "/Api/Wms/GetBinrackZonesByBinrackIdOrName", "noparams" => 1),
					"GetBinrackZonesByZoneIdOrName" => array( "type" => "POST", "url" => "/Api/Wms/GetBinrackZonesByZoneIdOrName", "noparams" => 1),
					"GetWarehouseZonesByLocation" => array( "type" => "POST", "url" => "/Api/Wms/GetWarehouseZonesByLocation", "noparams" => 1),
					"GetWarehouseZoneTypes" => array( "type" => "POST", "url" => "/Api/Wms/GetWarehouseZoneTypes", "noparams" => 1),
					"UpdateWarehouseBinrackBinrackToZone" => array( "type" => "POST", "url" => "/Api/Wms/UpdateWarehouseBinrackBinrackToZone", "noparams" => 1),
					"UpdateWarehouseZone" => array( "type" => "POST", "url" => "/Api/Wms/UpdateWarehouseZone", "noparams" => 1),
					"UpdateWarehouseZoneType" => array( "type" => "POST", "url" => "/Api/Wms/UpdateWarehouseZoneType", "noparams" => 1)			
				);
				
			if ( array_key_exists( $apiname, $api_calls ) ) {
				return $api_calls[ $apiname ];
			} else {
				return false;
			}
		}

		function call_linnworks_api( $apicall, $params = NULL ) {
			
			$check_api = $this->api_call_names( $apicall );
			
			if ( $check_api != false ) {
				// API Call Found

				if ( !empty($params) AND !is_null($params) ) {
					$pc = count( $params ); // count the number of parameters passed
				} else {
					$pc = 0; // if empty the set Parameter count to 0
					$params = NULL; // If empty enforce NULL value
				}

				if ( $check_api["noparams"] == $pc ) {
					if ( $pc > 0 ) {
						foreach ( $params  AS $key => &$value ) {
							$value = json_encode( $value );
						}

						$params = http_build_query( $params );
					}
					
					return $this->api_call($check_api["type"],$check_api["url"],$params);

				}
			}
			return false;
		}

		/*
			Parameter formatting notes

			[] Are index values in json
				EG array("test") or array("0"=>"test")

			{} are associative arrays
				EG array("Test"=>"Data")

			array("Test"=> array("data"))

			Once encoded with json_encode the output will be
				{"Test":["data"]}
		*/
		
		function check_credentials() {
			/*
				Check if the credentials are set and return true if they are and false if not
			*/
			$check = array($this->linn_app_id,$this->linn_app_secret,$this->linn_app_token);

			if ( in_array(null,$check) ) {
				Return false;
			} else {
				Return true;
			}
		}
		function set_credentials($id,$secret,$token) {
			/*
				Setup API Creditials ready for use
			*/
			
			$this->linn_app_id = $id;
			$this->linn_app_secret = $secret;
			$this->linn_app_token = $token;
		}
		
		// Auth
		
		function AuthorizeByApplication() {
			/*
				Create connection to Linnworks API server and get autfhorisation token

				request={
					"ApplicationId": "298de537-0fd8-4493-9947-6e441412a7ba",
					"ApplicationSecret": "d58b3abc-efbf-4e8a-ac94-fc0e5d4f6e7c",
					"Token": "9b442692-ee22-4b39-bdc6-e1e112641475"
				}
			*/
			
			$url = "https://api.linnworks.net//api/Auth/AuthorizeByApplication";
			// Set Required Parameters for authentication then URL Encode
			
			$params = "request=" . json_encode(
										array(	
											"applicationId" => $this->linn_app_id, 
											"applicationSecret" => $this->linn_app_secret, 
											"token" => $this->linn_app_token
										));
			
			$c_data = self::api_call( "POST", $url, $params); //

			if ( !isset($c_data["Message"]) ) {
				$this->linn_auth_data = $c_data; // Assign curl data
				$this->linn_auth_token = $c_data["Token"]; // Store API Token
				$this->linn_auth_server = $c_data["Server"]; // Store API Server
				
				return true;
			} else {
				error_log("Linnworks API:linn_auth.  No Connection", 0);
				
				return false;
			}
		}
		function GetApplicationProfileBySecretKey() {
			/*
				applicationId=9280b1b1-c8df-4566-8168-63fdf197e215&
				applicationSecret=892ec09f-a771-4fbc-92b3-a51fd8c83160&
				userId=dc38dae1-374b-44ec-a004-90f4f739e1bb
			*/
			
			$url = "/api/Auth/GetApplicationProfileBySecretKey";
		}
		function GetServerUTCTime() {
			/*
				No parameters required.
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}

	}
?>