(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName).controller('NewClientController', NewClientController);

	NewClientController.$inject = ['$rootScope', '$scope','$state', '$timeout', 'toaster', 'ClientsResource', 'RepositoriesResource', 'HardwareProfilesResource', 'NetbootsResource'];

	function NewClientController($rootScope, $scope, $state, $timeout, toaster, ClientsResource, RepositoriesResource, HardwareProfilesResource, NetbootsResource) {
		var vm = this;
		vm.client = {};
		vm.netboots = [];
		vm.save = save;

		init();


		function init(){
			if($rootScope.user){
				loadFormOptions();
				loadNetboots();
				// Los repositorios vienen cargados ya desde config.router
				vm.repositories = $rootScope.repositories;
				if(!$rootScope.hardwareProfiles){
					HardwareProfilesResource.query().then(
						function(response){
							vm.hardwareProfiles = response;
							$rootScope.hardwareProfiles = response;
						},
						function(error){
							alert(error);
						}
					);
				}else{
					vm.hardwareProfiles = $rootScope.hardwareProfiles;
				}
			}
		}

		function loadNetboots(){
			NetbootsResource.query().then(
				function(result){
					vm.netboots = result;
				},
				function(error){
					toaster.pop({type: "error", title: "error",body: error});
				}
			);
		}


		function save(){
  			var result = $rootScope.ValidateFormsService.validateForms(vm);

            // Si no hubo ningun error se guardan todas las pgms
           	if(result == true){
           		// Si se indicó un padre en la url, se añade dicha propiedad
           		vm.client.organizationalUnit = $state.params.ou;
           		vm.client.idproautoexec = 0;
           		var clientCopy = angular.copy(vm.client);
           		clientCopy.repository = clientCopy.repository?clientCopy.repository.id:null;
           		clientCopy.hardwareProfile = clientCopy.hardwareProfile?clientCopy.hardwareProfile.id:null;
           		clientCopy.oglive = (clientCopy.oglive)?clientCopy.oglive.directory:null;
           		clientCopy.netboot = (clientCopy.netboot)?clientCopy.netboot.id:null;
           		
	  			ClientsResource.save(clientCopy).then(
		  			function(response){
			  			$state.go("app.ous",{},{reload: true});
			  			$timeout(function(){
			  				toaster.pop({type: "success", title: "success",body: "Successfully saved"});
			  			},0);
			  		},
			  		function(error){
			  			toaster.pop({type: "error", title: "error",body: error});
			  		}
		  		);
	  		}
  		}


		function loadFormOptions(){
			ClientsResource.options().then(
				function(result){
					vm.formOptions = result;
				}
			);
		};

	  };
})();