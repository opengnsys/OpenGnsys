(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName).controller('DhcpClientController', DhcpClientController);

	DhcpClientController.$inject = ['$rootScope', '$scope','$state', '$timeout', '$filter', '$q','toaster', 'ClientsResource', 'RepositoriesResource', 'HardwareProfilesResource', 'ServerDchpResource', 'NetbootsResource'];

	function DhcpClientController($rootScope, $scope, $state, $timeout, $filter, $q, toaster, ClientsResource, RepositoriesResource, HardwareProfilesResource, ServerDchpResource, NetbootsResource ) {
		var vm = this;
		vm.clients = [];
		vm.netboots = [];
		vm.selectAll = true;
		vm.downloadFromServer = downloadFromServer;
		vm.proccessDhcp = proccessDhcp;
		vm.selectedUnselectAll = selectedUnselectAll;
		vm.save = save;
		vm.commonProperties = {
			netiface: "eth0",
			netdriver: "generic",
			oglive: $rootScope.constants.ogliveinfo[0]

		};

		init();


		function init(){
			vm.dhcpFile = "/etc/dhcp/dhcpd.conf";
			loadNetboots();
			// Los repositorios vienen cargados ya desde config.router
			vm.repositories = $rootScope.repositories;
			vm.commonProperties.repository = vm.repositories[0].id;
			if(!$rootScope.hardwareProfiles){
				HardwareProfilesResource.query().then(
					function(response){
						vm.hardwareProfiles = response;
						$rootScope.hardwareProfiles = response;
						vm.commonProperties.hardwareProfile = vm.hardwareProfiles[0].id;
					},
					function(error){
						alert(error);
					}
				);
			}else{
				vm.hardwareProfiles = $rootScope.hardwareProfiles;
			}
		}

		function loadNetboots(){
			NetbootsResource.query().then(
				function(result){
					vm.netboots = result;
					vm.commonProperties.netboot = vm.netboots[0];
				},
				function(error){
					toaster.pop({type: "error", title: "error",body: error});
				}
			);
		}


		function downloadFromServer(){
			ServerDchpResource.getDhcp().then(
				function(response){
					vm.dhcpText = response.text;
					toaster.pop({type: "success", title: "success",body: $filter("translate")("successfully_loaded")});
				},
				function(error){
					toaster.pop({type: "error", title: "error",body: error});
				}
			);
		}

		function proccessDhcp(){
			if(typeof vm.dhcpText != "undefined" && vm.dhcpText != ""){
				var lines = vm.dhcpText.split('\n');
				vm.clients = [];
				for(var i = 0;i < lines.length; i++){
				    // Comprobar si la línea actual contiene la palabra "host" sin ninguna # delante que sería comentario
				    if(/^host/.test(lines[i].trim())){
				    	// procesar la linea
				    	// host pc53-151 { hardware ethernet 00:1E:33:61:49:B8; fixed-address 172.16.53.151; }
				    	var parts = lines[i].split("{");
				    	var hostname = parts[0].trim().split(" ")[1];

				    	parts = parts[1].trim().split(";");
				    	var mac = parts[0].trim().split("ethernet")[1];
				    	var ip = parts[1].trim().split("fixed-address")[1];
				    	vm.clients.push(
					    	{
					    		name: hostname,
					    		ip: ip,
					    		mac: mac,
					    		$$selected: true
					    	}
				    	);
				    }
				}
			}
			else{
				toaster.pop({type: "error", title: "error",body: $filter("translate")("nothing_to_proccess")});
			}
		}

		function selectedUnselectAll(){
			for(var c = 0; c < vm.clients.length; c++){
				vm.clients[c].$$selected = vm.selectAll;
			}
		}

		function save(){

			var promises = [];
           for(var c = 0; c < vm.clients.length; c++){
           		if(vm.clients[c].$$selected == true){
           			var client = angular.copy(vm.clients[c]);

           			// Si se indicó un padre en la url, se añade dicha propiedad
		       		client.organizationalUnit = $state.params.ou;
		       		client.idproautoexec = 0;
		       		client.netdriver = vm.commonProperties.netdriver;
		       		client.netiface = vm.commonProperties.netiface;
		       		// Propiedades comunes
		       		//client.repository = vm.commonProperties.repository;
		       		//client.hardwareProfile = vm.commonProperties.hardwareProfile;
		       		promises.push(ClientsResource.save(client));
           		}
  			}
  			$q.all(promises).then(
	  			function(response){
		  			toaster.pop({type: "success", title: "success",body: "Successfully saved"});
		  			$state.go("app.ous",{},{reload: true});
		  		},
		  		function(error){
		  			toaster.pop({type: "error", title: "error",body: error});
		  		}
	  		);
  		}

	  };
})();