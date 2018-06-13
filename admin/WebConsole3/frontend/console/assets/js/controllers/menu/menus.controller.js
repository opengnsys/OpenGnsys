(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName).controller('MenusController', MenusController);

	MenusController.$inject = ['$rootScope', '$scope','$state', '$timeout', '$filter', '$sce', 'toaster', 'ogSweetAlert', 'MenusResource', 'OGCommonService'];

	function MenusController($rootScope, $scope, $state, $timeout, $filter, $sce, toaster, ogSweetAlert, MenusResource, OGCommonService) {
		var vm = this;
		vm.menuGroups = [];
		vm.deleteMenu = deleteMenu;

		init();

		function init(){
			MenusResource.query().then(
				function(response){
					vm.menuGroups = [
						OGCommonService.createGroups(response,"menus")
					];
					vm.menuGroups[0].name = "Menus";

				},
				function(error){
					alert(error);
				}
			);
		}
		vm.trustSrc = function(src) {
			return $sce.trustAsResourceUrl(src);
		}

		function deleteMenu(menu){
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
				   MenusResource.delete({menuId: menu.id}).then(
				   	function(response){
				   		toaster.pop({type: "success", title: "success",body: $filter("translate")("successfully_deleted")});
				   		// Buscar el elemento en el array y borrarlo
				   		var index = vm.menuGroups[0].menus.indexOf(menu);
				   		if(index !== -1){
				   			vm.menuGroups[0].menus.splice(index, 1);
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