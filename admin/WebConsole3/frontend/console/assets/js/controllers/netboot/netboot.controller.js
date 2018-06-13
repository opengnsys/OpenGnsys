(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName).controller('NetbootController', NetbootController);

	NetbootController.$inject = ['$rootScope', '$state', '$timeout', '$filter', '$sce', 'toaster', 'ogSweetAlert', 'NetbootsResource'];

	function NetbootController($rootScope, $state, $timeout, $filter, $sce, toaster, ogSweetAlert, NetbootsResource) {
		var vm = this;
		vm.netboots = [];
		vm.deleteNetboot = deleteNetboot;
		
		init();

		function init(){
			NetbootsResource.query().then(
				function(result){
					vm.netboots = result;
				}
			);
		}

  		function deleteNetboot(id){
  			ogSweetAlert.swal({
			   title: $filter("translate")("sure_to_delete")+"?",
			   text: $filter("translate")("action_cannot_be_undone"),
			   type: "warning",
			   showCancelButton: true,
			   confirmButtonColor: "#3c8dbc",
			   confirmButtonText: $filter("translate")("yes_delete"),
			   closeOnConfirm: true}, 
			function(result){
				if(result == true){
					NetbootsResource.delete({netbootId: id}).then(
						function(response){
							toaster.pop({type: "success", title: $filter("translate")("success"),body: $filter("translate")("successfully_deleted")});
							var index = vm.netboots.indexOfByKey({id: id}, "id");
							if(index != -1){
								vm.netboots.splice(index,1);
							}
						},
						function(error){
							toaster.pop({type: "error", title: "error",body: error});
						}
					);
				}
			});
  		}

	  };
})();