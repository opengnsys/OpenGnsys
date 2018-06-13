(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName).controller('EditHardwareProfileController', EditHardwareProfileController);

	EditHardwareProfileController.$inject = ['$rootScope', '$scope','$state', '$timeout', '$filter', 'toaster', 'profileFunctions', 'hardwareComponents','HardwareProfilesResource'];

	function EditHardwareProfileController($rootScope, $scope, $state, $timeout, $filter, toaster, profileFunctions, hardwareComponents, HardwareProfilesResource) {
		var vm = this;
		vm.formOptions = {};
		vm.hardwareProfile = {};
		/* Estas variables vienen dadas por el controlador padre hardware.controller */
		vm.hardwares = angular.copy(hardwareComponents);
		vm.hardwareProfile = {};
		// La ruta abstract para profile carga las funciones comunes en el objeto profileFunctions
		vm.checkUnchekComponent = profileFunctions.checkUnchekComponent;
		vm.save = save;

		init();

		function init(){
			HardwareProfilesResource.options().then(
				function(response){
					vm.formOptions = response;
				}
			);

			HardwareProfilesResource.get({profileId: $state.params.profileId}).then(
				function(response){
					vm.hardwareProfile = response;
					// Seleccionamos aquellos componentes que formen parte del perfil
					vm.hardwareProfile.hardwares = vm.hardwareProfile.hardwares||[];
					for(var index = 0; index < vm.hardwareProfile.hardwares.length; index++){
						var result = vm.hardwares.indexOfByKey(vm.hardwareProfile.hardwares[index], "id");
						vm.hardwares[result].$$selected = true;
						// Solo nos quedamos con el id
						vm.hardwareProfile.hardwares[index] = vm.hardwareProfile.hardwares[index].id;
					}
				},
				function(error){
					toaster.pop({type: "error", title: "error",body: error});
				}
			);
		}

		function save(Form){
  			var result = $rootScope.ValidateFormsService.validateForm(Form);

            // Si no hubo ningun error se guardan todas las pgms
           	if(result == true){
           		var hpCopy = angular.copy(vm.hardwareProfile);
           		delete hpCopy.id;
	  			HardwareProfilesResource.update({profileId: vm.hardwareProfile.id}, hpCopy).then(
		  			function(response){
			  			toaster.pop({type: "success", title: "success",body: "Successfully saved"});
			  			$state.go("app.hardware",{},{reload: true});
			  		},
			  		function(error){
			  			toaster.pop({type: "error", title: "error",body: error});
			  		}
		  		);
	  		}
  		}

	  };
})();