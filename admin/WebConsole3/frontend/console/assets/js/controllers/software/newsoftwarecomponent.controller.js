(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName).controller('NewSoftwareComponentController', NewSoftwareComponentController);

	NewSoftwareComponentController.$inject = ['$rootScope', '$scope','$state', '$timeout', '$filter', 'toaster', 'softwareTypes', 'SoftwareComponentsResource'];

	function NewSoftwareComponentController($rootScope, $scope, $state, $timeout, $filter, toaster, softwareTypes, SoftwareComponentsResource) {
		var vm = this;
		/* Estas variables vienen dadas por el controlador padre software.controller */
		vm.softwareComponent = {};
		vm.formOptions = {};
		vm.save = save;
		vm.softwareTypes = [];

		init();

		function init(){
			vm.softwareTypes = softwareTypes;
			SoftwareComponentsResource.options().then(
				function(response){
					vm.formOptions = response;
				}
			);
		}

		function save(Form){
  			var result = $rootScope.ValidateFormsService.validateForm(Form);

            // Si no hubo ningun error se guardan todas las pgms
           	if(result == true){
	  			SoftwareComponentsResource.save(angular.copy(vm.softwareComponent)).then(
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