(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName).controller('EditMenuController', EditMenuController);

	EditMenuController.$inject = ['$rootScope', '$scope','$state', '$timeout', '$filter', '$sce', 'MenusResource'];

	function EditMenuController($rootScope, $scope, $state, $timeout, $filter, $sce, MenusResource) {
		var vm = this;
		vm.menu = [];
		vm.formOptions = {};
		vm.modifyMenu = modifyMenu;

		vm.editMenuItem = editMenuItem;
		vm.saveMenuItem = saveMenuItem;

		init();

		function init(){
			if($rootScope.user){
				MenusResource.get({menuId: $state.params.menuId}).then(
					function(response){
						vm.menu = response;
					},
					function(error){
						alert(error);
					}
				);
			}
			MenusResource.options().then(
				function(result){
					vm.formOptions = result;
				}
			);
		}

		vm.trustSrc = function(src) {
			return $sce.trustAsResourceUrl(src);
		}

		function modifyMenu(menu){

		}

		function editMenuItem(menuItem){
			menuItem.$$editing = true;
			menuItem.$$tmpPrivate = {id: menuItem.private};
			menuItem.$$tmpDescription = menuItem.description;
			menuItem.$$tmpImgUrl = {value: menuItem.imgUrl};
			menuItem.$$tmpOrder = parseInt(menuItem.order);
		}
		function saveMenuItem(menuItem){
			menuItem.$$editing = false;
			menuItem.private = menuItem.$$tmpPrivate.id;
			menuItem.description = menuItem.$$tmpDescription;
			menuItem.imgUrl = menuItem.$$tmpImgUrl.value;
			menuItem.order = parseInt(menuItem.$$tmpOrder);
			// TODO - Llamar al servidor para guardar el cambio
		}

	  };
})();