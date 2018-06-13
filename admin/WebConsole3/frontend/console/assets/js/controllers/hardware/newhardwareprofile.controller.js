(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName).controller('NewHardwareProfileController', NewHardwareProfileController);

	NewHardwareProfileController.$inject = ['$rootScope', '$scope','$state', '$timeout', '$filter', 'toaster', 'profileFunctions', 'hardwareComponents','HardwareProfilesResource'];

	function NewHardwareProfileController($rootScope, $scope, $state, $timeout, $filter, toaster, profileFunctions, hardwareComponents, HardwareProfilesResource) {
		var vm = this;
		/* Estas variables vienen dadas por el controlador padre hardware.controller */
		vm.hardwares = angular.copy(hardwareComponents);
		vm.hardwareProfile = {};
		// La ruta abstract para profile carga las funciones comunes en el objeto profileFunctions
		vm.checkUnchekComponent = profileFunctions.checkUnchekComponent;
		vm.formOptions = {};
		vm.save = save;

		init();

		function init(){

			HardwareProfilesResource.options().then(
				function(response){
					vm.formOptions = response;
				}
			);
			vm.hardwareProfile.hardwares = [];
		}

		function save(Form){
  			var result = $rootScope.ValidateFormsService.validateForm(Form);

            // Si no hubo ningun error se guardan todas las pgms
           	if(result == true){
	  			HardwareProfilesResource.save(angular.copy(vm.hardwareProfile)).then(
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