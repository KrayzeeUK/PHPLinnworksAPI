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
			
			if ( $check_api == false ) {
				// API Call not found
				echo "Not Found";
				return false;
			} else {
				// API Call Found

				print_r( $params );

				if ( !empty($params) ) {
					$pc = count( $params ); // count the number of parameters passed
					echo "Count: " . $pc . "<hr>";
				} else {
					$pc = 0;
				}
				
				if ( $check_api["noparams"] > 0 ) {
					if ( !empty($params) ) {
						
						if ( $pc >= $check_api["noparams"] ) {
							foreach ( $params  AS $key => &$value ) {
								echo $key . "<hr>";
								$value = json_encode( $value );
							}
						} else {
							// Does not meet the minimum amount of parameters
							echo "No enough parameters<hr>";
						}

						print_r( $params );
						$params = http_build_query( $params );
						print_r( $params );
						echo "<hr>";
						return $this->api_call($check_api["type"],$check_api["url"],$params);
					}
				} else {
					
				}
			}
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

		// Inventory
		
		function AddImageToInventoryItem( $item ) {
			/*
				request={
					  "ItemNumber": "sample string 1",
					  "StockItemId": "5d46757d-ff43-4497-aad1-b05513c907bb",
					  "IsMain": true,
					  "ImageUrl": "sample string 4"
					}
			*/
			
			$params = "request=" . json_encode( $item );
			$c_data = self::api_call( "POST", "/api/Inventory/AddImageToInventoryItem", $params); //

			return $c_data;
		}
		function AddInventoryItem( $item ) {
			/*
				inventoryItem={
								  "ItemDescription": "sample string 1",
								  "Quantity": 2,
								  "InOrder": 3,
								  "Due": 4,
								  "MinimumLevel": 5,
								  "Available": -1,
								  "IsCompositeParent": true,
								  "ItemNumber": "sample string 6",
								  "ItemTitle": "sample string 7",
								  "BarcodeNumber": "sample string 8",
								  "MetaData": "sample string 9",
								  "isBatchedStockType": false,
								  "PurchasePrice": 10.1,
								  "RetailPrice": 1.1,
								  "TaxRate": 11.1,
								  "PostalServiceId": "9eafd9a1-731a-4bf8-8410-79450dbc0793",
								  "PostalServiceName": "sample string 13",
								  "CategoryId": "57959a1e-6fc0-4365-9d79-1175ca2bfa38",
								  "CategoryName": "sample string 15",
								  "PackageGroupId": "2adf11db-c30a-44e5-9f0f-847799a1731d",
								  "PackageGroupName": "sample string 17",
								  "Height": 18.1,
								  "Width": 19.1,
								  "Depth": 20.1,
								  "Weight": 21.1,
								  "CreationDate": "2021-05-05T15:35:21.7777371+01:00",
								  "InventoryTrackingType": 22,
								  "BatchNumberScanRequired": true,
								  "SerialNumberScanRequired": true,
								  "StockItemId": "c3218af5-7397-49d6-92d2-75210a19c495",
								  "StockItemIntId": 26
								}
			*/
			
			$params = "inventoryItem=" . json_encode($item);
			$c_data = self::api_call( "POST", "/api/Inventory/AddInventoryItem", $params); //

			return $c_data;
		}
		function AddItemLocations( $ilocation ) {
			/*
				itemLocations=[
								  {
									"StockLocationId": "4ad117d4-2faa-4540-98bb-0103a9919a96",
									"LocationName": "sample string 2",
									"BinRack": "sample string 3",
									"StockItemId": "2cf2541a-8ffb-45d9-a792-357a24cae952",
									"StockItemIntId": 5
								  }
								]
			*/
			
			$params = "itemLocations=" . json_encode($ilocation);
			$c_data = self::api_call( "POST", "/api/Inventory/AddItemLocations",$params); //

			return $c_data;
		}
		function AddProductIdentifiers( $pident ) {
			/*
				request={
						  "ProductIdentifiers": [
							{
							  "PkId": 1,
							  "StockItemId": "217e8b4e-5fc0-4f03-92cf-26818eefda95",
							  "Type": 0,
							  "Site": "sample string 3",
							  "Value": "sample string 4",
							  "ModifiedDate": "2021-05-05T15:35:21.6351179+01:00",
							  "ModifiedUserName": "sample string 6"
							}
						  ]
						}
			*/
			
			$params = "request=" . $json_encode($pident);
			$c_data = self::api_call( "POST", "/api/Inventory/AddProductIdentifiers", $params); //

			return $c_data;
		}
		function AddScrapCategories( $category ) {
			/*
				request={
						  "CategoryNames": [
							"sample string 1"
						  ]
						}
			*/
			
			$params = "request=" . json_encode($category);
			$c_data = self::api_call( "POST", "/api/Inventory/AddScrapCategories", $params); //

			return $c_data;
		}
		function AddScrapItem( $item ) {
			/*
				request={
					  "ScrapItem": {
						"Id": "a061deee-5765-42fc-a465-7cf5bb0f1735",
						"StockItemId": "594ddde7-6424-416c-b2f4-88f0b76f6c58",
						"ItemNumber": "sample string 3",
						"ItemTitle": "sample string 4",
						"Quantity": 5,
						"CategoryName": "sample string 6",
						"ScrapReason": "sample string 7",
						"TotalCost": 8.1,
						"UserName": "sample string 9",
						"CreatedDate": "2021-05-05T15:35:21.6191606+01:00",
						"StockLocationId": "472564b2-92f7-4b09-b078-fc9376e3abd2"
					  },
					  "LocationId": "dc91ebd5-7106-4d53-86c8-9ef47a479f4b",
					  "IgnoreConsumption": true
					}
			*/
			
			$params = "request=" . json_encode($item);
			$c_data = self::api_call( "POST", "/api/Inventory/AddScrapItem", $params); // Call API

			return $c_data;
		}
		function AddSupplier( $supplier ) {
			/*
				supplier={
					  "pkSupplierID": "6ffca144-f111-4ff0-b0fe-d6d0fbea72dd",
					  "SupplierName": "sample string 2",
					  "ContactName": "sample string 3",
					  "Address": "sample string 4",
					  "AlternativeAddress": "sample string 5",
					  "City": "sample string 6",
					  "Region": "sample string 7",
					  "Country": "sample string 8",
					  "PostCode": "sample string 9",
					  "TelephoneNumber": "sample string 10",
					  "SecondaryTelNumber": "sample string 11",
					  "FaxNumber": "sample string 12",
					  "Email": "sample string 13",
					  "WebPage": "sample string 14",
					  "Currency": "sample string 15"
					}
			*/
			
			$params = "supplier=" . json_encode($supplier);
			$c_data = self::api_call( "POST", "/api/Inventory/AddSupplier", $params); // Call API

			return $c_data;
		}
		function AdjustEbayTemplatesDispatchLMS( $inventoryItemIds, $subSource, $siteId, $adjustmentOptions ) {
			/*
			inventoryItemIds=[
						  "a6007dd8-0f44-47ec-8073-0f99a5b0faf1"
						]
			&subSource=sample string 1
			&siteId=sample string 1
			&adjustmentOptions={
				  "Title": true,
				  "Price": true,
				  "Description": true,
				  "AddExtendedProperties": true,
				  "ReviseExtendedProperties": true,
				  "UpdateImages": true,
				  "VariationAttributes": true,
				  "ReloadAllImages": true
				}
			*/
			
			$params = http_build_query( array(
							"inventoryItemIds" => json_encode($inventoryItemIds),
							"subSource" => $subSource,
							"siteId" => $siteId,
							"adjustmentOptions" => json_encode($adjustmentOptions)
						));
						
			$c_data = self::api_call( "POST", "/api/Inventory/AdjustEbayTemplatesDispatchLMS", $params); // Call API

			return $c_data;
		}
		function AdjustEbayTemplatesInstantLMS( $inventoryItemIds, $subSource, $siteId, $adjustmentOptions ) {
			/*
				inventoryItemIds=[
							  "99a91939-f967-4fd9-97ac-53f77ae56579"
							]
				&subSource=sample string 1
				&siteId=sample string 1
				&adjustmentOptions={
					  "Title": true,
					  "Price": true,
					  "Description": true,
					  "AddExtendedProperties": true,
					  "ReviseExtendedProperties": true,
					  "UpdateImages": true,
					  "VariationAttributes": true,
					  "ReloadAllImages": true
					}
			*/
			
			$params = http_build_query( array(
							"inventoryItemIds" => json_encode($inventoryItemIds),
							"subSource" => $subSource,
							"siteId" => $siteId,
							"adjustmentOptions" => json_encode($adjustmentOptions)
						));
						
			$c_data = self::api_call( "POST", "/api/Inventory/AdjustEbayTemplatesInstantLMS", $params); // Call API

			return $c_data;
		}
		function AdjustTemplatesInstant( $inventoryItemIds, $source, $subSource, $adjustmentOptions ) {
			/*
				inventoryItemIds=[
							  "ac6ec20a-405d-4b97-9d15-5e6bcfffee78"
							]
				&source=sample string 1
				&subSource=sample string 1
				&adjustmentOptions={
					  "Title": true,
					  "Price": true,
					  "Description": true,
					  "AddExtendedProperties": true,
					  "ReviseExtendedProperties": true,
					  "UpdateImages": true,
					  "VariationAttributes": true,
					  "ReloadAllImages": true
					}
			*/
			
			$params = http_build_query( array(
							"inventoryItemIds" => json_encode($inventoryItemIds),
							"source" => $source,
							"subSource" => $subSource,
							"adjustmentOptions" => json_encode($adjustmentOptions)
						));
						
			$c_data = self::api_call( "POST", "/api/Inventory/AdjustTemplatesInstant", $params); // Call API

			return $c_data;
		}
		function ArchiveInventoryItems( $InventoryItemIds, $SelectedRegions, $Token ) {
			/*
				parameters={
						  "InventoryItemIds": [
							"3fa5070f-cb0a-4d62-a99a-aad561b51b02"
						  ],
						  "SelectedRegions": [
							{
							  "Item1": 1,
							  "Item2": 2
							}
						  ],
						  "Token": "0fc79c1e-4782-4a23-b24a-5c101a1d9a1e"
						}
			*/
			
			$params = "parameters=" . json_encode( array(
											"InventoryItemIds" => $InventoryItemIds,
											"SelectedRegions" => $SelectedRegions,
											"Token" => $Token
										) );
			
			$c_data = self::api_call( "POST", "/api/Inventory/ArchiveInventoryItems", $params); // Call API

			return $c_data;
		}
		function BatchGetInventoryItemChannelSKUs( $inventoryItemIds ) {
			/*
				inventoryItemIds=[
								  "2023958b-4bb0-4179-9081-295487646fc9"
								]
			*/
			
			$params = "inventoryItemIds=" . json_encode($inventoryItemIds);
			$c_data = self::api_call( "POST", "/api/Inventory/BatchGetInventoryItemChannelSKUs", $params); // Call API

			return $c_data;
		}
		function BulkScrapBatchedItems( $LocationId, $ScrapItems ) {
			/*
				request={
					  "LocationId": "2af4b330-6159-44b0-8665-aa29e2c43d7f",
					  "ScrapItems": [
						{
						  "pkScrapId": "64acdb8c-1ddc-4222-b80c-d673070c1635",
						  "BatchInventoryId": 2,
						  "fkStockItemId": "7ed56e8a-33d6-4536-9389-32384b7f6c65",
						  "Quantity": 4,
						  "ScrapCategory": "sample string 5",
						  "ScrapReason": "sample string 6"
						}
					  ]
					}
			*/
			
			$params = "request=" . json_encode(array(
												"LocationId" => $LocationId,
												"ScrapItems" => $ScrapItems
												));
			$c_data = self::api_call( "POST", "/api/Inventory/BulkScrapBatchedItems", $params); // Call API

			return $c_data;
		}
		function CreateBatches( $batch ) {
			/*
			batches=[
					  {
						"BatchId": 1,
						"SKU": "sample string 2",
						"InventoryTrackingType": 3,
						"StockItemId": "0230c8a8-4fc0-414d-a4b9-beb63e08c232",
						"BatchNumber": "sample string 5",
						"ExpiresOn": "2021-05-05T15:35:21.5982166+01:00",
						"SellBy": "2021-05-05T15:35:21.5982166+01:00",
						"Inventory": [
						  {
							"BatchInventoryId": 1,
							"BatchId": 2,
							"StockLocationId": "6a7b2e8a-91c5-455c-890c-9bcc579b9a01",
							"BinRack": "sample string 4",
							"PrioritySequence": 5,
							"Quantity": 6,
							"StockValue": 7.0,
							"StartQuantity": 8,
							"PickedQuantity": 9,
							"BatchStatus": "Available",
							"IsDeleted": true,
							"WarehouseBinrackStandardType": 1,
							"WarehouseBinrackTypeName": "sample string 12",
							"InTransfer": 1,
							"BinRackId": 1,
							"WarehouseBinrackTypeId": 1
						  }
						],
						"IsDeleted": true
					  }
					]
			*/
			
			$params = "batches=" . json_encode($batch);
			$c_data = self::api_call( "POST", "/api/Inventory/CreateBatches", $params); // Call API

			return $c_data;
		}
		function CreateCategory( $catName ) {
			/*
				categoryName=sample string 1
			*/
			
			$params = "categoryName=" . $catName;
			$c_data = self::api_call( "POST", "/api/Inventory/CreateCategory", $params); // Call API

			return $c_data;
		}
		function CreateCountries( $countries ) {
			/*
			countries=[
						  {
							"CountryId": "10f312d9-3438-4720-96c8-931bb5828487",
							"CountryName": "sample string 2",
							"CountryCode": "sample string 3",
							"Continent": "sample string 4",
							"Currency": "sample string 5",
							"CustomsRequired": true,
							"TaxRate": 1.1,
							"AddressFormat": "sample string 7",
							"Regions": [
							  {
								"pkRegionRowId": 1,
								"RegionCode": "sample string 2",
								"RegionName": "sample string 3",
								"TaxRate": 1.1,
								"fkCountryId": "1a3d5910-b30f-40f5-9d4a-bf2a3f9bcbbe",
								"ReplaceWith": 0,
								"IsHomeRegion": true,
								"TagsCount": 6
							  }
							],
							"RegionsCount": 8
						  }
						]
			*/
			
			$params = "countries=" . json_encode($countries);
			$c_data = self::api_call( "POST", "/api/Inventory/CreateCountries", $params); // Call API

			return $c_data;
		}
		function CreateCountryRegions( $regions ) {
			/*
				request={
						  "regions": [
							{
							  "pkRegionRowId": 1,
							  "RegionCode": "sample string 2",
							  "RegionName": "sample string 3",
							  "TaxRate": 1.1,
							  "fkCountryId": "51b94c32-d947-4681-b308-98520b482d4c",
							  "ReplaceWith": 0,
							  "IsHomeRegion": true,
							  "TagsCount": 6
							}
						  ]
						}
			*/
			
			$params = "request=" . json_encode($regions);
			$c_data = self::api_call( "POST", "/api/Inventory/CreateCountryRegions", $params); // Call API

			return $c_data;
		}
		function CreateInventoryItemChannelSKUs( $inventoryItemChannelSKUs ) {
			/*
				inventoryItemChannelSKUs=[
										  {
											"ChannelSKURowId": "b26fb825-8159-4230-b656-b7ded6ce3d5a",
											"SKU": "sample string 2",
											"Source": "sample string 3",
											"SubSource": "sample string 4",
											"UpdateStatus": "sample string 5",
											"ChannelReferenceId": "sample string 6",
											"LastUpdate": "2021-05-05T15:35:21.8136736+01:00",
											"MaxListedQuantity": 8,
											"EndWhenStock": 9,
											"SubmittedQuantity": 10,
											"ListedQuantity": 11,
											"StockPercentage": 12.1,
											"IgnoreSync": true,
											"IgnoreSyncMultiLocation": true,
											"IsMultiLocation": true,
											"StockItemId": "5f32fbf1-746b-4747-b570-5549ae9bcab0",
											"StockItemIntId": 16
										  }
										]
			*/
			
			$params = "inventoryItemChannelSKUs=" . json_encode($inventoryItemChannelSKUs);
			$c_data = self::api_call( "POST", "/api/Inventory/CreateInventoryItemChannelSKUs", $params); // Call API

			return $c_data;
		}
		function CreateInventoryItemCompositions( $params ) {
			/*
				inventoryItemCompositions=[
										  {
											"LinkedStockItemId": "6f869569-e5cd-4f2a-855e-57e717b59b71",
											"ItemTitle": "sample string 2",
											"SKU": "sample string 3",
											"Quantity": 4,
											"PurchasePrice": 5.1,
											"InventoryTrackingType": 64,
											"DimHeight": 7.1,
											"DimWidth": 8.1,
											"DimDepth": 9.1,
											"Weight": 10.1,
											"PackageGroupId": "fb7a1637-18cb-410a-a996-af3033560a84",
											"StockItemId": "cf600281-0ba6-4d77-a6f6-34bb318614e2",
											"StockItemIntId": 2
										  }
										]
			*/
			
			$params = "inventoryItemCompositions=" . json_encode($params);
			$c_data = self::api_call( "POST", "/api/Inventory/CreateInventoryItemCompositions", $params); // Call API

			return $c_data;
		}
		function CreateInventoryItemDescriptions( $params ) {
			/*
				inventoryItemDescriptions=[
										  {
											"pkRowId": "a3b50266-3618-4c8e-8723-ae9949b1c835",
											"Source": "sample string 2",
											"SubSource": "sample string 3",
											"Description": "sample string 4",
											"StockItemId": "4e9a88f7-6289-4e53-b70e-04094c0b1538",
											"StockItemIntId": 2
										  }
										]
			*/
			
			$params = "inventoryItemDescriptions=" . json_encode($params);
			$c_data = self::api_call( "POST", "/api/Inventory/CreateInventoryItemDescriptions", $params); // Call API

			return $c_data;
		}
		function CreateInventoryItemExtendedProperties( $params ) {
			/*
				inventoryItemExtendedProperties=[
												  {
													"fkStockItemId": "d4070aca-f93a-49a5-a339-cfff0cd8c25d",
													"SKU": "sample string 1",
													"ProperyName": "sample string 2",
													"PropertyValue": "sample string 3",
													"PropertyType": "sample string 4"
												  }
												]
			*/
			
			$params = "inventoryItemExtendedProperties=" . json_encode($params);
			$c_data = self::api_call( "POST", "/api/Inventory/CreateInventoryItemExtendedProperties", $params); // Call API

			return $c_data;
		}
		function CreateInventoryItemPrices( $params ) {
			/*
				inventoryItemPrices=[
									  {
										"Rules": [
										  {
											"pkRowId": 1,
											"fkStockPricingId": "cecea4db-e251-4c49-9f22-483471a488f2",
											"Type": "sample string 2",
											"LowerBound": 3,
											"Value": 4.1
										  }
										],
										"pkRowId": "c41fff3e-8d59-4720-963d-228dd54dce0d",
										"Source": "sample string 2",
										"SubSource": "sample string 3",
										"Price": 4.1,
										"Tag": "sample string 5",
										"UpdateStatus": 0,
										"StockItemId": "03d05840-66c2-475f-a04a-4f05929f155c",
										"StockItemIntId": 2
									  }
									]
			*/
			
			$params = "inventoryItemPrices=" . json_encode($params);
			$c_data = self::api_call( "POST", "/api/Inventory/CreateInventoryItemPrices", $params); // Call API

			return $c_data;
		}
		function CreateInventoryItemPricingRules( $params ) {
			/*
				rules=[
						  {
							"pkRowId": 1,
							"fkStockPricingId": "5d553051-7ae5-43d2-a8b3-a21a43384522",
							"Type": "sample string 2",
							"LowerBound": 3,
							"Value": 4.1
						  }
						]
			*/
			
			$params = "rules=" . json_encode($params);
			$c_data = self::api_call( "POST", "/api/Inventory/CreateInventoryItemPricingRules", $params); // Call API

			return $c_data;
		}
		function CreateInventoryItemTitles( $params ) {
			/*
			inventoryItemTitles=[
								  {
									"pkRowId": "03c50e88-1f47-414c-8ef4-ff1c62012e39",
									"Source": "sample string 2",
									"SubSource": "sample string 3",
									"Title": "sample string 4",
									"StockItemId": "6deb325e-363a-4375-a0ba-6f0bd085211a",
									"StockItemIntId": 2
								  }
								]
			*/
			
			$params = "inventoryItemTitles=" . json_encode($params);
			$c_data = self::api_call( "POST", "/api/Inventory/CreateInventoryItemTitles", $params); // Call API

			return $c_data;
		}
		function CreateStockSupplierStat( $params ) {
			/*
				itemSuppliers=[
								  {
									"IsDefault": true,
									"Supplier": "sample string 2",
									"SupplierID": "47d942c9-8756-45d0-959f-d936fb9cf065",
									"Code": "sample string 4",
									"SupplierBarcode": "sample string 5",
									"LeadTime": 6,
									"PurchasePrice": 7.1,
									"MinPrice": 8.1,
									"MaxPrice": 9.1,
									"AveragePrice": 10.1,
									"AverageLeadTime": 11.1,
									"SupplierMinOrderQty": 12,
									"SupplierPackSize": 13,
									"SupplierCurrency": "sample string 14",
									"StockItemId": "7970aaff-c2a9-4425-8553-7a15d0d3fbf7",
									"StockItemIntId": 16
								  }
								]
			*/
			
			$params = "itemSuppliers=" . json_encode($params);
			$c_data = self::api_call( "POST", "/api/Inventory/CreateStockSupplierStat", $params); // Call API

			return $c_data;
		}
		function CreateUserSpecificView( $params ) {
			/*
				view={
						  "Id": "ee18d436-950f-4297-b2c8-2f91c04599f9",
						  "Name": "sample string 2",
						  "Mode": 0,
						  "Source": "sample string 3",
						  "SubSource": "sample string 4",
						  "CountryCode": "sample string 5",
						  "CountryName": "sample string 6",
						  "Listing": 0,
						  "ShowOnlyChanged": true,
						  "IncludeProducts": 0,
						  "Filters": [
							{
							  "FilterName": 0,
							  "DisplayName": "SKU / Title / Barcode",
							  "FilterNameExact": [
								"sample string 1"
							  ],
							  "Field": 2,
							  "Condition": 0,
							  "ConditionDisplayName": "Equals",
							  "Value": "sample string 1"
							}
						  ],
						  "Columns": [
							{
							  "ColumnName": 0,
							  "DisplayName": "SKU",
							  "ExportName": "SKU",
							  "Group": 0,
							  "Field": 2,
							  "SortDirection": 0,
							  "Width": 1.1,
							  "IsEditable": false
							}
						  ],
						  "Channels": [
							{
							  "Source": "sample string 1",
							  "SubSource": "sample string 2",
							  "SourceVersion": "sample string 3",
							  "SourceType": "sample string 4",
							  "Width": 5.1,
							  "ChannelId": 6
							}
						  ]
						}
			*/
			
			$params = "view=" . json_encode($params);
			$c_data = self::api_call( "POST", "/api/Inventory/CreateUserSpecificView", $params); // Call API

			return $c_data;
		}
		function DeleteBatchesByStockItemId( $params ) {
			/*
				stockItemId=7ae91326-9189-4120-9666-82e309747c61
			*/
			
			$params = "stockItemId=" . $params;
			$c_data = self::api_call( "POST", "/api/Inventory/DeleteBatchesByStockItemId", $params); // Call API

			return $c_data;
		}
		function DeleteBatchInventoryInBulk( $params ) {
			/*
				batchInventoryIds=[
									  1
									]
			*/
			
			$params = "batchInventoryIds=" . json_encode($params);
			$c_data = self::api_call( "POST", "/api/Inventory/DeleteBatchInventoryInBulk", $params); // Call API

			return $c_data;
		}
		function DeleteCategoryById( $params ) {
			/*
				categoryId=61559246-ac7d-44b6-b591-b82892f5b4b9
			*/
			
			$params = "categoryId=" . $params;
			$c_data = self::api_call( "POST", "/api/Inventory/DeleteCategoryById", $params); // Call API

			return $c_data;
		}
		function DeleteCountries( $params ) {
			/*
				countriesIds=[
								  "b61b7342-0f35-49e2-8d77-6e99e25df45d"
								]
			*/
			
			$params = "countriesIds=" . json_encode($params);
			$c_data = self::api_call( "POST", "/api/Inventory/DeleteCountries", $params); // Call API

			return $c_data;
		}
		function DeleteEbayCompatibilityList( $params ) {
			/*
				request={
						  "EbayCompatibilityList": [
							{
							  "FkStockItemId": "856023b0-29ba-48e0-9450-c3e8b04e0ee8",
							  "FkCompatibilityListId": "60cea184-6cc4-4676-8946-f6172d9a8556",
							  "SKU": "sample string 1",
							  "CompatibilityNotes": "sample string 2",
							  "Value": "sample string 3",
							  "IncludeYears": "sample string 4",
							  "ExcludeYears": "sample string 5",
							  "Culture": "sample string 6"
							}
						  ]
						}
			*/
			
			$params = "request=" . json_encode($params);
			$c_data = self::api_call( "POST", "/api/Inventory/DeleteEbayCompatibilityList", $params); // Call API

			return $c_data;
		}
		function DeleteImagesFromInventoryItem( $params ) {
			/*
				inventoryItemImages={
									  "97766571-b5da-4ade-8d0e-959cde7bb757": [
										"sample string 1"
									  ]
									}
			*/
			
			$params = "inventoryItemImages=" . json_encode($params);
			$c_data = self::api_call( "POST", "/api/Inventory/DeleteImagesFromInventoryItem", $params); // Call API

			return $c_data;
		}
		function DeleteInventoryItemChannelSKUs( $params ) {
			/*
				inventoryItemChannelSKUIds=[
											  "0fe868ac-8a91-4914-8731-b966e13458c0"
											]
			*/
			
			$params = "inventoryItemChannelSKUIds=" . json_encode($params);
			$c_data = self::api_call( "POST", "/api/Inventory/DeleteInventoryItemChannelSKUs", $params); // Call API

			return $c_data;
		}
		function DeleteInventoryItemCompositions( $stockItemId, $inventoryItemCompositionIds ) {
			/*
				stockItemId=5cb347ba-8f4e-4152-84e4-27609c26e88f
							&inventoryItemCompositionIds=[
							  "28fba802-013d-4ae2-bc34-3b00d30665f1"
							]
			*/
			
			if ( !is_array( $inventoryItemCompositionIds ) ) {
				$inventoryItemCompositionIds = array( $inventoryItemCompositionIds );
			}
			
			$params = http_build_query( array(
							"stockItemId" => $stockItemId,
							"inventoryItemCompositionIds" => json_encode($inventoryItemCompositionIds)
						));
	
			$c_data = self::api_call( "POST", "/api/Inventory/DeleteInventoryItemCompositions", $params); // Call API

			return $c_data;
		}
		function DeleteInventoryItemDescriptions( $params ) {
			/*
				inventoryItemDescriptionIds=[
											  "cd6c1588-c62d-4514-83dd-8fcee7000bb1"
											]
			*/
			
			if ( !is_array( $params ) ) {
				$params = array( $params );
			}	
			
			$params = "inventoryItemDescriptionIds=" . json_encode($params);
			$c_data = self::api_call( "POST", "/api/Inventory/DeleteInventoryItemDescriptions", $params); // Call API

			return $c_data;
		}
		function DeleteInventoryItemExtendedProperties( $inventoryItemId, $inventoryItemExtendedPropertyIds ) {
			/*
				inventoryItemId=ce16ec22-b13f-46c9-acb6-2aa07ac02f6d
								&inventoryItemExtendedPropertyIds=[
										  "dd8508ea-a01c-4f0c-8137-1028eda302a9"
										]
				*/
			if ( !is_array( $inventoryItemExtendedPropertyIds ) ) {
				$inventoryItemExtendedPropertyIds = array( $inventoryItemExtendedPropertyIds );
			}
			
			$params = http_build_query( array(
							"inventoryItemId" => $inventoryItemId,
							"inventoryItemExtendedPropertyIds" => json_encode($inventoryItemExtendedPropertyIds)
						));
						
			$c_data = self::api_call( "POST", "/api/Inventory/DeleteInventoryItemExtendedProperties", $params); // Call API

			return $c_data;
		}
		function DeleteInventoryItemPrices( $params ) {
			/*
				inventoryItemPriceIds=[
										  "8f2379f4-7799-493b-b35a-469c50937a70"
										]
			*/
			
			if ( !is_array( $params ) ) {
				$params = array( $params );
			}
			
			$params = "inventoryItemPriceIds=" . json_encode($params);
			$c_data = self::api_call( "POST", "/api/Inventory/DeleteInventoryItemPrices", $params); // Call API

			return $c_data;
		}
		function DeleteInventoryItemPricingRules( $params ) {
			/*
				pricingRuleIds=[
								  1
								]
			*/
			
			if ( !is_array( $params ) ) {
				$params = array( $params );
			}
			
			$params = "pricingRuleIds=" . json_encode($params);
			$c_data = self::api_call( "POST", "/api/Inventory/DeleteInventoryItemPricingRules", $params); // Call API

			return $c_data;
		}
		function DeleteInventoryItems( $InventoryItemIds, $inventoryItemIds ) {
			/*
				request={
						  "InventoryItemIds": [
							"972661f3-f9ec-40b1-a92a-8aefe5787630"
						  ],
						  "SelectedRegions": [
							{
							  "Item1": 1,
							  "Item2": 2
							}
						  ],
						  "Token": "74bfada1-4f46-4b85-b164-6488cdb66f7d"
						}&inventoryItemIds=[
						  "1aff9db3-3cf4-4cbf-96e9-dc515550bc4c"
						]
			*/
			
			$params = http_build_query( array( "request" => json_encode( $InventoryItemIds ),
							"inventoryItemIds" => json_encode( $inventoryItemIds ) ) );

			$c_data = self::api_call( "POST", "/api/Inventory/DeleteInventoryItems", $params); // Call API

			return $c_data;
		}
		function DeleteInventoryItemTitles( $params ) {
			/*
				inventoryItemTitleIds=[
										  "f201654d-892f-48c0-bd6a-61730008433f"
										]
			*/
			
			if ( !is_array( $params ) ) {
				$params = array( $params );
			}
			
			$params = "inventoryItemTitleIds=" . json_encode($params);
			$c_data = self::api_call( "POST", "/api/Inventory/DeleteInventoryItemTitles", $params); // Call API

			return $c_data;
		}
		function DeleteItemLocations( $inventoryItemId, $itemLocations ) {
			/*
				inventoryItemId=308f69e7-1400-4ccd-b7d4-10e6755e2132
				&itemLocations=[
								  "391c1bd5-27f0-45af-b795-43a8fd946b8a"
								]
			*/
			
			$params = http_build_query( 
						array( "inventoryItemId" =>  $inventoryItemId,
								"itemLocations" => json_encode( $itemLocations ) ) );

			$c_data = self::api_call( "POST", "/api/Inventory/DeleteItemLocations", $params); // Call API

			return $c_data;
		}
		function DeleteProductIdentifiers( $params ) {
			/*
				request={
						  "ProductIdentifierIds": [
							1
						  ]
						}
			*/
			
			$params = "request=" . json_encode($params);
			$c_data = self::api_call( "POST", "/api/Inventory/DeleteProductIdentifiers", $params); // Call API

			return $c_data;
		}
		function DeleteScrapCategories( $params ) {
			/*
				request={
						  "CategoryIds": [
							1
						  ]
						}
			*/
			
			$params = "request=" . json_encode($params);
			$c_data = self::api_call( "POST", "/api/Inventory/DeleteScrapCategories", $params); // Call API

			return $c_data;
		}
		function DeleteStockSupplierStat( $stockItemId, $itemSupplierIds ) {
			/*
			stockItemId=9d29a3c2-996c-4a41-b5a7-df41d0b3eb04
			&itemSupplierIds=[
								  "221cfba1-9c6d-4d79-8930-12eb773db8c0"
								]
			*/
			
			$params = http_build_query( 
						array( "stockItemId" =>  $stockItemId,
								"itemSupplierIds" => json_encode( $itemSupplierIds ) ) );
			
			$c_data = self::api_call( "POST", "/api/Inventory/DeleteStockSupplierStat", $params); // Call API

			return $c_data;
		}
		function DeleteSuppliers( $params ) {
			/*
				suppliersIds=[
								  "9497e95a-39b2-4d13-9056-663fb8209015"
								]
			*/
			
			if ( !is_array( $params ) ) {
				$params = array( $params );
			}
			
			$params = "suppliersIds=" . json_encode($params);
			$c_data = self::api_call( "POST", "/api/Inventory/DeleteSuppliers", $params); // Call API

			return $c_data;
		}
		function DeleteUserSpecificView( $params ) {
			/*
					viewName=sample string 1
			*/
			
			$params = "viewName=" . $params;
			$c_data = self::api_call( "POST", "/api/Inventory/DeleteUserSpecificView", $params); // Call API

			return $c_data;
		}
		function DuplicateInventoryItem( $inventoryItem, $sourceItemId, $Images) {
			/*
				inventoryItem={
							  "ItemDescription": "sample string 1",
							  "Quantity": 2,
							  "InOrder": 3,
							  "Due": 4,
							  "MinimumLevel": 5,
							  "Available": -1,
							  "IsCompositeParent": true,
							  "ItemNumber": "sample string 6",
							  "ItemTitle": "sample string 7",
							  "BarcodeNumber": "sample string 8",
							  "MetaData": "sample string 9",
							  "isBatchedStockType": false,
							  "PurchasePrice": 10.1,
							  "RetailPrice": 1.1,
							  "TaxRate": 11.1,
							  "PostalServiceId": "a1e07cc6-bd7c-41ad-9092-9e1afba06524",
							  "PostalServiceName": "sample string 13",
							  "CategoryId": "ca18b494-c585-493a-ad6a-46f282338296",
							  "CategoryName": "sample string 15",
							  "PackageGroupId": "a67093fd-2a7c-4e56-9fdd-de2badfa4187",
							  "PackageGroupName": "sample string 17",
							  "Height": 18.1,
							  "Width": 19.1,
							  "Depth": 20.1,
							  "Weight": 21.1,
							  "CreationDate": "2021-05-05T15:35:21.7877098+01:00",
							  "InventoryTrackingType": 22,
							  "BatchNumberScanRequired": true,
							  "SerialNumberScanRequired": true,
							  "StockItemId": "3ea3be81-c389-4275-864d-50f6711390bc",
							  "StockItemIntId": 26
							}
				&sourceItemId=b0b2bb0e-f979-4da6-9d24-87d0124eb4d1
				&Images=true
			*/
			
			$params = http_build_query( 
						array( "inventoryItem" =>  json_encode($inventoryItem),
								"sourceItemId" => $sourceItemId,
								"Images" => $Images ) );
						
			$c_data = self::api_call( "POST", "/api/Inventory/DuplicateInventoryItem", $params); // Call API

			return $c_data;
		}
		function GetAllExtendedPropertyNames() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetBatchAudit() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetBatchesByStockItemId() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetBatchInventoryById() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetCategories() {
			/*
			
			*/

			$c_data = self::api_call( "POST", "/api/Inventory/GetCategories"); // Call API

			return $c_data;
		}
		function GetChannels() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetChannelsBySource() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetCountries() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetCountryCodes() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetEbayCompatibilityList() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetExtendedPropertyNames() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetExtendedPropertyTypes() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetImagesInBulk() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetInventoryBatchTypes() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetInventoryItemAuditTrail() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetInventoryItemBatchInformation() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetInventoryItemBatchInformationByIds() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetInventoryItemById() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetInventoryItemChannelSKUs() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetInventoryItemChannelSKUsWithLocation() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetInventoryItemCompositions() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetInventoryItemDescriptions() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetInventoryItemExtendedProperties() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetInventoryItemImages() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetInventoryItemLocations() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetInventoryItemPriceChannelSuffixes() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetInventoryItemPriceRulesById() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetInventoryItemPriceRulesBySource() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetInventoryItemPrices() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetInventoryItemPriceTags() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetInventoryItemsCompositionByIds() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetInventoryItemsCount() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetInventoryItemTitles() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetNewItemNumber() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetPackageGroups() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetInventoryPostalServices() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetPreDefinedViews() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetProductIdentifiersBulkByStockItemId() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetProductIdentifiersByStockItemId() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetProductIdentifierTypes() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetScrapCategories() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetScrapHistory() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetStockItemBatchesByLocation() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetStockItemIdsBySKU() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetStockItemLabels() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetStockLocations() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetStockSupplierStat() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetSupplierDetails( $supplierID ) {
			/*
				supplierId=d1352b3c-18c2-4f5a-8945-524dad9f877a
			*/

			$params = "supplierId=". $supplierID ;
			
			$c_data = self::api_call( "POST", "/api/Inventory/GetSupplierDetails", $params); //
			
			return $c_data;
		}
		function GetSuppliers() {
			/*
				No parameters required.
			*/

			$c_data = self::api_call( "POST", "/api/Inventory/GetSuppliers" ); //

			return $c_data;
		}
		function GetUserSpecificViews() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function HasStockItemBatches() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function HasStockItemStockLevel() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function InsertUpdateEbayCompatibilityList() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function IsInventoryItemChannelSKULinked() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function IsOwnedStockLocation() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function ScrapBatchedItem() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function SetInventoryItemImageAsMain() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UnarchiveInventoryItems() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UnlinkChannelListing() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateBatchDetails() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateBatchesWithInventory() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateCategory() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateCompositeParentStockLevel() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateCountries() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateCountryRegions() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateImages() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateInventoryItem() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateInventoryItemChannelSKUs() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateInventoryItemChannelSKUsWithLocation() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateInventoryItemCompositions() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateInventoryItemDescriptions() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateInventoryItemExtendedProperties() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateInventoryItemField() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateInventoryItemLevels() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateInventoryItemLocationField() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateInventoryItemPrices() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateInventoryItemPricingRules() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateInventoryItemStockField() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateInventoryItemTitles() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateItemLocations() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateProductIdentifiers() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateScrapCategories() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateStockSupplierStat() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateSupplier() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateUserSpecificView() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UploadImagesToInventoryItem() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}

		// Listings
		
		function EndListingsPendingRelist( $params ) {
			/*
			request={
					  "Listings": [
						{
						  "ItemNumber": "sample string 1",
						  "ActionType": "sample string 2",
						  "ActionText": "sample string 3",
						  "AffectivefkStockItemId": "e5ac7ac6-8a18-4bfc-af4a-c2056add13ad",
						  "ListId": "32ea9ed3-5980-493e-98ec-b9fbeb3cb330",
						  "ActionDateTime": "2021-05-05T15:35:21.5254118+01:00",
						  "SetQuantity": 7,
						  "SetPrice": 8.1,
						  "IsError": true,
						  "SKU": "sample string 10"
						}
					  ]
					}
			*/
			
			$params = "request=" . json_encode($params);
			$c_data = self::api_call( "POST", "/api/Listings/EndListingsPendingRelist", $params); // Call API

			return $c_data;
		}
		function GetEbayListingAudit( $params ) {
			/*
				request={
						  "ItemNumber": "sample string 1",
						  "PageNumber": 2,
						  "EntriesPerPage": 3
						}
			*/
			
			$params = "request=" . json_encode($params);
			$c_data = self::api_call( "POST", "/api/Listings/GetEbayListingAudit", $params); // Call API

			return $c_data;
		}
		function SetListingStrikeOffState( $params ) {
			/*
				request={
						  "ListingAudits": [
												{
													"ItemNumber": String
													"ActionType": String 	
													"ActionText": String 	
													"AffectivefkStockItemId": Guid 	
													"ListId": Guid 	
													"ActionDateTime": DateTime 	
													"SetQuantity": Int32 	
													"SetPrice": Double 	
													"IsError": Boolean 	
													"SKU": String
												}
											],
						  "Listings": [
										{
											"SKU": String 	
											"ItemNumber": String
											"MappedBy"; String
											"RelistPending": Boolean
											"ChannelReferenceId": String
											"StrickenOff": Boolean
											"StockItemId": Guid
											"StartTime": DateTime
											"EndTime": DateTime
											"StrikeOffDate": DateTime
											"StrikeReason": String
											"LinkedWith": String
											"IsVariation": Boolean
											"FixedPrice": Boolean
											"ReslistedFrom": String
											"ListId": Guid
											"ListingPrice": Double
											"VariationItems": List<eBayItem>
											"IsGTC": Boolean
											"IsActive": Boolean
											"ChannelSKURowId": Guid
											"LinkedItemId": Guid
											"IgnoreSync": Boolean
											"Title": String
											"LinkedItemSku": String
											"LinkedItemTitle": String
											"MaxListedQuantity": Int32
											"EndWhenStock": Int32
											"StockPercentage": Double
											"Quantity": Int32
											"IsLinked": Boolean
											"IsSuggestedToLink": Boolean
											"IsMatchByTitle": Boolean
											"TotalRows": Int64
										}
									],
						  "StrikeReason": "sample string",
						  "StrikeOffState": 0,
						  "ListingsStatus": active
						}		
			*/
			
			$params = "request=" . json_encode($params);
			$c_data = self::api_call( "POST", "/api/Listings/SetListingStrikeOffState", $params); // Call API

			return $c_data;
		}

		// Locations
		
		function AddLocation( $params ) {
			/*
				location={
						  "Address1": "sample string 1",
						  "Address2": "sample string 2",
						  "City": "sample string 3",
						  "County": "sample string 4",
						  "Country": "sample string 5",
						  "ZipCode": "sample string 6",
						  "IsNotTrackable": true,
						  "LocationTag": "sample string 8",
						  "CountInOrderUntilAcknowledgement": true,
						  "FulfilmentCenterDeductStockWhenProcessed": true,
						  "IsWarehouseManaged": true,
						  "StockLocationId": "a439420c-680d-4910-b436-cf90f00e5807",
						  "LocationName": "sample string 13",
						  "IsFulfillmentCenter": true,
						  "StockLocationIntId": 1
						}
			*/
			
			$params = "location=" . json_encode($params);
			$c_data = self::api_call( "POST", "/api/Locations/AddLocation", $params); // Call API

			return $c_data;
		}
		function DeleteLocation( $params ) {
			/*
				pkStockLocationId=5781bdf0-eae2-4587-b431-673191d56964
			*/
			
			$params = "pkStockLocationId=" . $params;
			$c_data = self::api_call( "POST", "/api/Locations/DeleteLocation", $params); // Call API

			return $c_data;
		}
		function DeleteWarehouseTOTE( $params ) {
			/*
				request={
						  "ToteIds": [
							1
						  ],
						  "LocationId": "b232eae8-96df-467c-9952-10bab93a718b"
						}
			*/
			
			$params = "request=" . json_encode($params);
			$c_data = self::api_call( "POST", "/api/Locations/DeleteWarehouseTOTE", $params); // Call API

			return $c_data;
		}
		function GetLocation( $params ) {
			/*
				pkStockLocationId=9f542122-8f3b-4ee9-ad67-4fa9f908519b
			*/
			
			$params = "pkStockLocationId=" . $params;
			$c_data = self::api_call( "POST", "/api/Locations/GetLocation", $params); // Call API

			return $c_data;
		}
		function GetWarehouseTOTEs( $params ) {
			/*
				request={
						  "LocationId": "e91a0ae0-b3e8-4d5c-a321-2133e9e49fc2",
						  "ToteBarcode": "sample string 2",
						  "TotId": 1
						}
			*/
			
			$params = "request=" . json_encode($params);
			$c_data = self::api_call( "POST", "/api/Locations/GetWarehouseTOTEs", $params); // Call API

			return $c_data;
		}
		function UpdateLocation( $params ) {
			/*
				location={
						  "Address1": "sample string 1",
						  "Address2": "sample string 2",
						  "City": "sample string 3",
						  "County": "sample string 4",
						  "Country": "sample string 5",
						  "ZipCode": "sample string 6",
						  "IsNotTrackable": true,
						  "LocationTag": "sample string 8",
						  "CountInOrderUntilAcknowledgement": true,
						  "FulfilmentCenterDeductStockWhenProcessed": true,
						  "IsWarehouseManaged": true,
						  "StockLocationId": "4c847e52-b8b1-4f04-a39c-238283b1e060",
						  "LocationName": "sample string 13",
						  "IsFulfillmentCenter": true,
						  "StockLocationIntId": 1
						}
			*/
			
			$params = "location=" . json_encode($ordparamsernum);
			$c_data = self::api_call( "POST", "/api/Locations/UpdateLocation", $params); // Call API

			return $c_data;
		}

		// Macro
		
		function GetInstalledMacros() {
			/*
				request={}
			*/
			
			$params = "request={}";
			$c_data = self::api_call( "POST", "/api/Macro/GetInstalledMacros", $params); // Call API

			return $c_data;
		}
		function GetMacroConfigurations() {
			/*
				request={}
			*/
			
			$params = "request={}";
			$c_data = self::api_call( "POST", "/api/Macro/GetMacroConfigurations", $params); // Call API

			return $c_data;
		}

		// Order
		
		function AddCoupon() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function AddOrderItem() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function AddOrderService() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function AssignOrderItemBatches() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function AssignStockToOrder() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function AssignToFolder() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CancelOrder() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function ChangeOrderTag() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function ChangeShippingMethod() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function ChangeStatus() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function ClearInvoicePrinted() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function ClearPickListPrinted() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function ClearShippingLabelInfo() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CompleteOrder() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CreateNewItemAndLink() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CreateNewOrder() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CreateOrders() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CustomerLookUp() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteOrder() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetOpenOrderBasicInfoFromItems() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetAllAvailableOrderItemBatchsByOrderId() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetAllOpenOrders() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetAllOpenOrdersBetweenIndex() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetAssignedOrderItemBatches() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetAvailableFolders() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetBatchPilots() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetOrderCountries() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetDefaultPaymentMethodIdForNewOrder() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetDraftOrders() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetExtendedProperties() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetOrderExtendedPropertyNames() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetOrderExtendedPropertyTypes() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetLinkedItems() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetOpenOrderIdByOrderOrReferenceId() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetOpenOrderItemsSuppliers() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetOpenOrders() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetOpenOrdersByItemBarcode() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetOrder() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetOrderAuditTrail() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetOrderAuditTrailsByIds() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetOrderById() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetOrderDetailsByNumOrderId( $ordernum ) {
			/*
				Gets order information for order that matches ordernum
				Paramenters
				$ordernum	type = Int
				Order number to look for.
				returns array
			*/
			
			$params = "OrderId=" . $ordernum;
			$c_data = self::api_call( "POST", "/api/Orders/GetOrderDetailsByNumOrderId", $params); //

			return $c_data;
		}
		function GetOrderDetailsByReferenceId() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetOrderItemBatchesByOrderIds() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetOrderItemBatchsByOrderId() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetOrderItemComposition() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetOrderItems() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetOrderNotes( $orderID ) {
			/*

			*/

			$params = "orderId=" . $orderID;
			$c_data = self::api_call( "POST", "/api/Orders/GetOrderNotes", $params); //

			return $c_data;
		}
		function GetOrderNoteTypes() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetOrderPackagingCalculation() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetOrderPackagingSplit() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetOrderRelations( $ordernum ) {
			/*
				Gets order information for order that matches ordernum
				
				Paramenters
				
				$ordernum	type = Int
				
				returns array
			*/

			$params = "OrderId=" . $ordernum ;
			$c_data = self::api_call( "POST", "/api/Orders/GetOrderRelations", $params); //

			return $c_data;
		}
		function GetOrders() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetOrdersById() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetOrderView() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetOrderViews() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetOrderXml() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetOrderXmlJSTree() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetPackagingGroups() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetPaymentMethods() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetShippingMethods() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetUserLocationId() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function LockOrder() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function MergeOrders() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function MoveToFulfilmentCenter() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function MoveToLocation() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function ProcessFulfilmentCentreOrder() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function ProcessOrder() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function ProcessOrderRequiredBatchScans() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function ProcessOrderByOrderOrReferenceId() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function ProcessOrdersInBatch() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function RecalculateSingleOrderPackaging() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function RemoveOrderItem() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function RunRulesEngine() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function SaveOrderView() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function SetAdditionalInfo() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function SetAvailableFolders() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function SetDefaultPaymentMethodIdForNewOrder() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function SetExtendedProperties() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function SetInvoicesPrinted() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function SetLabelsPrinted() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function SetOrderCustomerInfo() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function SetOrderGeneralInfo() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function SetOrderNotes( $orderID, $notes ) {
			/*
			
			*/

			$params = http_build_query( array( 
				"orderId" => $orderID,
				"orderNotes" => json_encode($notes) ),"&");

			$c_data = self::api_call( "POST", "/api/Orders/SetOrderNotes", $params); //

			return $c_data;
		}
		function SetOrderPackaging() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function SetOrderPackagingSplit() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function SetOrderShippingInfo() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function SetOrderSplitPackagingManualOverwrite() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function SetOrderTotalsInfo() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function SetPaymentMethods() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function SetPickListPrinted() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function SplitOrder() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UnassignToFolder() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateAdditionalInfo() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateBillingAddress() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateLinkItem() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateOrderItem() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function ValidateCoupon() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}

		// Order Workflow
		
		function CheckinUser() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeallocateOrderFromJob() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetGroup() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetGroupList() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetJob() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetJobAudit() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetJobByName() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetJobErrors() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetPrintAttachment() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetWorkflow() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function Run() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateGroup() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}

		// Picking
		
		function CheckAllocatableToPickwave() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteOrdersFromPickingWaves() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GeneratePickingWave() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetAllPickingWaveHeaders() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetAllPickingWaves() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetItemBinracks() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetMyPickingWaveHeaders() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetMyPickingWaves() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetPickingWave() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetPickwaveUsersWithSummary() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdatePickedItemDelta() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdatePickingWaveHeader() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdatePickingWaveItem() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdatePickingWaveItemWithNewBinrack() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}

		// Postal Services
		
		function CreatePostalService() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeletePostalService() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetChannelLinks() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetPostalServices() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdatePostalService() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}

		// Post Sale
		
		function CreateCancellation() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}

		// Print Service
		
		function CreatePDFfromJobForceTemplate() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CreatePDFfromJobForceTemplateStockIn() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CreatePDFfromJobForceTemplateWithQuantities() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CreateReturnShippingLabelsPDF() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CreateReturnShippingLabelsPDFWithSKUs() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetTemplateList() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetUsersForPrinterConfig() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function PrintTemplatePreview() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function VPGetPrinters() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}

		// Processed Orders
		
		function AddOrderNote( $orderID, $note, $internal ) {
			/*
				Add an additional note to a order.
				Parameters
				$orderID	Type String
				Linnworks Order GUI reference ID
				$note		Type String
				String containing the note you wish to add
				$internal	Type Bool
				true or false
			*/
			
			if ( $this->linn_auth_data == false ) {
				// No connection to server
				error_log("Linnworks API:AddOrderNote.  No Connection", 0);
				return false;
			} else {
				$notes = $this->GetOrderNotes( $orderID ); // Get order details
				$notes[] = array(
					"OrderId" => $orderID,
					"Note" => $note,
					"Internal"=>$internal
				);
				//echo $this->debug_display( $notes, "Notes" );
				$outcome = $this->SetOrderNotes( $orderID, $notes );
				return $outcome;
			}
		}
		function AddReturnCategory() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function ChangeOrderNote() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CheckOrderFullyReturned() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CreateExchange() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CreateFullResend() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CreateResend() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CreateReturn() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteOrderNote() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteReturnCategory() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DownloadOrdersToCSV() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetChannelRefundReasons() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetOrderInfo() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetPackageSplit() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetProcessedAuditTrail() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetProcessedOrderExtendedProperties() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetProcessedOrderNotes() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetProcessedRelatives() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetRefundableServiceItems() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetRefunds() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetRefundsOptions() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetReturnCategories() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetReturnItemsInfo() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetReturnOrderInfo() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetReturnsExchanges() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetTotalRefunds() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function IsRefundValid() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function IsRefundValidationRequiredByOrderId() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function MarkManualRefundsAsActioned() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function RefundFreeText() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function RefundServices() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function RefundShipping() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function RenameReturnCategory() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function SearchProcessedOrders() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function SearchProcessedOrdersPaged() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function ValidateCompleteOrderRefund() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}

		// PurchaseOrder
		
		function AddAdditionalCostTypes() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function AddPurchaseOrderExtendedProperty() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function AddPurchaseOrderItem() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function AddPurchaseOrderNote() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function ChangePurchaseOrderStatus() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CreatePurchaseOrderInitial() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteAdditionalCostTypes() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeletePurchaseOrder() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeletePurchaseOrderExtendedProperty() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeletePurchaseOrderItem() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeletePurchaseOrderNote() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeliverPurchaseItem() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeliverPurchaseItemAll() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeliverPurchaseItemAllExceptBatchItems() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeliverPurchaseItemsWithQuantity() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function FindStockItem( $params ) {
			/*
				$params{
				"Codes": [
				"sample string 1",
				"sample string 2"
				]
				}
			*/

			$params = "request=" . json_encode( array("codes"=> $params) );
			
			$c_data = self::api_call( "POST", "/api/PurchaseOrder/FindStockItem", $params); //
			
			return $c_data;
		}
		function GetAdditionalCost() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetAdditionalCostTypes() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetDeliveredRecords() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetEmailCSVFile() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetEmailsSent() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetPaymentStatement() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}		
		function GetPurchaseOrder( $poID ) {
			/*
				pkPurchaseId=c6d95dbc-a127-44f9-883e-fb608a2d756a
			*/

				$params = http_build_query( array("pkPurchaseId" => $poID) );
				
				$c_data = self::api_call( "POST", "/api/PurchaseOrder/Get_PurchaseOrder", $params); //

				return $c_data;
		}
		function GetPurchaseOrderAudit() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetPurchaseOrderExtendedProperty() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetPurchaseOrderItemOpenOrders() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetPurchaseOrderNote() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetPurchaseOrderStatusList() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetPurchaseOrdersWithStockItems( $poID ) {
			/*
			
			*/

			$params = "purchaseOrder=" . json_encode( array("orderId" => $poID) );
			
			$c_data = self::api_call( "POST", "/api/PurchaseOrder/GetPurchaseOrdersWithStockItems",	$params); //
		
			return $c_data;
		}
		function ModifyAdditionalCost() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function ModifyAdditionalCostAllocation() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function ModifyPaymentStatement() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function ModifyPurchaseOrderItemsBulk() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function SearchPurchaseOrders( $params ) {
			/*
				$params{
				"DateFrom":"08/15/2019", 
				"DateTo":"08/17/2019", 
				"Status":"OPEN", 
				"ReferenceLike":"987", 
				"EntriesPerPage":"100", 
				"PageNumber":"1", 
				"Location":["3329af95-5329-45f8-beac-e3c5852fc130"], 
				"Supplier":["313c97d4-3877-4087-b6a9-d475634d0857"]
				}
			*/
			
			$params = "searchParameter=" . json_encode( $params );
			
			$c_data = self::api_call( "POST", "/api/PurchaseOrder/Search_PurchaseOrders", $params ); //

			return $c_data;
		}
		function UpdateAdditionalCostTypes() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdatePurchaseOrderExtendedProperty() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdatePurchaseOrderHeader() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdatePurchaseOrderItem() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}

		// Stock
		
		function AddRollingStockTake() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function AddVariationItems() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function BatchStockLevelDelta() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function BookInStockBatch() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function BookInStockItem() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CheckVariationParentSKUExists() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CreateStockBatches() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CreateVariationGroup() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CreateWarehouseMove() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteVariationGroup() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteVariationGroups() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteVariationItem() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteVariationItems() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetItemChangesHistory() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetItemChangesHistoryCSV() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetSoldStat() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetStockConsumption() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetStockDuePO() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetStockItemReturnStat() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetStockItems() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetStockItemsByIds() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetStockItemsByKey( $ikey ) {
			/*
				stockIdentifier={
					"Key": "sample string 1",
					"LocationId": "6c1b3897-e3f1-4fca-bd0e-e722f9b2f14a"
				}
			*/
			
			$params = "stockIdentifier=" . json_encode( $ikey );
			
			$c_data = self::api_call( "POST", "/api/Stock/GetStockItemsByKey", $params); //

			return $c_data;
		}
		function GetStockItemScrapStat() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetStockItemsFull($keywords,$pageid,$pageqty=50,$options="[0,1,2,3,4,5,6,7,8]") {
			/*
				Gets a list of items in the stock inventory which match the keywords

				Parameters
				
				$keywords 	type = string
					Keywords, SKU or barcode of item to look for.
				$pageid		type = int
					Page index number
				$pageqty		type = int
					quatity of items per page
				$options	type = string	Default = "[0,1,2,3,4,5,6,7,8]"
					0 = StockLevels, 
					1 = Pricing, 
					2 = Supplier, 
					3 = ShippingInformation, 
					4 = ChannelTitle, 
					5 = Channel Description, 
					6 = ChannelPrice, 
					7 = ExtendedProperties, 
					8 = Images 

				returns array
			*/

			$params = http_build_query( array(
										"keyword" => $keywords,
										"loadCompositeParents" => "True",
										"loadVariationParents" => "False",
										"entriesPerPage" => $pageqty,
										"pageNumber" => $pageid,
										"dataRequirements" => $options,
										"searchTypes" => "[0,1,2]"
										),"&");
										
			$c_data = self::api_call( "POST", "/api/Stock/GetStockItemsFull", $params); //

			return $c_data;
		}
		function GetStockItemsFullByIds() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetStockItemTypeInfo() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetStockLevel() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetStockLevelBatch() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetStockLevelByLocation() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetStockSold() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetVariationGroupByName() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetVariationGroupByParentId() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetVariationGroupSearchTypes() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetVariationItems() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function RenameVariationGroup() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function SearchVariationGroups() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function SetStockLevel() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function SKUExists() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateStockItemPartial() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateSkuGroupIdentifier() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateStockLevelsBulk() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateStockLevelsBySKU() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateStockMinimumLevel() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}

	}
?>