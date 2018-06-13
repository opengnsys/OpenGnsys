(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName).controller('NewHardwareComponentController', NewHardwareComponentController);

	NewHardwareComponentController.$inject = ['$rootScope', '$scope','$state', '$timeout', '$filter', 'toaster', 'HardwareComponentsResource'];

	function NewHardwareComponentController($rootScope, $scope, $state, $timeout, $filter, toaster, HardwareComponentsResource) {
		var vm = this;
		/* Estas variables vienen dadas por el controlador padre hardware.controller */
		vm.hardwareComponent = {};
		vm.formOptions = {};
		vm.save = save;

		init();

		function init(){

			HardwareComponentsResource.options().then(
				function(response){
					vm.formOptions = response;
				}
			);
		}

		function save(Form){
  			var result = $rootScope.ValidateFormsService.validateForm(Form);

            // Si no hubo ningun error se guardan todas las pgms
           	if(result == true){
	  			HardwareComponentsResource.save(angular.copy(vm.hardwareComponent)).then(
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