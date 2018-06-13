(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName).controller('HardwareProfileController', HardwareProfileController);

	HardwareProfileController.$inject = ['$rootScope', '$scope','$state', '$timeout', '$filter', 'hardwareComponents','HardwareProfilesResource'];

	function HardwareProfileController($rootScope, $scope, $state, $timeout, $filter, hardwareComponents, HardwareProfilesResource) {
		var vm = this;
		/* Estas variables vienen dadas por el controlador padre hardware.controller */
		vm.hardwareComponents = hardwareComponents;
		vm.hardwareProfile = {};
		vm.checkUnchekComponent = checkUnchekComponent;

		init();

		function init(){
			if($rootScope.user){
				HardwareProfilesResource.get({ouid: $rootScope.user.ou.id, profileId: $state.params.profileId}).then(
					function(response){
						vm.hardwareProfile = response;
						// Seleccionamos aquellos componentes que formen parte del perfil
						for(var index = 0; index < vm.hardwareProfile.hardwareComponents.length; index++){
							var result = vm.hardwareComponents.indexOfByKey(vm.hardwareProfile.hardwareComponents[index], "id");
							vm.hardwareComponents[result].selected = true;
						}
					},
					function(error){
						alert(error);
					}
				);
			}
		}

		function checkUnchekComponent(component){
			// Seleccionar o deseleccionar el componente en el perfil hardware
			// Si el componente que llega está deseleccionado
			if(component.selected == false){
				// Hay que quitarlo del perfil hardware
				var index = vm.hardwareProfile.hardwareComponents.indexOfByKey(component, "id");
				if(index != -1){
					vm.hardwareProfile.hardwareComponents.splice(index,1);
				}
			}
			else{
				vm.hardwareProfile.hardwareComponents.push(component);
			}
		}

	  };
})();