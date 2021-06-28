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

			$this->curl_handle = curl_init(); // Initate New curl session
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
				echo "URL: " . $api_url;
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

		// Customer
		
		function CreateNewCustomer() {
			/*
				customerDetails={
					"EmailAddress": "sample string 1",
					"Address1": "sample string 2",
					"Address2": "sample string 3",
					"Address3": "sample string 4",
					"Town": "sample string 5",
					"Region": "sample string 6",
					"PostCode": "sample string 7",
					"Country": "sample string 8",
					"Continent": "sample string 9",
					"FullName": "sample string 10",
					"Company": "sample string 11",
					"PhoneNumber": "sample string 12",
					"CountryId": "8c7c9eec-9b5e-4b45-b2d9-1d1926f07016"
				}
			*/
			
			$url = "/api/Customer/CreateNewCustomer";
		}

		// Dashboards
		
		function ExecuteCustomPagedScript() {
		/*
			scriptId=1¶meters=[
			{
				"ParameterId": 1,
				"Type": "sample string 2",
				"Name": "sample string 3",
				"Description": "sample string 4",
				"DefaultValue": "sample string 5",
				"AvailableValues": [
					"sample string 1"
				],
				"Value": {},
				"SortOrder": 10
			}
			]&entriesPerPage=1&pageNumber=1&cancellationToken={
				"IsCancellationRequested": false,
				"CanBeCanceled": false,
				"WaitHandle": {
					"Handle": {
						"value": 1468
					},
					"SafeWaitHandle": {
						"IsInvalid": false,
						"IsClosed": false
					}
				}
			}
		*/ 
		
			$url = "/api/Dashboards/ExecuteCustomPagedScript";
		}
		function ExecuteCustomPagedScriptCustomer() {
			/*
			
			*/
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function ExecuteCustomScriptQuery() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetInventoryLocationCategoriesData() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetInventoryLocationData() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetInventoryLocationProductsData() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetLowStockLevel() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetPerformanceDetail() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetPerformanceTableData() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetTopProducts() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}

		// Email
		
		function GenerateAdhocEmail() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GenerateFreeTextEmail() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetEmailTemplate() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetEmailTemplates() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}

		// Extensions
		
		function DeleteSetting() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetSetting() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetSettingKeys() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetSettings() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function SetSetting() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}

		// Import/Export
		
		function DeleteExport() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteImport() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DownloadImportedFile() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function EnableExport() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function EnableImport() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetExport() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetExportList() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetFullfilmentCenterSettings() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetImport() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetImportList() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function RunNowExport() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function RunNowImport() {
			/*
			
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
		function CreateInventoryItemCompositions() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CreateInventoryItemDescriptions() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CreateInventoryItemExtendedProperties() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CreateInventoryItemPrices() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CreateInventoryItemPricingRules() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CreateInventoryItemTitles() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CreateStockSupplierStat() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CreateUserSpecificView() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteBatchesByStockItemId() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteBatchInventoryInBulk() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteCategoryById() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteCountries() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteEbayCompatibilityList() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteImagesFromInventoryItem() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteInventoryItemChannelSKUs() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteInventoryItemCompositions() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteInventoryItemDescriptions() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteInventoryItemExtendedProperties() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteInventoryItemPrices() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteInventoryItemPricingRules() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteInventoryItems() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteInventoryItemTitles() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteItemLocations() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteProductIdentifiers() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteScrapCategories() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteStockSupplierStat() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteSuppliers() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteUserSpecificView() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DuplicateInventoryItem() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

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
		
		function EndListingsPendingRelist() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetEbayListingAudit() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function SetListingStrikeOffState() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}

		// Locations
		
		function AddLocation() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteLocation() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteWarehouseTOTE() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetLocation() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetWarehouseTOTEs() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateLocation() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}

		// Macro
		
		function GetInstalledMacros() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetMacroConfigurations() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

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

		// Returns Refunds
		
		function AcknowledgeRefundErrors() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function AcknowledgeRMAErrors() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function ActionBookedOrder() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function ActionRefund() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function ActionRMABooking() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CreateRefund() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CreateReturnsRefundsCSV() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CreateRMABooking() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteBookedItem() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteBookedOrder() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeletePendingRefundItem() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteRefund() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteRMA() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function EditBookedItemInfo() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetActionableRefundHeaders() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetActionableRMAHeaders() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetBookedReturnsExchangeOrders() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetProcessedOrAckedErrorRefundHeaders() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetProcessedOrAckedErrorRMAHeaders() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetRefundHeadersByOrderId() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetRefundLinesByHeaderId() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetRefundOptions() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetRefundOrders() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetReturnOptions() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetRMAHeadersByOrderId() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetSearchTypes() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetReturnsTotalRefunds() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetWarehouseLocations() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function RefundOrder() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function SearchReturnsRefundsPaged() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateRefund() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateRMABooking() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}

		// Rule Engine
		
		function AddAction() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CheckConditionNameExists() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CopyAction() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CopyCondition() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CreateDraftFromExisting() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CreateNewCondition() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CreateNewDraft() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CreateNewDraftFromExisting() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteAction() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteCondition() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteRuleById() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetActionOptions() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetActionTypes() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetConditionWeb() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetEvaluationFields() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetEvaluatorTypes() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetKeyOptions() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetMultiKeyOptions() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetMultiOptions() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetOptions() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetRequiredFieldsByRuleId() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetRequiredFieldsByType() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetRuleConditionNodes() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetRules() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetRulesByType() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetValuesFromExisting() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function SaveConditionChanges() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function SetConditionEnabled() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function SetDraftLive() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function SetRuleEnabled() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function SetRuleName() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function SwapConditions() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function SwapRules() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function TestEvaluateRule() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateAction() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}

		// Settings
		
		function DeleteCurrencyConversionRates() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetAvailableTimeZones() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetCurrencyConversionRates() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetLatestCurrencyRate() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetMeasures() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function InsertCurrencyConversionRates() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateCurrencyConversionRates() {
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

		// Warehouse Transfer
		
		function AddItemsToTransfer() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function AddItemToTransfer() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function AddTransferBinNote() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function AddTransferItemNote() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function AddTransferNote() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function AddTransferProperty() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function AllocateItemToBin() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function ChangeBinDetails() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function ChangeTransferFromLocation() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function ChangeTransferItemReceivedQuantity() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function ChangeTransferItemRequestQuantity() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function ChangeTransferItemSentQuantity() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function ChangeTransferLocations() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function ChangeTransferProperty() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function ChangeTransferStatus() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function ChangeTransferToLocation() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CheckForDraftTransfer() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CreateNewBin() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CreateTransferFromDescrepancies() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function CreateTransferRequestWithReturn() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteEmptyDraftTransfer() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteTransfer() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteTransferProperty() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetActiveTransfersAllLocations() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetActiveTransfersForLocation() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetArchivedTransfers() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetArchivedTransfersBetweenArchivedDates() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetArchivedTransfersBetweenDates() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetArchivedTransfersFiltered() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetDiscrepancyItems() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetListTransfers() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetModifiedBasic() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetServerTime() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetStockAvailability() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetTransferAudit() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetTransferBinNotes() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetTransferItemNotes() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetTransferItems() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetTransferNotes() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetTransferProperties() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetTransferWithItems() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetTransferWithNotes() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function IsDraftTransferChanged() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function PrintTransfer() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function RemoveAllEmptyBins() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function RemoveItemFromTransfer() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function SearchTransfersAllLocations() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function SearchTransfersByLocation() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function SetReferenceNumber() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}

		// Wms
		
		function AddWarehouseZone() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function AddWarehouseZoneType() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteWarehouseZone() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function DeleteWarehouseZoneType() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetBinrackZonesByBinrackIdOrName() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetBinrackZonesByZoneIdOrName() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetWarehouseZonesByLocation() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function GetWarehouseZoneTypes() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateWarehouseBinrackBinrackToZone() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateWarehouseZone() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
		function UpdateWarehouseZoneType() {
			/*
			
			*/
			
			$params = "OrderId=" . json_encode($ordernum);
			$c_data = self::api_call( "POST", "", $params); // Call API

			return $c_data;
		}
	}
?>