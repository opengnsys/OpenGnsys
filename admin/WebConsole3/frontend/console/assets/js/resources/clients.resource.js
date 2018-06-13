(function(){
	'use strict';
	angular.module(appName).service("ClientsResource", ClientsResource);

	ClientsResource.$inject = ["$resource", '$q', 'gbnBaseResource','API_URL'];

	function ClientsResource($resource, $q, gbnBaseResource, API_URL){
		var methods = {
			options: options,
			hardware: hardware,
			status: status,
			statusAll: statusAll,
			diskConfig: diskConfig
		}
		var resource = $resource(API_URL+"/clients/:clientId.json", {clientId: "@clientId"}, {'update': {method:'PATCH'}});


		return gbnBaseResource.getBaseResource(resource, {methods: methods, mock: false});
			

		function options(){
			return $q(function(resolve, reject){
				var opts = {
					fields: {
						rows: [
							{
								"name": {
									type: "text",
									required: true,
									label: "name",
									css: "col-md-2"
								},
								"mac": {
									type: "text",
									required: true,
									css: "col-md-2"
								},
								"ip": {
									type: "text",
									required: true,
									css: "col-md-2"
								},
								"serialno": {
									type: "text",
									required: true,
									css: "col-md-2"
								},
								"netiface": {
									type: "text",
									required: false,
									label: "eth",
									css: "col-md-1",
									min: 0
								},
								"netdriver": {
									type: "text",
									required: false,
									label: "net_driver",
									css: "col-md-1"
								}
								
							},
							{
								
								"repository": {
									type: "select",
									label: "repository",
									required: false,
									css: "col-md-2",
									options: {
										source: "vm.repositories",
										label: "name",
										output: "id",
										trackby: " track by item.id"
									},
								},
								"hardwareProfile": {
									type: "select",
									label: "hardware_profile",
									required: false,
									css: "col-md-2",
									options: {
										source: "vm.hardwareProfiles",
										label: "description",
										output: "id",
										trackby: " track by item.id"
									},
								},
								"oglive": {
									type: "select",
									label: "oglive",
									required: true,
									css: "col-md-3",
									options: {
										source: "constants.ogliveinfo",
										label: "directory",
										output: "directory",
										trackby: " track by item.directory"
									},
								},
								"netboot": {
									type: "select",
									label: "netboot",
									required: true,
									css: "col-md-3",
									options: {
										source: "vm.netboots",
										label: "name",
										output: "id",
										trackby: " track by item.id"
									},
								}
							}
						]
					}
				};
				resolve(opts);
			});
		}

		function hardware() {
			var resource =  $resource(API_URL+"/ous/:ouid/labs/:labId/clients/:clientId/hardware", {ouid: "@ouid", labId: "@labId", clientId: "@clientId"}, {'update': {method:'POST'}});
			return gbnBaseResource.getBaseResource(resource, {mock: false});
		}

		function status() {
			var resource =  $resource(API_URL+"/ous/:ouid/labs/:labId/clients/:clientId/status", {ouid: "@ouid", labId: "@labId", clientId: "@clientId"}, {'update': {method:'POST'}});
			return gbnBaseResource.getBaseResource(resource, {mock: false});
		}

	
		function statusAll() {
			var resource =  $resource(API_URL+"/clients/status.json", {ou: "@ouId"}, {'update': {method:'PATCH'}});
			return gbnBaseResource.getBaseResource(resource, {mock: false});
		}

		function diskConfig() {
			var resource =  $resource(API_URL+"/ous/:ouid/labs/:labId/clients/:clientId/diskcfg", {ouid: "@ouid", labId: "@labId", clientId: "@clientId"}, {'update': {method:'POST'}});
			return gbnBaseResource.getBaseResource(resource, {mock: false});
		}
	}
})();