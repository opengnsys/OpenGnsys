(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName).controller('LoginCommandController', LoginCommandController);

	LoginCommandController.$inject = ['$rootScope', '$scope','$state', '$filter', '$timeout', '$q', 'toaster', 'CommandsResource'];

	function LoginCommandController($rootScope, $scope, $state, $filter, $timeout, $q, toaster,  CommandsResource) {
		var vm = this;
		vm.execution = {};
		vm.command = {};
		vm.sendCommand = sendCommand;
		vm.canLogin = canLogin;

		init();


		function init(){
			if($rootScope.user && $rootScope.selectedClients){
				vm.execution.clients = _.join(Object.keys($rootScope.selectedClients));
			}
		}


		function sendCommand(){
  			var result = $rootScope.ValidateFormsService.validateForms(vm);
  			if(!vm.selectedPartition){
  				toaster.pop({type: "error", title: "error",body: $filter("translate")("you_must_select_partition")});
  			}
  			else{
  				var disk = vm.selectedPartition.numDisk;
  				var partition = vm.selectedPartition.numPartition;

	  			vm.execution.script = "bootOs " + disk + " " + partition +" &";
	  			vm.execution.script = vm.execution.script.replace(/\"/g, "\\\"").replace(/\$/g, "\\\$");
  				vm.execution.type = "RUN_SCRIPT";

	  			CommandsResource.execute(vm.execution).then(
		  			function(response){
			  			toaster.pop({type: "success", title: "success",body: $filter("translate")("successfully_executed")});
			  			$state.go("app.ous",{},{reload: true});
			  		},
			  		function(error){
			  			toaster.pop({type: "error", title: "error",body: error});
			  		}
		  		);
		  	}
  		}


  		function canLogin(partition) {
  			return partition.osName != "" && partition.osName != "DATA";
  		}

	  };
})();