(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName).controller('NewCommandController', NewCommandController);

	NewCommandController.$inject = ['$rootScope', '$scope','$state', '$timeout', 'toaster', 'CommandsResource'];

	function NewCommandController($rootScope, $scope, $state, $timeout, toaster, CommandsResource) {
		var vm = this;
		vm.command = {};
		vm.save = save;

		init();


		function init(){
			if($rootScope.user){
				loadFormOptions();
			}
		}


		function save(){
  			var result = $rootScope.ValidateFormsService.validateForms(vm);

            // Si no hubo ningun error se guardan todas las pgms
           	if(result == true){
           		vm.command.type = "RUN_SCRIPT";
	  			CommandsResource.save(vm.command).then(
		  			function(response){
			  			toaster.pop({type: "success", title: "success",body: "Successfully saved"});
			  			$state.go("app.commands",{},{reload: true});
			  		},
			  		function(error){
			  			toaster.pop({type: "error", title: "error",body: error});
			  		}
		  		);
	  		}
  		}


		function loadFormOptions(){
			CommandsResource.options().then(
				function(result){
					vm.formOptions = result;
				}
			);
		};

	  };
})();