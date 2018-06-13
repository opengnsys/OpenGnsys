(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName).controller('NewMenuController', NewMenuController);

	NewMenuController.$inject = ['$rootScope', '$scope','$state', '$timeout', '$filter', '$sce', 'toaster', 'MenusResource'];

	function NewMenuController($rootScope, $scope, $state, $timeout, $filter, $sce, toaster, MenusResource) {
		var vm = this;
		vm.menu = {};
		vm.formOptions = {};
		
		vm.save = save;
		vm.editMenuItem = editMenuItem;
		vm.saveMenuItem = saveMenuItem;

		init();

		function init(){
			MenusResource.options().then(
				function(result){
					vm.formOptions = result;
				}
			);
		}

		function save(Form){
  			var result = $rootScope.ValidateFormsService.validateForm(Form);

            // Si no hubo ningun error se guardan todas las pgms
           	if(result == true){
           		var menuCopy = angular.copy(vm.menu);
           		menuCopy.resolution = vm.menu.resolution.id;
	  			MenusResource.save(menuCopy).then(
		  			function(response){
			  			$state.go("app.menus",{},{reload: true});
			  			$timeout(function(){
			  				toaster.pop({type: "success", title: "success",body: "Successfully saved"});
			  			}, 0);
			  		},
			  		function(error){
			  			toaster.pop({type: "error", title: "error",body: error});
			  		}
		  		);
	  		}
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