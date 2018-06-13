(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName).controller('EditCommandController', EditCommandController);

	EditCommandController.$inject = ['$rootScope', '$scope','$state', '$timeout', 'toaster', 'CommandsResource'];

	function EditCommandController($rootScope, $scope, $state, $timeout, toaster, CommandsResource) {
		var vm = this;
		vm.command = {};
		vm.save = save;

		init();


		function init(){
			if($rootScope.user){
				loadFormOptions();
				CommandsResource.get({commandId: $state.params.commandId}).then(
					function(response){
						vm.command = response;
					},
					function(error){
						toaster.pop({type: "error", title: "error",body: error});
					}
				);
			}
		}

		function save(){
  			var result = $rootScope.ValidateFormsService.validateForms(vm);

            // Si no hubo ningun error se guardan todas las pgms
           	if(result == true){
           		var obj = angular.copy(vm.command);
           		delete obj.id;
	  			CommandsResource.update({commandId: vm.command.id}, obj).then(
		  			function(response){
			  			toaster.pop({type: "success", title: "success",body: "Successfully updated"});
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