(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName).controller('EditSoftwareProfileController', EditSoftwareProfileController);

	EditSoftwareProfileController.$inject = ['$rootScope', '$scope','$state', '$timeout', '$filter', 'toaster', 'profileFunctions', 'softwareComponents','SoftwareProfilesResource'];

	function EditSoftwareProfileController($rootScope, $scope, $state, $timeout, $filter, toaster, profileFunctions, softwareComponents, SoftwareProfilesResource) {
		var vm = this;
		vm.formOptions = {};
		vm.softwareProfile = {};
		/* Estas variables vienen dadas por el controlador padre software.controller */
		vm.softwares = angular.copy(softwareComponents);
		vm.softwareProfile = {};
		// La ruta abstract para profile carga las funciones comunes en el objeto profileFunctions
		vm.checkUnchekComponent = profileFunctions.checkUnchekComponent;
		vm.save = save;

		init();

		function init(){
			SoftwareProfilesResource.options().then(
				function(response){
					vm.formOptions = response;
				}
			);

			SoftwareProfilesResource.get({profileId: $state.params.profileId}).then(
				function(response){
					vm.softwareProfile = response;
					// Seleccionamos aquellos componentes que formen parte del perfil
					vm.softwareProfile.softwares = vm.softwareProfile.softwares||[];
					for(var index = 0; index < vm.softwareProfile.softwares.length; index++){
						var result = vm.softwares.indexOfByKey(vm.softwareProfile.softwares[index], "id");
						vm.softwares[result].$$selected = true;
						// Solo nos quedamos con el id
						vm.softwareProfile.softwares[index] = vm.softwareProfile.softwares[index].id;
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
           		var hpCopy = angular.copy(vm.softwareProfile);
           		delete hpCopy.id;
	  			SoftwareProfilesResource.update({profileId: vm.softwareProfile.id}, hpCopy).then(
		  			function(response){
			  			toaster.pop({type: "success", title: "success",body: "Successfully saved"});
			  			$state.go("app.software",{},{reload: true});
			  		},
			  		function(error){
			  			toaster.pop({type: "error", title: "error",body: error});
			  		}
		  		);
	  		}
  		}

	  };
})();