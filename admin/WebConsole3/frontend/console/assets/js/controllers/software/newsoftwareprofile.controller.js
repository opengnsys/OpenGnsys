(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName).controller('NewSoftwareProfileController', NewSoftwareProfileController);

	NewSoftwareProfileController.$inject = ['$rootScope', '$scope','$state', '$timeout', '$filter', 'toaster', 'profileFunctions', 'softwareComponents','SoftwareProfilesResource'];

	function NewSoftwareProfileController($rootScope, $scope, $state, $timeout, $filter, toaster, profileFunctions, softwareComponents, SoftwareProfilesResource) {
		var vm = this;
		/* Estas variables vienen dadas por el controlador padre software.controller */
		vm.softwares = angular.copy(softwareComponents);
		vm.softwareProfile = {};
		// La ruta abstract para profile carga las funciones comunes en el objeto profileFunctions
		vm.checkUnchekComponent = profileFunctions.checkUnchekComponent;
		vm.formOptions = {};
		vm.save = save;

		init();

		function init(){

			SoftwareProfilesResource.options().then(
				function(response){
					vm.formOptions = response;
				}
			);
			vm.softwareProfile.softwares = [];
		}

		function save(Form){
  			var result = $rootScope.ValidateFormsService.validateForm(Form);

            // Si no hubo ningun error se guardan todas las pgms
           	if(result == true){
	  			SoftwareProfilesResource.save(angular.copy(vm.softwareProfile)).then(
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