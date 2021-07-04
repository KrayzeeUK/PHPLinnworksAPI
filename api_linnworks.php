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
		
		private $debug = false; 			// Enable debug mode (Call enable_debug function to enable)

		private $log_api = true;			// Enable API Logging
		private $log_dir = NULL;			// Director to save API Call files in
		
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

			$html = "";

			if ( $this->debug ) {
				$html .= "<br><hr>" . $title . "<hr><pre>";
				$html .= "<textarea style=\"" . $style . "\">" . print_r( $vals,true ) . "</textarea>";
				$html .= "<pre><hr><br>";
			}			
			
			return $html;
		}

		function set_log_dir( $path ) {
			
			if ( substr($path, -1) != "\\" ) {
				$path = $path . "\\";
			}
			
			$this->log_dir = $path; // Set log path
		}
		protected function log_api_calls( $log ) {

			if ( $this->log_api AND $this->log_dir != NULL ) {
				
				$lfname = $this->log_dir . "api_log_" . date('Y-m-d') . ".log";
				
				if ( !file_exists ( $this->log_dir ) ) {
					// Directory Doesnt Exist.
					if ( !mkdir( $this->log_dir, 0777, true) ) {
						// if unable to make directory exit with failure message
						Die("Failed to create file struction");
					}
				}
				
				file_put_contents( $lfname, var_export( $log, true ), FILE_APPEND );
				
			}
		}
		
		// Main API Calling Routine

		protected function api_call( $type, $api_url, $api_params=NULL, $api_headers=NULL, $api_options=NULL ) {
			/*
				Set all require headers for API Authorisation
			*/
			
			$log_data["CallType"] = $type; // Assign call type to log

			$d_header[] = "Connection: keep-alive";
			$d_header[] = "Accept: application/json";
			$d_header[] = "Content-Type: application/x-www-form-urlencoded; charset=UTF-8";
			$d_header[] = "Accept-Encoding: gzip, deflate";

			if ( $this->linn_auth_token != NULL ) {
				$d_header[] = "Authorization: " . $this->linn_auth_token ;
				
				$api_url = $this->linn_auth_server . $api_url;
			}
			
			$log_data["URL"] = $api_url; // Assign URL to log
			
			if ( $api_headers != NULL AND !empty($api_headers) ) {
				$d_header = array_merge( $d_header, $api_headers ); // Merge Default Headers with additional headers
			}
			
			$log_data["Headers"] = $d_header; // Assign headers to log
			
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
			
			$log_data["Parameters"] = $api_params; // Assign parameters to log
			
			if ( $this->debug ) {
				echo "URL: " . $api_url . "<hr>";
				echo $this->debug_display( $d_options, "Options" );
				echo $this->debug_display( $d_header, "Headers" );
				echo $this->debug_display( $api_params, "parameters" );
			}
			
			curl_setopt_array($this->curl_handle, $d_options); // Set all options

			$session_data = json_decode( curl_exec($this->curl_handle), true ); // Execute Curl function and store return & decode json return

			$log_data["APIReturn"] = $session_data; // Assign API Return data to log

			if ( $this->debug ) {
				echo $this->debug_display( curl_getinfo($this->curl_handle), "CURL Info" );
				echo $this->debug_display( $session_data, "Session Data" );
			}

			$this->log_api_calls( $log_data, "API Call" ); // Log API Call

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
					"getapplicationprofilebysecretkey" => array( "type" => "POST", "url" => "/api/Auth/GetApplicationProfileBySecretKey", "noparams" => 3),
					"getserverutctime" => array( "type" => "POST", "url" => "/Api/Auth/GetServerUTCTime", "noparams" => 0),
					"createnewcustomer" => array( "type" => "POST", "url" => "/Api/Customer/CreateNewCustomer", "noparams" => 1),
					"executecustompagedscript" => array( "type" => "POST", "url" => "/Api/Dashboards/ExecuteCustomPagedScript", "noparams" => 1),
					"executecustompagedscript_customer" => array( "type" => "POST", "url" => "/Api/Dashboards/ExecuteCustomPagedScript_Customer", "noparams" => 1),
					"executecustomscriptquery" => array( "type" => "POST", "url" => "/Api/Dashboards/ExecuteCustomScriptQuery", "noparams" => 1),
					"getinventorylocationcategoriesdata" => array( "type" => "POST", "url" => "/Api/Dashboards/GetInventoryLocationCategoriesData", "noparams" => 1),
					"getinventorylocationdata" => array( "type" => "POST", "url" => "/Api/Dashboards/GetInventoryLocationData", "noparams" => 1),
					"getinventorylocationproductsdata" => array( "type" => "POST", "url" => "/Api/Dashboards/GetInventoryLocationProductsData", "noparams" => 1),
					"getlowstocklevel" => array( "type" => "POST", "url" => "/Api/Dashboards/GetLowStockLevel", "noparams" => 1),
					"getperformancedetail" => array( "type" => "POST", "url" => "/Api/Dashboards/GetPerformanceDetail", "noparams" => 1),
					"getperformancetabledata" => array( "type" => "POST", "url" => "/Api/Dashboards/GetPerformanceTableData", "noparams" => 1),
					"gettopproducts" => array( "type" => "POST", "url" => "/Api/Dashboards/GetTopProducts", "noparams" => 1),
					"generateadhocemail" => array( "type" => "POST", "url" => "/Api/Email/GenerateAdhocEmail", "noparams" => 1),
					"generatefreetextemail" => array( "type" => "POST", "url" => "/Api/Email/GenerateFreeTextEmail", "noparams" => 1),
					"getemailtemplate" => array( "type" => "POST", "url" => "/Api/Email/GetEmailTemplate", "noparams" => 1),
					"getemailtemplates" => array( "type" => "POST", "url" => "/Api/Email/GetEmailTemplates", "noparams" => 1),
					"deletesetting" => array( "type" => "POST", "url" => "/Api/Extensions/DeleteSetting", "noparams" => 1),
					"getsetting" => array( "type" => "POST", "url" => "/Api/Extensions/GetSetting", "noparams" => 1),
					"getsettingkeys" => array( "type" => "POST", "url" => "/Api/Extensions/GetSettingKeys", "noparams" => 1),
					"getsettings" => array( "type" => "POST", "url" => "/Api/Extensions/GetSettings", "noparams" => 1),
					"setsetting" => array( "type" => "POST", "url" => "/Api/Extensions/SetSetting", "noparams" => 1),
					"deleteexport" => array( "type" => "POST", "url" => "/Api/ImportExport/DeleteExport", "noparams" => 1),
					"deleteimport" => array( "type" => "POST", "url" => "/Api/ImportExport/DeleteImport", "noparams" => 1),
					"downloadimportedfile" => array( "type" => "POST", "url" => "/Api/ImportExport/DownloadImportedFile", "noparams" => 1),
					"enableexport" => array( "type" => "POST", "url" => "/Api/ImportExport/EnableExport", "noparams" => 1),
					"enableimport" => array( "type" => "POST", "url" => "/Api/ImportExport/EnableImport", "noparams" => 1),
					"getexport" => array( "type" => "POST", "url" => "/Api/ImportExport/GetExport", "noparams" => 1),
					"getexportlist" => array( "type" => "POST", "url" => "/Api/ImportExport/GetExportList", "noparams" => 1),
					"getfullfilmentcentersettings" => array( "type" => "POST", "url" => "/Api/ImportExport/GetFullfilmentCenterSettings", "noparams" => 1),
					"getimport" => array( "type" => "POST", "url" => "/Api/ImportExport/GetImport", "noparams" => 1),
					"getimportlist" => array( "type" => "POST", "url" => "/Api/ImportExport/GetImportList", "noparams" => 1),
					"runnowexport" => array( "type" => "POST", "url" => "/Api/ImportExport/RunNowExport", "noparams" => 1),
					"runnowimport" => array( "type" => "POST", "url" => "/Api/ImportExport/RunNowImport", "noparams" => 1),
					"addimagetoinventoryitem" => array( "type" => "POST", "url" => "/Api/Inventory/AddImageToInventoryItem", "noparams" => 1),
					"addinventoryitem" => array( "type" => "POST", "url" => "/Api/Inventory/AddInventoryItem", "noparams" => 1),
					"additemlocations" => array( "type" => "POST", "url" => "/Api/Inventory/AddItemLocations", "noparams" => 1),
					"addproductidentifiers" => array( "type" => "POST", "url" => "/Api/Inventory/AddProductIdentifiers", "noparams" => 1),
					"addscrapcategories" => array( "type" => "POST", "url" => "/Api/Inventory/AddScrapCategories", "noparams" => 1),
					"addscrapitem" => array( "type" => "POST", "url" => "/Api/Inventory/AddScrapItem", "noparams" => 1),
					"addsupplier" => array( "type" => "POST", "url" => "/Api/Inventory/AddSupplier", "noparams" => 1),
					"adjustebaytemplatesdispatchlms" => array( "type" => "POST", "url" => "/Api/Inventory/AdjustEbayTemplatesDispatchLMS", "noparams" => 1),
					"adjustebaytemplatesinstantlms" => array( "type" => "POST", "url" => "/Api/Inventory/AdjustEbayTemplatesInstantLMS", "noparams" => 1),
					"adjusttemplatesinstant" => array( "type" => "POST", "url" => "/Api/Inventory/AdjustTemplatesInstant", "noparams" => 1),
					"archiveinventoryitems" => array( "type" => "POST", "url" => "/Api/Inventory/ArchiveInventoryItems", "noparams" => 1),
					"batchgetinventoryitemchannelskus" => array( "type" => "POST", "url" => "/Api/Inventory/BatchGetInventoryItemChannelSKUs", "noparams" => 1),
					"bulkscrapbatcheditems" => array( "type" => "POST", "url" => "/Api/Inventory/BulkScrapBatchedItems", "noparams" => 1),
					"createbatches" => array( "type" => "POST", "url" => "/Api/Inventory/CreateBatches", "noparams" => 1),
					"createcategory" => array( "type" => "POST", "url" => "/Api/Inventory/CreateCategory", "noparams" => 1),
					"createcountries" => array( "type" => "POST", "url" => "/Api/Inventory/CreateCountries", "noparams" => 1),
					"createcountryregions" => array( "type" => "POST", "url" => "/Api/Inventory/CreateCountryRegions", "noparams" => 1),
					"createinventoryitemchannelskus" => array( "type" => "POST", "url" => "/Api/Inventory/CreateInventoryItemChannelSKUs", "noparams" => 1),
					"createinventoryitemcompositions" => array( "type" => "POST", "url" => "/Api/Inventory/CreateInventoryItemCompositions", "noparams" => 1),
					"createinventoryitemdescriptions" => array( "type" => "POST", "url" => "/Api/Inventory/CreateInventoryItemDescriptions", "noparams" => 1),
					"createinventoryitemextendedproperties" => array( "type" => "POST", "url" => "/Api/Inventory/CreateInventoryItemExtendedProperties", "noparams" => 1),
					"createinventoryitemprices" => array( "type" => "POST", "url" => "/Api/Inventory/CreateInventoryItemPrices", "noparams" => 1),
					"createinventoryitempricingrules" => array( "type" => "POST", "url" => "/Api/Inventory/CreateInventoryItemPricingRules", "noparams" => 1),
					"createinventoryitemtitles" => array( "type" => "POST", "url" => "/Api/Inventory/CreateInventoryItemTitles", "noparams" => 1),
					"createstocksupplierstat" => array( "type" => "POST", "url" => "/Api/Inventory/CreateStockSupplierStat", "noparams" => 1),
					"createuserspecificview" => array( "type" => "POST", "url" => "/Api/Inventory/CreateUserSpecificView", "noparams" => 1),
					"deletebatchesbystockitemid" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteBatchesByStockItemId", "noparams" => 1),
					"deletebatchinventoryinbulk" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteBatchInventoryInBulk", "noparams" => 1),
					"deletecategorybyid" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteCategoryById", "noparams" => 1),
					"deletecountries" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteCountries", "noparams" => 1),
					"deleteebaycompatibilitylist" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteEbayCompatibilityList", "noparams" => 1),
					"deleteimagesfrominventoryitem" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteImagesFromInventoryItem", "noparams" => 1),
					"deleteinventoryitemchannelskus" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteInventoryItemChannelSKUs", "noparams" => 1),
					"deleteinventoryitemcompositions" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteInventoryItemCompositions", "noparams" => 1),
					"deleteinventoryitemdescriptions" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteInventoryItemDescriptions", "noparams" => 1),
					"deleteinventoryitemextendedproperties" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteInventoryItemExtendedProperties", "noparams" => 1),
					"deleteinventoryitemprices" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteInventoryItemPrices", "noparams" => 1),
					"deleteinventoryitempricingrules" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteInventoryItemPricingRules", "noparams" => 1),
					"deleteinventoryitems" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteInventoryItems", "noparams" => 1),
					"deleteinventoryitemtitles" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteInventoryItemTitles", "noparams" => 1),
					"deleteitemlocations" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteItemLocations", "noparams" => 1),
					"deleteproductidentifiers" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteProductIdentifiers", "noparams" => 1),
					"deletescrapcategories" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteScrapCategories", "noparams" => 1),
					"deletestocksupplierstat" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteStockSupplierStat", "noparams" => 1),
					"deletesuppliers" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteSuppliers", "noparams" => 1),
					"deleteuserspecificview" => array( "type" => "POST", "url" => "/Api/Inventory/DeleteUserSpecificView", "noparams" => 1),
					"duplicateinventoryitem" => array( "type" => "POST", "url" => "/Api/Inventory/DuplicateInventoryItem", "noparams" => 1),
					"getallextendedpropertynames" => array( "type" => "POST", "url" => "/Api/Inventory/GetAllExtendedPropertyNames", "noparams" => 1),
					"getbatchaudit" => array( "type" => "POST", "url" => "/Api/Inventory/GetBatchAudit", "noparams" => 1),
					"getbatchesbystockitemid" => array( "type" => "POST", "url" => "/Api/Inventory/GetBatchesByStockItemId", "noparams" => 1),
					"getbatchinventorybyid" => array( "type" => "POST", "url" => "/Api/Inventory/GetBatchInventoryById", "noparams" => 1),
					"getcategories" => array( "type" => "POST", "url" => "/Api/Inventory/GetCategories", "noparams" => 0),
					"getchannels" => array( "type" => "POST", "url" => "/Api/Inventory/GetChannels", "noparams" => 1),
					"getchannelsbysource" => array( "type" => "POST", "url" => "/Api/Inventory/GetChannelsBySource", "noparams" => 1),
					"getcountries" => array( "type" => "POST", "url" => "/Api/Inventory/GetCountries", "noparams" => 1),
					"getcountrycodes" => array( "type" => "POST", "url" => "/Api/Inventory/GetCountryCodes", "noparams" => 1),
					"getebaycompatibilitylist" => array( "type" => "POST", "url" => "/Api/Inventory/GetEbayCompatibilityList", "noparams" => 1),
					"getextendedpropertynames" => array( "type" => "POST", "url" => "/Api/Inventory/GetExtendedPropertyNames", "noparams" => 1),
					"getextendedpropertytypes" => array( "type" => "POST", "url" => "/Api/Inventory/GetExtendedPropertyTypes", "noparams" => 1),
					"getimagesinbulk" => array( "type" => "POST", "url" => "/Api/Inventory/GetImagesInBulk", "noparams" => 1),
					"getinventorybatchtypes" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryBatchTypes", "noparams" => 1),
					"getinventoryitemaudittrail" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryItemAuditTrail", "noparams" => 1),
					"getinventoryitembatchinformation" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryItemBatchInformation", "noparams" => 1),
					"getinventoryitembatchinformationbyids" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryItemBatchInformationByIds", "noparams" => 1),
					"getinventoryitembyid" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryItemById", "noparams" => 1),
					"getinventoryitemchannelskus" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryItemChannelSKUs", "noparams" => 1),
					"getinventoryitemchannelskuswithlocation" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryItemChannelSKUsWithLocation", "noparams" => 1),
					"getinventoryitemcompositions" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryItemCompositions", "noparams" => 1),
					"getinventoryitemdescriptions" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryItemDescriptions", "noparams" => 1),
					"getinventoryitemextendedproperties" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryItemExtendedProperties", "noparams" => 1),
					"getinventoryitemimages" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryItemImages", "noparams" => 1),
					"getinventoryitemlocations" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryItemLocations", "noparams" => 1),
					"getinventoryitempricechannelsuffixes" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryItemPriceChannelSuffixes", "noparams" => 1),
					"getinventoryitempricerulesbyid" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryItemPriceRulesById", "noparams" => 1),
					"getinventoryitempricerulesbysource" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryItemPriceRulesBySource", "noparams" => 1),
					"getinventoryitemprices" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryItemPrices", "noparams" => 1),
					"getinventoryitempricetags" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryItemPriceTags", "noparams" => 1),
					"getinventoryitemscompositionbyids" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryItemsCompositionByIds", "noparams" => 1),
					"getinventoryitemscount" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryItemsCount", "noparams" => 1),
					"getinventoryitemtitles" => array( "type" => "POST", "url" => "/Api/Inventory/GetInventoryItemTitles", "noparams" => 1),
					"getnewitemnumber" => array( "type" => "POST", "url" => "/Api/Inventory/GetNewItemNumber", "noparams" => 1),
					"getpackagegroups" => array( "type" => "POST", "url" => "/Api/Inventory/GetPackageGroups", "noparams" => 1),
					"getpostalservices" => array( "type" => "POST", "url" => "/Api/Inventory/GetPostalServices", "noparams" => 1),
					"getpredefinedviews" => array( "type" => "POST", "url" => "/Api/Inventory/GetPreDefinedViews", "noparams" => 1),
					"getproductidentifiersbulkbystockitemid" => array( "type" => "POST", "url" => "/Api/Inventory/GetProductIdentifiersBulkByStockItemId", "noparams" => 1),
					"getproductidentifiersbystockitemid" => array( "type" => "POST", "url" => "/Api/Inventory/GetProductIdentifiersByStockItemId", "noparams" => 1),
					"getproductidentifiertypes" => array( "type" => "POST", "url" => "/Api/Inventory/GetProductIdentifierTypes", "noparams" => 1),
					"getscrapcategories" => array( "type" => "POST", "url" => "/Api/Inventory/GetScrapCategories", "noparams" => 1),
					"getscraphistory" => array( "type" => "POST", "url" => "/Api/Inventory/GetScrapHistory", "noparams" => 1),
					"getstockitembatchesbylocation" => array( "type" => "POST", "url" => "/Api/Inventory/GetStockItemBatchesByLocation", "noparams" => 1),
					"getstockitemidsbysku" => array( "type" => "POST", "url" => "/Api/Inventory/GetStockItemIdsBySKU", "noparams" => 1),
					"getstockitemlabels" => array( "type" => "POST", "url" => "/Api/Inventory/GetStockItemLabels", "noparams" => 1),
					"getstocklocations" => array( "type" => "POST", "url" => "/Api/Inventory/GetStockLocations", "noparams" => 1),
					"getstocksupplierstat" => array( "type" => "POST", "url" => "/Api/Inventory/GetStockSupplierStat", "noparams" => 1),
					"getsupplierdetails" => array( "type" => "POST", "url" => "/Api/Inventory/GetSupplierDetails", "noparams" => 1),
					"getsuppliers" => array( "type" => "POST", "url" => "/Api/Inventory/GetSuppliers", "noparams" => 1),
					"getuserspecificviews" => array( "type" => "POST", "url" => "/Api/Inventory/GetUserSpecificViews", "noparams" => 1),
					"hasstockitembatches" => array( "type" => "POST", "url" => "/Api/Inventory/HasStockItemBatches", "noparams" => 1),
					"hasstockitemstocklevel" => array( "type" => "POST", "url" => "/Api/Inventory/HasStockItemStockLevel", "noparams" => 1),
					"insertupdateebaycompatibilitylist" => array( "type" => "POST", "url" => "/Api/Inventory/InsertUpdateEbayCompatibilityList", "noparams" => 1),
					"isinventoryitemchannelskulinked" => array( "type" => "POST", "url" => "/Api/Inventory/IsInventoryItemChannelSKULinked", "noparams" => 1),
					"isownedstocklocation" => array( "type" => "POST", "url" => "/Api/Inventory/IsOwnedStockLocation", "noparams" => 1),
					"scrapbatcheditem" => array( "type" => "POST", "url" => "/Api/Inventory/ScrapBatchedItem", "noparams" => 1),
					"setinventoryitemimageasmain" => array( "type" => "POST", "url" => "/Api/Inventory/SetInventoryItemImageAsMain", "noparams" => 1),
					"unarchiveinventoryitems" => array( "type" => "POST", "url" => "/Api/Inventory/UnarchiveInventoryItems", "noparams" => 1),
					"unlinkchannellisting" => array( "type" => "POST", "url" => "/Api/Inventory/UnlinkChannelListing", "noparams" => 1),
					"updatebatchdetails" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateBatchDetails", "noparams" => 1),
					"updatebatcheswithinventory" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateBatchesWithInventory", "noparams" => 1),
					"updatecategory" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateCategory", "noparams" => 1),
					"updatecompositeparentstocklevel" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateCompositeParentStockLevel", "noparams" => 1),
					"updatecountries" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateCountries", "noparams" => 1),
					"updatecountryregions" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateCountryRegions", "noparams" => 1),
					"updateimages" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateImages", "noparams" => 1),
					"updateinventoryitem" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateInventoryItem", "noparams" => 1),
					"updateinventoryitemchannelskus" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateInventoryItemChannelSKUs", "noparams" => 1),
					"updateinventoryitemchannelskuswithlocation" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateInventoryItemChannelSKUsWithLocation", "noparams" => 1),
					"updateinventoryitemcompositions" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateInventoryItemCompositions", "noparams" => 1),
					"updateinventoryitemdescriptions" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateInventoryItemDescriptions", "noparams" => 1),
					"updateinventoryitemextendedproperties" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateInventoryItemExtendedProperties", "noparams" => 1),
					"updateinventoryitemfield" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateInventoryItemField", "noparams" => 1),
					"updateinventoryitemlevels" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateInventoryItemLevels", "noparams" => 1),
					"updateinventoryitemlocationfield" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateInventoryItemLocationField", "noparams" => 1),
					"updateinventoryitemprices" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateInventoryItemPrices", "noparams" => 1),
					"updateinventoryitempricingrules" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateInventoryItemPricingRules", "noparams" => 1),
					"updateinventoryitemstockfield" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateInventoryItemStockField", "noparams" => 1),
					"updateinventoryitemtitles" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateInventoryItemTitles", "noparams" => 1),
					"updateitemlocations" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateItemLocations", "noparams" => 1),
					"updateproductidentifiers" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateProductIdentifiers", "noparams" => 1),
					"updatescrapcategories" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateScrapCategories", "noparams" => 1),
					"updatestocksupplierstat" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateStockSupplierStat", "noparams" => 1),
					"updatesupplier" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateSupplier", "noparams" => 1),
					"updateuserspecificview" => array( "type" => "POST", "url" => "/Api/Inventory/UpdateUserSpecificView", "noparams" => 1),
					"uploadimagestoinventoryitem" => array( "type" => "POST", "url" => "/Api/Inventory/UploadImagesToInventoryItem", "noparams" => 1),
					"endlistingspendingrelist" => array( "type" => "POST", "url" => "/Api/Listings/EndListingsPendingRelist", "noparams" => 1),
					"getebaylistingaudit" => array( "type" => "POST", "url" => "/Api/Listings/GetEbayListingAudit", "noparams" => 1),
					"setlistingstrikeoffstate" => array( "type" => "POST", "url" => "/Api/Listings/SetListingStrikeOffState", "noparams" => 1),
					"addlocation" => array( "type" => "POST", "url" => "/Api/Locations/AddLocation", "noparams" => 1),
					"deletelocation" => array( "type" => "POST", "url" => "/Api/Locations/DeleteLocation", "noparams" => 1),
					"deletewarehousetote" => array( "type" => "POST", "url" => "/Api/Locations/DeleteWarehouseTOTE", "noparams" => 1),
					"getlocation" => array( "type" => "POST", "url" => "/Api/Locations/GetLocation", "noparams" => 1),
					"getwarehousetotes" => array( "type" => "POST", "url" => "/Api/Locations/GetWarehouseTOTEs", "noparams" => 1),
					"updatelocation" => array( "type" => "POST", "url" => "/Api/Locations/UpdateLocation", "noparams" => 1),
					"getinstalledmacros" => array( "type" => "POST", "url" => "/Api/Macro/GetInstalledMacros", "noparams" => 1),
					"getmacroconfigurations" => array( "type" => "POST", "url" => "/Api/Macro/GetMacroConfigurations", "noparams" => 1),
					"addcoupon" => array( "type" => "POST", "url" => "/Api/Orders/AddCoupon", "noparams" => 1),
					"addorderitem" => array( "type" => "POST", "url" => "/Api/Orders/AddOrderItem", "noparams" => 1),
					"addorderservice" => array( "type" => "POST", "url" => "/Api/Orders/AddOrderService", "noparams" => 1),
					"assignorderitembatches" => array( "type" => "POST", "url" => "/Api/Orders/AssignOrderItemBatches", "noparams" => 1),
					"assignstocktoorder" => array( "type" => "POST", "url" => "/Api/Orders/AssignStockToOrder", "noparams" => 1),
					"assigntofolder" => array( "type" => "POST", "url" => "/Api/Orders/AssignToFolder", "noparams" => 1),
					"cancelorder" => array( "type" => "POST", "url" => "/Api/Orders/CancelOrder", "noparams" => 1),
					"changeordertag" => array( "type" => "POST", "url" => "/Api/Orders/ChangeOrderTag", "noparams" => 1),
					"changeshippingmethod" => array( "type" => "POST", "url" => "/Api/Orders/ChangeShippingMethod", "noparams" => 1),
					"changestatus" => array( "type" => "POST", "url" => "/Api/Orders/ChangeStatus", "noparams" => 1),
					"clearinvoiceprinted" => array( "type" => "POST", "url" => "/Api/Orders/ClearInvoicePrinted", "noparams" => 1),
					"clearpicklistprinted" => array( "type" => "POST", "url" => "/Api/Orders/ClearPickListPrinted", "noparams" => 1),
					"clearshippinglabelinfo" => array( "type" => "POST", "url" => "/Api/Orders/ClearShippingLabelInfo", "noparams" => 1),
					"completeorder" => array( "type" => "POST", "url" => "/Api/Orders/CompleteOrder", "noparams" => 1),
					"createnewitemandlink" => array( "type" => "POST", "url" => "/Api/Orders/CreateNewItemAndLink", "noparams" => 1),
					"createneworder" => array( "type" => "POST", "url" => "/Api/Orders/CreateNewOrder", "noparams" => 1),
					"createorders" => array( "type" => "POST", "url" => "/Api/Orders/CreateOrders", "noparams" => 1),
					"customerlookup" => array( "type" => "POST", "url" => "/Api/Orders/CustomerLookUp", "noparams" => 1),
					"deleteorder" => array( "type" => "POST", "url" => "/Api/Orders/DeleteOrder", "noparams" => 1),
					"get_openorderbasicinfofromitems" => array( "type" => "POST", "url" => "/Api/Orders/Get_OpenOrderBasicInfoFromItems", "noparams" => 1),
					"getallavailableorderitembatchsbyorderid" => array( "type" => "POST", "url" => "/Api/Orders/GetAllAvailableOrderItemBatchsByOrderId", "noparams" => 1),
					"getallopenorders" => array( "type" => "POST", "url" => "/Api/Orders/GetAllOpenOrders", "noparams" => 1),
					"getallopenordersbetweenindex" => array( "type" => "POST", "url" => "/Api/Orders/GetAllOpenOrdersBetweenIndex", "noparams" => 1),
					"getassignedorderitembatches" => array( "type" => "POST", "url" => "/Api/Orders/GetAssignedOrderItemBatches", "noparams" => 1),
					"getavailablefolders" => array( "type" => "POST", "url" => "/Api/Orders/GetAvailableFolders", "noparams" => 1),
					"getbatchpilots" => array( "type" => "POST", "url" => "/Api/Orders/GetBatchPilots", "noparams" => 1),
					"getcountries" => array( "type" => "POST", "url" => "/Api/Orders/GetCountries", "noparams" => 1),
					"getdefaultpaymentmethodidforneworder" => array( "type" => "POST", "url" => "/Api/Orders/GetDefaultPaymentMethodIdForNewOrder", "noparams" => 1),
					"getdraftorders" => array( "type" => "POST", "url" => "/Api/Orders/GetDraftOrders", "noparams" => 1),
					"getextendedproperties" => array( "type" => "POST", "url" => "/Api/Orders/GetExtendedProperties", "noparams" => 1),
					"getextendedpropertynames" => array( "type" => "POST", "url" => "/Api/Orders/GetExtendedPropertyNames", "noparams" => 1),
					"getextendedpropertytypes" => array( "type" => "POST", "url" => "/Api/Orders/GetExtendedPropertyTypes", "noparams" => 1),
					"getlinkeditems" => array( "type" => "POST", "url" => "/Api/Orders/GetLinkedItems", "noparams" => 1),
					"getopenorderidbyorderorreferenceid" => array( "type" => "POST", "url" => "/Api/Orders/GetOpenOrderIdByOrderOrReferenceId", "noparams" => 1),
					"getopenorderitemssuppliers" => array( "type" => "POST", "url" => "/Api/Orders/GetOpenOrderItemsSuppliers", "noparams" => 1),
					"getopenorders" => array( "type" => "POST", "url" => "/Api/Orders/GetOpenOrders", "noparams" => 1),
					"getopenordersbyitembarcode" => array( "type" => "POST", "url" => "/Api/Orders/GetOpenOrdersByItemBarcode", "noparams" => 1),
					"getorder" => array( "type" => "POST", "url" => "/Api/Orders/GetOrder", "noparams" => 1),
					"getorderaudittrail" => array( "type" => "POST", "url" => "/Api/Orders/GetOrderAuditTrail", "noparams" => 1),
					"getorderaudittrailsbyids" => array( "type" => "POST", "url" => "/Api/Orders/GetOrderAuditTrailsByIds", "noparams" => 1),
					"getorderbyid" => array( "type" => "POST", "url" => "/Api/Orders/GetOrderById", "noparams" => 1),
					"getorderdetailsbynumorderid" => array( "type" => "POST", "url" => "/Api/Orders/GetOrderDetailsByNumOrderId", "noparams" => 1),
					"getorderdetailsbyreferenceid" => array( "type" => "POST", "url" => "/Api/Orders/GetOrderDetailsByReferenceId", "noparams" => 1),
					"getorderitembatchesbyorderids" => array( "type" => "POST", "url" => "/Api/Orders/GetOrderItemBatchesByOrderIds", "noparams" => 1),
					"getorderitembatchsbyorderid" => array( "type" => "POST", "url" => "/Api/Orders/GetOrderItemBatchsByOrderId", "noparams" => 1),
					"getorderitemcomposition" => array( "type" => "POST", "url" => "/Api/Orders/GetOrderItemComposition", "noparams" => 1),
					"getorderitems" => array( "type" => "POST", "url" => "/Api/Orders/GetOrderItems", "noparams" => 1),
					"getordernotes" => array( "type" => "POST", "url" => "/Api/Orders/GetOrderNotes", "noparams" => 1),
					"getordernotetypes" => array( "type" => "POST", "url" => "/Api/Orders/GetOrderNoteTypes", "noparams" => 1),
					"getorderpackagingcalculation" => array( "type" => "POST", "url" => "/Api/Orders/GetOrderPackagingCalculation", "noparams" => 1),
					"getorderpackagingsplit" => array( "type" => "POST", "url" => "/Api/Orders/GetOrderPackagingSplit", "noparams" => 1),
					"getorderrelations" => array( "type" => "POST", "url" => "/Api/Orders/GetOrderRelations", "noparams" => 1),
					"getorders" => array( "type" => "POST", "url" => "/Api/Orders/GetOrders", "noparams" => 1),
					"getordersbyid" => array( "type" => "POST", "url" => "/Api/Orders/GetOrdersById", "noparams" => 1),
					"getorderview" => array( "type" => "POST", "url" => "/Api/Orders/GetOrderView", "noparams" => 1),
					"getorderviews" => array( "type" => "POST", "url" => "/Api/Orders/GetOrderViews", "noparams" => 1),
					"getorderxml" => array( "type" => "POST", "url" => "/Api/Orders/GetOrderXml", "noparams" => 1),
					"getorderxmljstree" => array( "type" => "POST", "url" => "/Api/Orders/GetOrderXmlJSTree", "noparams" => 1),
					"getpackaginggroups" => array( "type" => "POST", "url" => "/Api/Orders/GetPackagingGroups", "noparams" => 1),
					"getpaymentmethods" => array( "type" => "POST", "url" => "/Api/Orders/GetPaymentMethods", "noparams" => 1),
					"getshippingmethods" => array( "type" => "POST", "url" => "/Api/Orders/GetShippingMethods", "noparams" => 1),
					"getuserlocationid" => array( "type" => "POST", "url" => "/Api/Orders/GetUserLocationId", "noparams" => 1),
					"lockorder" => array( "type" => "POST", "url" => "/Api/Orders/LockOrder", "noparams" => 1),
					"mergeorders" => array( "type" => "POST", "url" => "/Api/Orders/MergeOrders", "noparams" => 1),
					"movetofulfilmentcenter" => array( "type" => "POST", "url" => "/Api/Orders/MoveToFulfilmentCenter", "noparams" => 1),
					"movetolocation" => array( "type" => "POST", "url" => "/Api/Orders/MoveToLocation", "noparams" => 1),
					"processfulfilmentcentreorder" => array( "type" => "POST", "url" => "/Api/Orders/ProcessFulfilmentCentreOrder", "noparams" => 1),
					"processorder" => array( "type" => "POST", "url" => "/Api/Orders/ProcessOrder", "noparams" => 1),
					"processorder_requiredbatchscans" => array( "type" => "POST", "url" => "/Api/Orders/ProcessOrder_RequiredBatchScans", "noparams" => 1),
					"processorderbyorderorreferenceid" => array( "type" => "POST", "url" => "/Api/Orders/ProcessOrderByOrderOrReferenceId", "noparams" => 1),
					"processordersinbatch" => array( "type" => "POST", "url" => "/Api/Orders/ProcessOrdersInBatch", "noparams" => 1),
					"recalculatesingleorderpackaging" => array( "type" => "POST", "url" => "/Api/Orders/RecalculateSingleOrderPackaging", "noparams" => 1),
					"removeorderitem" => array( "type" => "POST", "url" => "/Api/Orders/RemoveOrderItem", "noparams" => 1),
					"runrulesengine" => array( "type" => "POST", "url" => "/Api/Orders/RunRulesEngine", "noparams" => 1),
					"saveorderview" => array( "type" => "POST", "url" => "/Api/Orders/SaveOrderView", "noparams" => 1),
					"setadditionalinfo" => array( "type" => "POST", "url" => "/Api/Orders/SetAdditionalInfo", "noparams" => 1),
					"setavailablefolders" => array( "type" => "POST", "url" => "/Api/Orders/SetAvailableFolders", "noparams" => 1),
					"setdefaultpaymentmethodidforneworder" => array( "type" => "POST", "url" => "/Api/Orders/SetDefaultPaymentMethodIdForNewOrder", "noparams" => 1),
					"setextendedproperties" => array( "type" => "POST", "url" => "/Api/Orders/SetExtendedProperties", "noparams" => 1),
					"setinvoicesprinted" => array( "type" => "POST", "url" => "/Api/Orders/SetInvoicesPrinted", "noparams" => 1),
					"setlabelsprinted" => array( "type" => "POST", "url" => "/Api/Orders/SetLabelsPrinted", "noparams" => 1),
					"setordercustomerinfo" => array( "type" => "POST", "url" => "/Api/Orders/SetOrderCustomerInfo", "noparams" => 1),
					"setordergeneralinfo" => array( "type" => "POST", "url" => "/Api/Orders/SetOrderGeneralInfo", "noparams" => 1),
					"setordernotes" => array( "type" => "POST", "url" => "/Api/Orders/SetOrderNotes", "noparams" => 1),
					"setorderpackaging" => array( "type" => "POST", "url" => "/Api/Orders/SetOrderPackaging", "noparams" => 1),
					"setorderpackagingsplit" => array( "type" => "POST", "url" => "/Api/Orders/SetOrderPackagingSplit", "noparams" => 1),
					"setordershippinginfo" => array( "type" => "POST", "url" => "/Api/Orders/SetOrderShippingInfo", "noparams" => 1),
					"setordersplitpackagingmanualoverwrite" => array( "type" => "POST", "url" => "/Api/Orders/SetOrderSplitPackagingManualOverwrite", "noparams" => 1),
					"setordertotalsinfo" => array( "type" => "POST", "url" => "/Api/Orders/SetOrderTotalsInfo", "noparams" => 1),
					"setpaymentmethods" => array( "type" => "POST", "url" => "/Api/Orders/SetPaymentMethods", "noparams" => 1),
					"setpicklistprinted" => array( "type" => "POST", "url" => "/Api/Orders/SetPickListPrinted", "noparams" => 1),
					"splitorder" => array( "type" => "POST", "url" => "/Api/Orders/SplitOrder", "noparams" => 1),
					"unassigntofolder" => array( "type" => "POST", "url" => "/Api/Orders/UnassignToFolder", "noparams" => 1),
					"updateadditionalinfo" => array( "type" => "POST", "url" => "/Api/Orders/UpdateAdditionalInfo", "noparams" => 1),
					"updatebillingaddress" => array( "type" => "POST", "url" => "/Api/Orders/UpdateBillingAddress", "noparams" => 1),
					"updatelinkitem" => array( "type" => "POST", "url" => "/Api/Orders/UpdateLinkItem", "noparams" => 1),
					"updateorderitem" => array( "type" => "POST", "url" => "/Api/Orders/UpdateOrderItem", "noparams" => 1),
					"validatecoupon" => array( "type" => "POST", "url" => "/Api/Orders/ValidateCoupon", "noparams" => 1),
					"checkinuser" => array( "type" => "POST", "url" => "/Api/OrderWorkflow/CheckinUser", "noparams" => 1),
					"deallocateorderfromjob" => array( "type" => "POST", "url" => "/Api/OrderWorkflow/DeallocateOrderFromJob", "noparams" => 1),
					"getgroup" => array( "type" => "POST", "url" => "/Api/OrderWorkflow/GetGroup", "noparams" => 1),
					"getgrouplist" => array( "type" => "POST", "url" => "/Api/OrderWorkflow/GetGroupList", "noparams" => 1),
					"getjob" => array( "type" => "POST", "url" => "/Api/OrderWorkflow/GetJob", "noparams" => 1),
					"getjobaudit" => array( "type" => "POST", "url" => "/Api/OrderWorkflow/GetJobAudit", "noparams" => 1),
					"getjobbyname" => array( "type" => "POST", "url" => "/Api/OrderWorkflow/GetJobByName", "noparams" => 1),
					"getjoberrors" => array( "type" => "POST", "url" => "/Api/OrderWorkflow/GetJobErrors", "noparams" => 1),
					"getprintattachment" => array( "type" => "POST", "url" => "/Api/OrderWorkflow/GetPrintAttachment", "noparams" => 1),
					"getworkflow" => array( "type" => "POST", "url" => "/Api/OrderWorkflow/GetWorkflow", "noparams" => 1),
					"run" => array( "type" => "POST", "url" => "/Api/OrderWorkflow/Run", "noparams" => 1),
					"updategroup" => array( "type" => "POST", "url" => "/Api/OrderWorkflow/UpdateGroup", "noparams" => 1),
					"checkallocatabletopickwave" => array( "type" => "POST", "url" => "/Api/Picking/CheckAllocatableToPickwave", "noparams" => 1),
					"deleteordersfrompickingwaves" => array( "type" => "POST", "url" => "/Api/Picking/DeleteOrdersFromPickingWaves", "noparams" => 1),
					"generatepickingwave" => array( "type" => "POST", "url" => "/Api/Picking/GeneratePickingWave", "noparams" => 1),
					"getallpickingwaveheaders" => array( "type" => "POST", "url" => "/Api/Picking/GetAllPickingWaveHeaders", "noparams" => 1),
					"getallpickingwaves" => array( "type" => "POST", "url" => "/Api/Picking/GetAllPickingWaves", "noparams" => 1),
					"getitembinracks" => array( "type" => "POST", "url" => "/Api/Picking/GetItemBinracks", "noparams" => 1),
					"getmypickingwaveheaders" => array( "type" => "POST", "url" => "/Api/Picking/GetMyPickingWaveHeaders", "noparams" => 1),
					"getmypickingwaves" => array( "type" => "POST", "url" => "/Api/Picking/GetMyPickingWaves", "noparams" => 1),
					"getpickingwave" => array( "type" => "POST", "url" => "/Api/Picking/GetPickingWave", "noparams" => 1),
					"getpickwaveuserswithsummary" => array( "type" => "POST", "url" => "/Api/Picking/GetPickwaveUsersWithSummary", "noparams" => 1),
					"updatepickeditemdelta" => array( "type" => "POST", "url" => "/Api/Picking/UpdatePickedItemDelta", "noparams" => 1),
					"updatepickingwaveheader" => array( "type" => "POST", "url" => "/Api/Picking/UpdatePickingWaveHeader", "noparams" => 1),
					"updatepickingwaveitem" => array( "type" => "POST", "url" => "/Api/Picking/UpdatePickingWaveItem", "noparams" => 1),
					"updatepickingwaveitemwithnewbinrack" => array( "type" => "POST", "url" => "/Api/Picking/UpdatePickingWaveItemWithNewBinrack", "noparams" => 1),
					"createpostalservice" => array( "type" => "POST", "url" => "/Api/PostalServices/CreatePostalService", "noparams" => 1),
					"deletepostalservice" => array( "type" => "POST", "url" => "/Api/PostalServices/DeletePostalService", "noparams" => 1),
					"getchannellinks" => array( "type" => "POST", "url" => "/Api/PostalServices/GetChannelLinks", "noparams" => 1),
					"getpostalservices" => array( "type" => "POST", "url" => "/Api/PostalServices/GetPostalServices", "noparams" => 1),
					"updatepostalservice" => array( "type" => "POST", "url" => "/Api/PostalServices/UpdatePostalService", "noparams" => 1),
					"createcancellation" => array( "type" => "POST", "url" => "/Api/PostSale/CreateCancellation", "noparams" => 1),
					"createpdffromjobforcetemplate" => array( "type" => "POST", "url" => "/Api/PrintService/CreatePDFfromJobForceTemplate", "noparams" => 1),
					"createpdffromjobforcetemplatestockin" => array( "type" => "POST", "url" => "/Api/PrintService/CreatePDFfromJobForceTemplateStockIn", "noparams" => 1),
					"createpdffromjobforcetemplatewithquantities" => array( "type" => "POST", "url" => "/Api/PrintService/CreatePDFfromJobForceTemplateWithQuantities", "noparams" => 1),
					"createreturnshippinglabelspdf" => array( "type" => "POST", "url" => "/Api/PrintService/CreateReturnShippingLabelsPDF", "noparams" => 1),
					"createreturnshippinglabelspdfwithskus" => array( "type" => "POST", "url" => "/Api/PrintService/CreateReturnShippingLabelsPDFWithSKUs", "noparams" => 1),
					"gettemplatelist" => array( "type" => "POST", "url" => "/Api/PrintService/GetTemplateList", "noparams" => 1),
					"getusersforprinterconfig" => array( "type" => "POST", "url" => "/Api/PrintService/GetUsersForPrinterConfig", "noparams" => 1),
					"printtemplatepreview" => array( "type" => "POST", "url" => "/Api/PrintService/PrintTemplatePreview", "noparams" => 1),
					"vp_getprinters" => array( "type" => "POST", "url" => "/Api/PrintService/VP_GetPrinters", "noparams" => 1),
					"addordernote" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/AddOrderNote", "noparams" => 1),
					"addreturncategory" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/AddReturnCategory", "noparams" => 1),
					"changeordernote" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/ChangeOrderNote", "noparams" => 1),
					"checkorderfullyreturned" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/CheckOrderFullyReturned", "noparams" => 1),
					"createexchange" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/CreateExchange", "noparams" => 1),
					"createfullresend" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/CreateFullResend", "noparams" => 1),
					"createresend" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/CreateResend", "noparams" => 1),
					"createreturn" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/CreateReturn", "noparams" => 1),
					"deleteordernote" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/DeleteOrderNote", "noparams" => 1),
					"deletereturncategory" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/DeleteReturnCategory", "noparams" => 1),
					"downloadorderstocsv" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/DownloadOrdersToCSV", "noparams" => 1),
					"getchannelrefundreasons" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/GetChannelRefundReasons", "noparams" => 1),
					"getorderinfo" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/GetOrderInfo", "noparams" => 1),
					"getpackagesplit" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/GetPackageSplit", "noparams" => 1),
					"getprocessedaudittrail" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/GetProcessedAuditTrail", "noparams" => 1),
					"getprocessedorderextendedproperties" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/GetProcessedOrderExtendedProperties", "noparams" => 1),
					"getprocessedordernotes" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/GetProcessedOrderNotes", "noparams" => 1),
					"getprocessedrelatives" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/GetProcessedRelatives", "noparams" => 1),
					"getrefundableserviceitems" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/GetRefundableServiceItems", "noparams" => 1),
					"getrefunds" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/GetRefunds", "noparams" => 1),
					"getrefundsoptions" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/GetRefundsOptions", "noparams" => 1),
					"getreturncategories" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/GetReturnCategories", "noparams" => 1),
					"getreturnitemsinfo" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/GetReturnItemsInfo", "noparams" => 1),
					"getreturnorderinfo" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/GetReturnOrderInfo", "noparams" => 1),
					"getreturnsexchanges" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/GetReturnsExchanges", "noparams" => 1),
					"gettotalrefunds" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/GetTotalRefunds", "noparams" => 1),
					"isrefundvalid" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/IsRefundValid", "noparams" => 1),
					"isrefundvalidationrequiredbyorderid" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/IsRefundValidationRequiredByOrderId", "noparams" => 1),
					"markmanualrefundsasactioned" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/MarkManualRefundsAsActioned", "noparams" => 1),
					"refundfreetext" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/RefundFreeText", "noparams" => 1),
					"refundservices" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/RefundServices", "noparams" => 1),
					"refundshipping" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/RefundShipping", "noparams" => 1),
					"renamereturncategory" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/RenameReturnCategory", "noparams" => 1),
					"searchprocessedorders" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/SearchProcessedOrders", "noparams" => 1),
					"searchprocessedorderspaged" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/SearchProcessedOrdersPaged", "noparams" => 1),
					"validatecompleteorderrefund" => array( "type" => "POST", "url" => "/Api/ProcessedOrders/ValidateCompleteOrderRefund", "noparams" => 1),
					"add_additionalcosttypes" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Add_AdditionalCostTypes", "noparams" => 1),
					"add_purchaseorderextendedproperty" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Add_PurchaseOrderExtendedProperty", "noparams" => 1),
					"add_purchaseorderitem" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Add_PurchaseOrderItem", "noparams" => 1),
					"add_purchaseordernote" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Add_PurchaseOrderNote", "noparams" => 1),
					"change_purchaseorderstatus" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Change_PurchaseOrderStatus", "noparams" => 1),
					"create_purchaseorder_initial" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Create_PurchaseOrder_Initial", "noparams" => 1),
					"delete_additionalcosttypes" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Delete_AdditionalCostTypes", "noparams" => 1),
					"delete_purchaseorder" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Delete_PurchaseOrder", "noparams" => 1),
					"delete_purchaseorderextendedproperty" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Delete_PurchaseOrderExtendedProperty", "noparams" => 1),
					"delete_purchaseorderitem" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Delete_PurchaseOrderItem", "noparams" => 1),
					"delete_purchaseordernote" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Delete_PurchaseOrderNote", "noparams" => 1),
					"deliver_purchaseitem" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Deliver_PurchaseItem", "noparams" => 1),
					"deliver_purchaseitemall" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Deliver_PurchaseItemAll", "noparams" => 1),
					"deliver_purchaseitemall_exceptbatchitems" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Deliver_PurchaseItemAll_ExceptBatchItems", "noparams" => 1),
					"deliver_purchaseitems_withquantity" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Deliver_PurchaseItems_WithQuantity", "noparams" => 1),
					"findstockitem" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/FindStockItem", "noparams" => 1),
					"get_additional_cost" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Get_Additional_Cost", "noparams" => 1),
					"get_additionalcosttypes" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Get_AdditionalCostTypes", "noparams" => 1),
					"get_deliveredrecords" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Get_DeliveredRecords", "noparams" => 1),
					"get_emailcsvfile" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Get_EmailCSVFile", "noparams" => 1),
					"get_emailssent" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Get_EmailsSent", "noparams" => 1),
					"get_payment_statement" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Get_Payment_Statement", "noparams" => 1),
					"get_purchaseorder" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Get_PurchaseOrder", "noparams" => 1),
					"get_purchaseorderaudit" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Get_PurchaseOrderAudit", "noparams" => 1),
					"get_purchaseorderextendedproperty" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Get_PurchaseOrderExtendedProperty", "noparams" => 1),
					"get_purchaseorderitem_openorders" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Get_PurchaseOrderItem_OpenOrders", "noparams" => 1),
					"get_purchaseordernote" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Get_PurchaseOrderNote", "noparams" => 1),
					"getpurchaseorderstatuslist" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/GetPurchaseOrderStatusList", "noparams" => 1),
					"getpurchaseorderswithstockitems" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/GetPurchaseOrdersWithStockItems", "noparams" => 1),
					"modify_additionalcost" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Modify_AdditionalCost", "noparams" => 1),
					"modify_additionalcostallocation" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Modify_AdditionalCostAllocation", "noparams" => 1),
					"modify_paymentstatement" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Modify_PaymentStatement", "noparams" => 1),
					"modify_purchaseorderitems_bulk" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Modify_PurchaseOrderItems_Bulk", "noparams" => 1),
					"search_purchaseorders" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Search_PurchaseOrders", "noparams" => 1),
					"update_additionalcosttypes" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Update_AdditionalCostTypes", "noparams" => 1),
					"update_purchaseorderextendedproperty" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Update_PurchaseOrderExtendedProperty", "noparams" => 1),
					"update_purchaseorderheader" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Update_PurchaseOrderHeader", "noparams" => 1),
					"update_purchaseorderitem" => array( "type" => "POST", "url" => "/Api/PurchaseOrder/Update_PurchaseOrderItem", "noparams" => 1),
					"acknowledgerefunderrors" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/AcknowledgeRefundErrors", "noparams" => 1),
					"acknowledgermaerrors" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/AcknowledgeRMAErrors", "noparams" => 1),
					"actionbookedorder" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/ActionBookedOrder", "noparams" => 1),
					"actionrefund" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/ActionRefund", "noparams" => 1),
					"actionrmabooking" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/ActionRMABooking", "noparams" => 1),
					"createrefund" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/CreateRefund", "noparams" => 1),
					"createreturnsrefundscsv" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/CreateReturnsRefundsCSV", "noparams" => 1),
					"creatermabooking" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/CreateRMABooking", "noparams" => 1),
					"deletebookeditem" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/DeleteBookedItem", "noparams" => 1),
					"deletebookedorder" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/DeleteBookedOrder", "noparams" => 1),
					"deletependingrefunditem" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/DeletePendingRefundItem", "noparams" => 1),
					"deleterefund" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/DeleteRefund", "noparams" => 1),
					"deleterma" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/DeleteRMA", "noparams" => 1),
					"editbookediteminfo" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/EditBookedItemInfo", "noparams" => 1),
					"getactionablerefundheaders" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/GetActionableRefundHeaders", "noparams" => 1),
					"getactionablermaheaders" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/GetActionableRMAHeaders", "noparams" => 1),
					"getbookedreturnsexchangeorders" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/GetBookedReturnsExchangeOrders", "noparams" => 1),
					"getprocessedorackederrorrefundheaders" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/GetProcessedOrAckedErrorRefundHeaders", "noparams" => 1),
					"getprocessedorackederrorrmaheaders" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/GetProcessedOrAckedErrorRMAHeaders", "noparams" => 1),
					"getrefundheadersbyorderid" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/GetRefundHeadersByOrderId", "noparams" => 1),
					"getrefundlinesbyheaderid" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/GetRefundLinesByHeaderId", "noparams" => 1),
					"getrefundoptions" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/GetRefundOptions", "noparams" => 1),
					"getrefundorders" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/GetRefundOrders", "noparams" => 1),
					"getreturnoptions" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/GetReturnOptions", "noparams" => 1),
					"getrmaheadersbyorderid" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/GetRMAHeadersByOrderId", "noparams" => 1),
					"getsearchtypes" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/GetSearchTypes", "noparams" => 1),
					"gettotalrefunds" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/GetTotalRefunds", "noparams" => 1),
					"getwarehouselocations" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/GetWarehouseLocations", "noparams" => 1),
					"refundorder" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/RefundOrder", "noparams" => 1),
					"searchreturnsrefundspaged" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/SearchReturnsRefundsPaged", "noparams" => 1),
					"updaterefund" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/UpdateRefund", "noparams" => 1),
					"updatermabooking" => array( "type" => "POST", "url" => "/Api/ReturnsRefunds/UpdateRMABooking", "noparams" => 1),
					"addaction" => array( "type" => "POST", "url" => "/Api/RulesEngine/AddAction", "noparams" => 1),
					"checkconditionnameexists" => array( "type" => "POST", "url" => "/Api/RulesEngine/CheckConditionNameExists", "noparams" => 1),
					"copyaction" => array( "type" => "POST", "url" => "/Api/RulesEngine/CopyAction", "noparams" => 1),
					"copycondition" => array( "type" => "POST", "url" => "/Api/RulesEngine/CopyCondition", "noparams" => 1),
					"createdraftfromexisting" => array( "type" => "POST", "url" => "/Api/RulesEngine/CreateDraftFromExisting", "noparams" => 1),
					"createnewcondition" => array( "type" => "POST", "url" => "/Api/RulesEngine/CreateNewCondition", "noparams" => 1),
					"createnewdraft" => array( "type" => "POST", "url" => "/Api/RulesEngine/CreateNewDraft", "noparams" => 1),
					"createnewdraftfromexisting" => array( "type" => "POST", "url" => "/Api/RulesEngine/CreateNewDraftFromExisting", "noparams" => 1),
					"deleteaction" => array( "type" => "POST", "url" => "/Api/RulesEngine/DeleteAction", "noparams" => 1),
					"deletecondition" => array( "type" => "POST", "url" => "/Api/RulesEngine/DeleteCondition", "noparams" => 1),
					"deleterulebyid" => array( "type" => "POST", "url" => "/Api/RulesEngine/DeleteRuleById", "noparams" => 1),
					"getactionoptions" => array( "type" => "POST", "url" => "/Api/RulesEngine/GetActionOptions", "noparams" => 1),
					"getactiontypes" => array( "type" => "POST", "url" => "/Api/RulesEngine/GetActionTypes", "noparams" => 1),
					"getconditionweb" => array( "type" => "POST", "url" => "/Api/RulesEngine/GetConditionWeb", "noparams" => 1),
					"getevaluationfields" => array( "type" => "POST", "url" => "/Api/RulesEngine/GetEvaluationFields", "noparams" => 1),
					"getevaluatortypes" => array( "type" => "POST", "url" => "/Api/RulesEngine/GetEvaluatorTypes", "noparams" => 1),
					"getkeyoptions" => array( "type" => "POST", "url" => "/Api/RulesEngine/GetKeyOptions", "noparams" => 1),
					"getmultikeyoptions" => array( "type" => "POST", "url" => "/Api/RulesEngine/GetMultiKeyOptions", "noparams" => 1),
					"getmultioptions" => array( "type" => "POST", "url" => "/Api/RulesEngine/GetMultiOptions", "noparams" => 1),
					"getoptions" => array( "type" => "POST", "url" => "/Api/RulesEngine/GetOptions", "noparams" => 1),
					"getrequiredfieldsbyruleid" => array( "type" => "POST", "url" => "/Api/RulesEngine/GetRequiredFieldsByRuleId", "noparams" => 1),
					"getrequiredfieldsbytype" => array( "type" => "POST", "url" => "/Api/RulesEngine/GetRequiredFieldsByType", "noparams" => 1),
					"getruleconditionnodes" => array( "type" => "POST", "url" => "/Api/RulesEngine/GetRuleConditionNodes", "noparams" => 1),
					"getrules" => array( "type" => "POST", "url" => "/Api/RulesEngine/GetRules", "noparams" => 1),
					"getrulesbytype" => array( "type" => "POST", "url" => "/Api/RulesEngine/GetRulesByType", "noparams" => 1),
					"getvaluesfromexisting" => array( "type" => "POST", "url" => "/Api/RulesEngine/GetValuesFromExisting", "noparams" => 1),
					"saveconditionchanges" => array( "type" => "POST", "url" => "/Api/RulesEngine/SaveConditionChanges", "noparams" => 1),
					"setconditionenabled" => array( "type" => "POST", "url" => "/Api/RulesEngine/SetConditionEnabled", "noparams" => 1),
					"setdraftlive" => array( "type" => "POST", "url" => "/Api/RulesEngine/SetDraftLive", "noparams" => 1),
					"setruleenabled" => array( "type" => "POST", "url" => "/Api/RulesEngine/SetRuleEnabled", "noparams" => 1),
					"setrulename" => array( "type" => "POST", "url" => "/Api/RulesEngine/SetRuleName", "noparams" => 1),
					"swapconditions" => array( "type" => "POST", "url" => "/Api/RulesEngine/SwapConditions", "noparams" => 1),
					"swaprules" => array( "type" => "POST", "url" => "/Api/RulesEngine/SwapRules", "noparams" => 1),
					"testevaluaterule" => array( "type" => "POST", "url" => "/Api/RulesEngine/TestEvaluateRule", "noparams" => 1),
					"updateaction" => array( "type" => "POST", "url" => "/Api/RulesEngine/UpdateAction", "noparams" => 1),
					"deletecurrencyconversionrates" => array( "type" => "POST", "url" => "/Api/Settings/DeleteCurrencyConversionRates", "noparams" => 1),
					"getavailabletimezones" => array( "type" => "POST", "url" => "/Api/Settings/GetAvailableTimeZones", "noparams" => 1),
					"getcurrencyconversionrates" => array( "type" => "POST", "url" => "/Api/Settings/GetCurrencyConversionRates", "noparams" => 1),
					"getlatestcurrencyrate" => array( "type" => "POST", "url" => "/Api/Settings/GetLatestCurrencyRate", "noparams" => 1),
					"getmeasures" => array( "type" => "POST", "url" => "/Api/Settings/GetMeasures", "noparams" => 1),
					"insertcurrencyconversionrates" => array( "type" => "POST", "url" => "/Api/Settings/InsertCurrencyConversionRates", "noparams" => 1),
					"updatecurrencyconversionrates" => array( "type" => "POST", "url" => "/Api/Settings/UpdateCurrencyConversionRates", "noparams" => 1),
					"addrollingstocktake" => array( "type" => "POST", "url" => "/Api/Stock/AddRollingStockTake", "noparams" => 1),
					"addvariationitems" => array( "type" => "POST", "url" => "/Api/Stock/AddVariationItems", "noparams" => 1),
					"batchstockleveldelta" => array( "type" => "POST", "url" => "/Api/Stock/BatchStockLevelDelta", "noparams" => 1),
					"bookinstockbatch" => array( "type" => "POST", "url" => "/Api/Stock/BookInStockBatch", "noparams" => 1),
					"bookinstockitem" => array( "type" => "POST", "url" => "/Api/Stock/BookInStockItem", "noparams" => 1),
					"checkvariationparentskuexists" => array( "type" => "POST", "url" => "/Api/Stock/CheckVariationParentSKUExists", "noparams" => 1),
					"createstockbatches" => array( "type" => "POST", "url" => "/Api/Stock/CreateStockBatches", "noparams" => 1),
					"createvariationgroup" => array( "type" => "POST", "url" => "/Api/Stock/CreateVariationGroup", "noparams" => 1),
					"createwarehousemove" => array( "type" => "POST", "url" => "/Api/Stock/CreateWarehouseMove", "noparams" => 1),
					"deletevariationgroup" => array( "type" => "POST", "url" => "/Api/Stock/DeleteVariationGroup", "noparams" => 1),
					"deletevariationgroups" => array( "type" => "POST", "url" => "/Api/Stock/DeleteVariationGroups", "noparams" => 1),
					"deletevariationitem" => array( "type" => "POST", "url" => "/Api/Stock/DeleteVariationItem", "noparams" => 1),
					"deletevariationitems" => array( "type" => "POST", "url" => "/Api/Stock/DeleteVariationItems", "noparams" => 1),
					"getitemchangeshistory" => array( "type" => "POST", "url" => "/Api/Stock/GetItemChangesHistory", "noparams" => 1),
					"getitemchangeshistorycsv" => array( "type" => "POST", "url" => "/Api/Stock/GetItemChangesHistoryCSV", "noparams" => 1),
					"getsoldstat" => array( "type" => "POST", "url" => "/Api/Stock/GetSoldStat", "noparams" => 1),
					"getstockconsumption" => array( "type" => "POST", "url" => "/Api/Stock/GetStockConsumption", "noparams" => 1),
					"getstockduepo" => array( "type" => "POST", "url" => "/Api/Stock/GetStockDuePO", "noparams" => 1),
					"getstockitemreturnstat" => array( "type" => "POST", "url" => "/Api/Stock/GetStockItemReturnStat", "noparams" => 1),
					"getstockitems" => array( "type" => "POST", "url" => "/Api/Stock/GetStockItems", "noparams" => 1),
					"getstockitemsbyids" => array( "type" => "POST", "url" => "/Api/Stock/GetStockItemsByIds", "noparams" => 1),
					"getstockitemsbykey" => array( "type" => "POST", "url" => "/Api/Stock/GetStockItemsByKey", "noparams" => 1),
					"getstockitemscrapstat" => array( "type" => "POST", "url" => "/Api/Stock/GetStockItemScrapStat", "noparams" => 1),
					"getstockitemsfull" => array( "type" => "POST", "url" => "/Api/Stock/GetStockItemsFull", "noparams" => 1),
					"getstockitemsfullbyids" => array( "type" => "POST", "url" => "/Api/Stock/GetStockItemsFullByIds", "noparams" => 1),
					"getstockitemtypeinfo" => array( "type" => "POST", "url" => "/Api/Stock/GetStockItemTypeInfo", "noparams" => 1),
					"getstocklevel" => array( "type" => "POST", "url" => "/Api/Stock/GetStockLevel", "noparams" => 1),
					"getstocklevel_batch" => array( "type" => "POST", "url" => "/Api/Stock/GetStockLevel_Batch", "noparams" => 1),
					"getstocklevelbylocation" => array( "type" => "POST", "url" => "/Api/Stock/GetStockLevelByLocation", "noparams" => 1),
					"getstocksold" => array( "type" => "POST", "url" => "/Api/Stock/GetStockSold", "noparams" => 1),
					"getvariationgroupbyname" => array( "type" => "POST", "url" => "/Api/Stock/GetVariationGroupByName", "noparams" => 1),
					"getvariationgroupbyparentid" => array( "type" => "POST", "url" => "/Api/Stock/GetVariationGroupByParentId", "noparams" => 1),
					"getvariationgroupsearchtypes" => array( "type" => "POST", "url" => "/Api/Stock/GetVariationGroupSearchTypes", "noparams" => 1),
					"getvariationitems" => array( "type" => "POST", "url" => "/Api/Stock/GetVariationItems", "noparams" => 1),
					"renamevariationgroup" => array( "type" => "POST", "url" => "/Api/Stock/RenameVariationGroup", "noparams" => 1),
					"searchvariationgroups" => array( "type" => "POST", "url" => "/Api/Stock/SearchVariationGroups", "noparams" => 1),
					"setstocklevel" => array( "type" => "POST", "url" => "/Api/Stock/SetStockLevel", "noparams" => 1),
					"skuexists" => array( "type" => "POST", "url" => "/Api/Stock/SKUExists", "noparams" => 1),
					"update_stockitempartial" => array( "type" => "POST", "url" => "/Api/Stock/Update_StockItemPartial", "noparams" => 1),
					"updateskugroupidentifier" => array( "type" => "POST", "url" => "/Api/Stock/UpdateSkuGroupIdentifier", "noparams" => 1),
					"updatestocklevelsbulk" => array( "type" => "POST", "url" => "/Api/Stock/UpdateStockLevelsBulk", "noparams" => 1),
					"updatestocklevelsbysku" => array( "type" => "POST", "url" => "/Api/Stock/UpdateStockLevelsBySKU", "noparams" => 1),
					"updatestockminimumlevel" => array( "type" => "POST", "url" => "/Api/Stock/UpdateStockMinimumLevel", "noparams" => 1),
					"additemstotransfer" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/AddItemsToTransfer", "noparams" => 1),
					"additemtotransfer" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/AddItemToTransfer", "noparams" => 1),
					"addtransferbinnote" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/AddTransferBinNote", "noparams" => 1),
					"addtransferitemnote" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/AddTransferItemNote", "noparams" => 1),
					"addtransfernote" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/AddTransferNote", "noparams" => 1),
					"addtransferproperty" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/AddTransferProperty", "noparams" => 1),
					"allocateitemtobin" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/AllocateItemToBin", "noparams" => 1),
					"changebindetails" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/ChangeBinDetails", "noparams" => 1),
					"changetransferfromlocation" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/ChangeTransferFromLocation", "noparams" => 1),
					"changetransferitemreceivedquantity" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/ChangeTransferItemReceivedQuantity", "noparams" => 1),
					"changetransferitemrequestquantity" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/ChangeTransferItemRequestQuantity", "noparams" => 1),
					"changetransferitemsentquantity" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/ChangeTransferItemSentQuantity", "noparams" => 1),
					"changetransferlocations" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/ChangeTransferLocations", "noparams" => 1),
					"changetransferproperty" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/ChangeTransferProperty", "noparams" => 1),
					"changetransferstatus" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/ChangeTransferStatus", "noparams" => 1),
					"changetransfertolocation" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/ChangeTransferToLocation", "noparams" => 1),
					"checkfordrafttransfer" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/CheckForDraftTransfer", "noparams" => 1),
					"createnewbin" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/CreateNewBin", "noparams" => 1),
					"createtransferfromdescrepancies" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/CreateTransferFromDescrepancies", "noparams" => 1),
					"createtransferrequestwithreturn" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/CreateTransferRequestWithReturn", "noparams" => 1),
					"deleteemptydrafttransfer" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/DeleteEmptyDraftTransfer", "noparams" => 1),
					"deletetransfer" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/DeleteTransfer", "noparams" => 1),
					"deletetransferproperty" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/DeleteTransferProperty", "noparams" => 1),
					"getactivetransfersalllocations" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/GetActiveTransfersAllLocations", "noparams" => 1),
					"getactivetransfersforlocation" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/GetActiveTransfersForLocation", "noparams" => 1),
					"getarchivedtransfers" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/GetArchivedTransfers", "noparams" => 1),
					"getarchivedtransfersbetweenarchiveddates" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/GetArchivedTransfersBetweenArchivedDates", "noparams" => 1),
					"getarchivedtransfersbetweendates" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/GetArchivedTransfersBetweenDates", "noparams" => 1),
					"getarchivedtransfersfiltered" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/GetArchivedTransfersFiltered", "noparams" => 1),
					"getdiscrepancyitems" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/GetDiscrepancyItems", "noparams" => 1),
					"getlisttransfers" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/GetListTransfers", "noparams" => 1),
					"getmodifiedbasic" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/GetModifiedBasic", "noparams" => 1),
					"getservertime" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/GetServerTime", "noparams" => 1),
					"getstockavailability" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/GetStockAvailability", "noparams" => 1),
					"gettransferaudit" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/GetTransferAudit", "noparams" => 1),
					"gettransferbinnotes" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/GetTransferBinNotes", "noparams" => 1),
					"gettransferitemnotes" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/GetTransferItemNotes", "noparams" => 1),
					"gettransferitems" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/GetTransferItems", "noparams" => 1),
					"gettransfernotes" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/GetTransferNotes", "noparams" => 1),
					"gettransferproperties" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/GetTransferProperties", "noparams" => 1),
					"gettransferwithitems" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/GetTransferWithItems", "noparams" => 1),
					"gettransferwithnotes" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/GetTransferWithNotes", "noparams" => 1),
					"isdrafttransferchanged" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/IsDraftTransferChanged", "noparams" => 1),
					"printtransfer" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/PrintTransfer", "noparams" => 1),
					"removeallemptybins" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/RemoveAllEmptyBins", "noparams" => 1),
					"removeitemfromtransfer" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/RemoveItemFromTransfer", "noparams" => 1),
					"searchtransfersalllocations" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/SearchTransfersAllLocations", "noparams" => 1),
					"searchtransfersbylocation" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/SearchTransfersByLocation", "noparams" => 1),
					"setreferencenumber" => array( "type" => "POST", "url" => "/Api/WarehouseTransfer/SetReferenceNumber", "noparams" => 1),
					"addwarehousezone" => array( "type" => "POST", "url" => "/Api/Wms/AddWarehouseZone", "noparams" => 1),
					"addwarehousezonetype" => array( "type" => "POST", "url" => "/Api/Wms/AddWarehouseZoneType", "noparams" => 1),
					"deletewarehousezone" => array( "type" => "POST", "url" => "/Api/Wms/DeleteWarehouseZone", "noparams" => 1),
					"deletewarehousezonetype" => array( "type" => "POST", "url" => "/Api/Wms/DeleteWarehouseZoneType", "noparams" => 1),
					"getbinrackzonesbybinrackidorname" => array( "type" => "POST", "url" => "/Api/Wms/GetBinrackZonesByBinrackIdOrName", "noparams" => 1),
					"getbinrackzonesbyzoneidorname" => array( "type" => "POST", "url" => "/Api/Wms/GetBinrackZonesByZoneIdOrName", "noparams" => 1),
					"getwarehousezonesbylocation" => array( "type" => "POST", "url" => "/Api/Wms/GetWarehouseZonesByLocation", "noparams" => 1),
					"getwarehousezonetypes" => array( "type" => "POST", "url" => "/Api/Wms/GetWarehouseZoneTypes", "noparams" => 1),
					"updatewarehousebinrackbinracktozone" => array( "type" => "POST", "url" => "/Api/Wms/UpdateWarehouseBinrackBinrackToZone", "noparams" => 1),
					"updatewarehousezone" => array( "type" => "POST", "url" => "/Api/Wms/UpdateWarehouseZone", "noparams" => 1),
					"updatewarehousezonetype" => array( "type" => "POST", "url" => "/Api/Wms/UpdateWarehouseZoneType", "noparams" => 1)			
				);
				
			$apiname = strtolower( $apiname );
			
			if ( array_key_exists( $apiname, $api_calls ) ) {
				return $api_calls[ $apiname ];
			} else {
				return false;
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
				
				$log_data = array( $apicall => $params,
								  "ParamerterCount" => $pc,
								  "RequiredCount" => $check_api["noparams"]
								);
				
				if ( $this->debug ) {
					echo $this->debug_display( $log_data, "Paramerters" );
				}

				if ( $check_api["noparams"] == $pc ) {
					if ( $pc > 0 ) {
						foreach ( $params AS $key => &$value ) {
							$value = json_encode( $value );
						}
						
						$log_data["Final"] = $params;

						if ( $this->debug ) {
							echo $this->debug_display( $params, "Final Parameters" );
						}

						$params = http_build_query( $params );
					}
					
					$this->log_api_calls( $log_data, "Call Linnworks API" ); // Log API Call
										
					return $this->api_call($check_api["type"],$check_api["url"],$params);

				}
			} else {
				if ( $this->debug ) {
					echo "API Call Not Found<hr>";
				}
			}
			return false;
		}

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
	}
?>