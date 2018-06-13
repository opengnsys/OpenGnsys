(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName)
	  .controller('MainMenuController', MainMenuController);
	  
	  // buildings viene injectado desde config.route.js
	  MainMenuController.$inject= ['$rootScope', '$scope', '$filter', '$state', '$translate', 'ous', 'isLogged'];

	  function MainMenuController($rootScope, $scope, $filter, $state, $translate, ous, isLogged) {
	  	var vm = this;

	  	init();


	  	function init(){
			if(isLogged == false){
				$state.go("login.signin");
			}
			else{
				vm.ous = ous;
				vm.changeOu = changeOu;
				vm.selectedOu = $rootScope.user.ou;
			}
	  	}

	  	/**
	  	$rootScope.$watch("user.ou", function(newValue,oldValue,scope){
	  		vm.selectedOu = $rootScope.user.ou;
	  	});
	  	/**/

	  	function changeOu(){
	  		$rootScope.user.ou = vm.selectedOu;
	  		localStorage.setItem("user", JSON.stringify($rootScope.user));
	  		$state.reload();
	  	}
	
	  };
})();