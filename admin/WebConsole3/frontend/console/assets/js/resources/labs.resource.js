(function(){
	'use strict';
	angular.module(appName).service("LabsResource", LabsResource);

	LabsResource.$inject = ["$resource", '$q', 'gbnBaseResource','API_URL'];

	function LabsResource($resource, $q, gbnBaseResource, API_URL){
		var methods = {
			options: options
		}
		var resource = $resource(API_URL+"/ous/:ouid/labs.json/:labid", {ouid: "@ouid", labid: "@labid"}, {'update': {method:'PATCH'}});


		var labsResource = gbnBaseResource.getBaseResource(resource, {methods: methods, mock: false});
		labsResource.clients = clients;
		return labsResource;
			

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
									css: "col-md-8"
								},
								"capacity": {
									type: "number",
									required: false,
									label: "max_clients",
									css: "col-md-2",
									min: 0
								},
								"defclients": {
									type: "number",
									required: false,
									label: "def_clients",
									css: "col-md-2"
								},
							},
							{
								"inremotepc": {
									type: "checkbox",
									required: true,
									css: "col-md-4"
								},
								"projector": {
									type: "checkbox",
									required: true,
									css: "col-md-4"
								},										
								"board": {
									type: "checkbox",
									required: true,
									css: "col-md-4"
								},

							},
							{
								"description": {
									type: "textarea",
									required: false
								},

							},
							{
								"routerip": {
									type: "text",
									required: true,
									css: "col-md-3"
								},
								"netmask": {
									type: "text",
									required: true,
									css: "col-md-3"
								},
								
								"dns": {
									type: "text",
									required: true,
									css: "col-md-4"
								},
							},
							{
								"ntp": {
									type: "text",
									required: true,
									css: "col-md-4"
								},
								"proxyurl": {
									type: "text",
									required: true,
									css: "col-md-4"
								},

							},
							{
								"mcastmode": {
									type: "select",
									required: true,
									options: ["full-duplex", "half-duplex"],
									css: "col-md-3"
								},
								"mcastip": {
									type: "text",
									required: true,
									css: "col-md-5"
								},
								"mcastport": {
									type: "number",
									required: true,
									css: "col-md-2"
								},
								"mcastspeed": {
									type: "number",
									required: true,
									css: "col-md-2"
								},
							},
							{
								"p2pmode": {
									type: "text",
									required: true,
									css: "col-md-6"
								},
								"p2ptime": {
									type: "number",
									required: true,
									css: "col-md-6"
								},
							}
						]
						
						
						
						
						
						
						
						
						
						
					}
				};
				resolve(opts);
			});
		}

		function clients(params){
			var res = $resource(API_URL+"/ous/:ouid/labs/:labid/clients", {ouid: "@ouid", labid: "@labid"}, {'update': {method:'POST'}});
			return res.query(params).$promise;
		}
	}
})();