(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName).controller('CommandsController', CommandsController);

	CommandsController.$inject = ['$rootScope', '$scope','$state', '$timeout', '$filter', 'ogSweetAlert', 'toaster', 'CommandsResource'];

	function CommandsController($rootScope, $scope, $state, $timeout, $filter, ogSweetAlert, toaster, CommandsResource) {
		var vm = this;
		vm.commands = [];
		
		init();

		function init(){
			CommandsResource.query().then(
				function(response){
					vm.commands = response;
				},
				function(error){
					toaster.pop({type: "error", title: "error",body: error});
				}
			)
		}

	  };
})();