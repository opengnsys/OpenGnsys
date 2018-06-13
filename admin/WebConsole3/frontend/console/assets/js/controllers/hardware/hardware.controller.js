(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName).controller('HardwareController', HardwareController);

	HardwareController.$inject = ['$rootScope', '$scope','$state', '$timeout', '$filter', 'toaster', 'ogSweetAlert', 'hardwareTypes', 'hardwareComponents','HardwareProfilesResource', 'HardwareComponentsResource', 'OGCommonService'];

	function HardwareController($rootScope, $scope, $state, $timeout, $filter, toaster, ogSweetAlert, hardwareTypes, hardwareComponents, HardwareProfilesResource, HardwareComponentsResource, OGCommonService) {
		var vm = this;
		vm.windowsboots = $rootScope.constants.windowsboots;
		vm.hardwareTypes = [];
		vm.hardwareComponents = [];
		vm.hardwareComponentsGroups = [];
		vm.hardwareProfiles = [];
		vm.hardwareProfileGroups = [];

		vm.editHardwareProfile = editHardwareProfile;
		vm.saveHardwareProfile = saveHardwareProfile;
		vm.deleteHardwareProfile = deleteHardwareProfile;

		vm.editHardwareType = editHardwareType;
		vm.saveHardwareType = saveHardwareType;

		vm.editHardwareComponent = editHardwareComponent;
		vm.saveHardwareComponent = saveHardwareComponent;
		vm.deleteHardwareComponent = deleteHardwareComponent;

		init();

		function init(){
			if($rootScope.user){
				HardwareProfilesResource.query().then(
					function(response){
						vm.hardwareProfileGroups = [
							OGCommonService.createGroups(response, "profiles")
						];
						vm.hardwareProfileGroups[0].name =  $filter("translate")("hardware_profiles");
						
					},
					function(error){
						alert(error);
					}
				);
				vm.hardwareTypes = hardwareTypes;
				vm.hardwareComponentsGroups = [
						OGCommonService.createGroups(hardwareComponents, "components")
				];
				vm.hardwareComponentsGroups[0].name = $filter("translate")("hardware_components");
				
			}
		}

		
		function editHardwareProfile(hardwareProfile){
			hardwareProfile.$$editing = true;
			hardwareProfile.$$tmpName = hardwareProfile.name;
			hardwareProfile.$$tmpDescription = hardwareProfile.description;
			hardwareProfile.$$tmpWindowsboot = hardwareProfile.windowsboot;
		}
		function saveHardwareProfile(hardwareProfile){
			hardwareProfile.$$editing = false;
			hardwareProfile.name = hardwareProfile.$$tmpName;
			hardwareProfile.description = hardwareProfile.$$tmpDescription;
			hardwareProfile.windowsboot = hardwareProfile.$$tmpWindowsboot;
			var hpCopy = angular.copy(hardwareProfile);
			delete hpCopy.id;
			// TODO - Llamar al servidor para guardar el cambio
			HardwareProfilesResource.update({profileId: hardwareProfile.id}, hpCopy).then(
				function(response){
		  			toaster.pop({type: "success", title: "success",body: $filter("translate")("successfully_saved")});
		  		},
		  		function(error){
		  			toaster.pop({type: "error", title: "error",body: error});
		  		}
			);
		}

		function deleteHardwareProfile(hardwareProfile){
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
					HardwareProfilesResource.delete({profileId: hardwareProfile.id}).then(
						function(response){
				  			toaster.pop({type: "success", title: $filter("translate")("success"),body: $filter("translate")("successfully_deleted")});
				  			var index = vm.hardwareProfileGroups[0].profiles.indexOf(hardwareProfile);
				  			if(index != -1){
				  				vm.hardwareProfileGroups[0].profiles.splice(index,1);
				  			}
				  		},
				  		function(error){
				  			toaster.pop({type: "error", title: $filter("translate")("error"),body: error});
				  		}
					)
				}
			});
		}

		function editHardwareType(hardwareType){
			hardwareType.$$editing = true;
			hardwareType.$$tmpName = hardwareType.name;
		}
		function saveHardwareType(hardwareType){
			hardwareType.$$editing = false;
			hardwareType.name = hardwareType.$$tmpName;
			// TODO - Llamar al servidor para guardar el cambio
		}

		function editHardwareComponent(hardwareComponent){
			hardwareComponent.$$editing = true;
			hardwareComponent.$$tmpDescription = hardwareComponent.description;
			hardwareComponent.$$tmpType = hardwareComponent.type;
		}
		function saveHardwareComponent(hardwareComponent){
			hardwareComponent.$$editing = false;
			hardwareComponent.description = hardwareComponent.$$tmpDescription;
			hardwareComponent.type = hardwareComponent.$$tmpType;
			var hcCopy = angular.copy(hardwareComponent);
			delete hcCopy.id;
			// TODO - Llamar al servidor para guardar el cambio
			HardwareComponentsResource.update({hardwareId: hardwareComponent.id}, hcCopy).then(
				function(response){
		  			toaster.pop({type: "success", title: $filter("translate")("success"),body: $filter("translate")("successfully_saved")});
		  		},
		  		function(error){
		  			toaster.pop({type: "error", title: $filter("translate")("error"),body: error});
		  		}
			);
		}

		function deleteHardwareComponent(hardwareComponent){
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
					HardwareComponentsResource.delete({hardwareId: hardwareComponent.id}).then(
						function(response){
				  			toaster.pop({type: "success", title: $filter("translate")("success"),body: $filter("translate")("successfully_deleted")});
				  			var index = vm.hardwareComponentsGroups[0].components.indexOf(hardwareComponent);
				  			if(index != -1){
				  				vm.hardwareComponentsGroups[0].components.splice(index,1);
				  			}
				  		},
				  		function(error){
				  			toaster.pop({type: "error", title: $filter("translate")("error"),body: error});
				  		}
					)
				}
			});
		}
	  };
})();