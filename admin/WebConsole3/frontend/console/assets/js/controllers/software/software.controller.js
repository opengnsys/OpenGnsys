(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName).controller('SoftwareController', SoftwareController);

	SoftwareController.$inject = ['$rootScope', '$scope','$state', '$timeout', '$filter', 'toaster', 'ogSweetAlert', 'softwareTypes', 'softwareComponents','SoftwareProfilesResource', 'SoftwareComponentsResource', 'OGCommonService'];

	function SoftwareController($rootScope, $scope, $state, $timeout, $filter, toaster, ogSweetAlert, softwareTypes, softwareComponents, SoftwareProfilesResource, SoftwareComponentsResource, OGCommonService) {
		var vm = this;
		vm.windowsboots = $rootScope.constants.windowsboots;
		vm.softwareTypes = [];
		vm.softwareComponents = [];
		vm.softwareComponentsGroups = [];
		vm.softwareProfiles = [];
		vm.softwareProfileGroups = [];

		vm.editSoftwareProfile = editSoftwareProfile;
		vm.saveSoftwareProfile = saveSoftwareProfile;
		vm.deleteSoftwareProfile = deleteSoftwareProfile;

		vm.editSoftwareType = editSoftwareType;
		vm.saveSoftwareType = saveSoftwareType;

		vm.editSoftwareComponent = editSoftwareComponent;
		vm.saveSoftwareComponent = saveSoftwareComponent;
		vm.deleteSoftwareComponent = deleteSoftwareComponent;

		init();

		function init(){
			if($rootScope.user){
				SoftwareProfilesResource.query().then(
					function(response){
						vm.softwareProfileGroups = [
							OGCommonService.createGroups(response, "profiles")
						];
						vm.softwareProfileGroups[0].name =  $filter("translate")("software_profiles");
						
					},
					function(error){
						alert(error);
					}
				);
				vm.softwareTypes = softwareTypes;
				vm.softwareComponentsGroups = [
						OGCommonService.createGroups(softwareComponents, "components")
				];
				vm.softwareComponentsGroups[0].name = $filter("translate")("software_components");
				
			}
		}

		
		function editSoftwareProfile(softwareProfile){
			softwareProfile.$$editing = true;
			softwareProfile.$$tmpName = softwareProfile.name;
			softwareProfile.$$tmpDescription = softwareProfile.description;
			softwareProfile.$$tmpWindowsboot = softwareProfile.windowsboot;
		}
		function saveSoftwareProfile(softwareProfile){
			softwareProfile.$$editing = false;
			softwareProfile.name = softwareProfile.$$tmpName;
			softwareProfile.description = softwareProfile.$$tmpDescription;
			softwareProfile.windowsboot = softwareProfile.$$tmpWindowsboot;
			var hpCopy = angular.copy(softwareProfile);
			delete hpCopy.id;
			// TODO - Llamar al servidor para guardar el cambio
			SoftwareProfilesResource.update({profileId: softwareProfile.id}, hpCopy).then(
				function(response){
		  			toaster.pop({type: "success", title: "success",body: $filter("translate")("successfully_saved")});
		  		},
		  		function(error){
		  			toaster.pop({type: "error", title: "error",body: error});
		  		}
			);
		}

		function deleteSoftwareProfile(softwareProfile){
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
					SoftwareProfilesResource.delete({profileId: softwareProfile.id}).then(
						function(response){
				  			toaster.pop({type: "success", title: $filter("translate")("success"),body: $filter("translate")("successfully_deleted")});
				  			var index = vm.softwareProfileGroups[0].profiles.indexOf(softwareProfile);
				  			if(index != -1){
				  				vm.softwareProfileGroups[0].profiles.splice(index,1);
				  			}
				  		},
				  		function(error){
				  			toaster.pop({type: "error", title: $filter("translate")("error"),body: error});
				  		}
					)
				}
			});
		}

		function editSoftwareType(softwareType){
			softwareType.$$editing = true;
			softwareType.$$tmpName = softwareType.name;
		}
		function saveSoftwareType(softwareType){
			softwareType.$$editing = false;
			softwareType.name = softwareType.$$tmpName;
			// TODO - Llamar al servidor para guardar el cambio
		}

		function editSoftwareComponent(softwareComponent){
			softwareComponent.$$editing = true;
			softwareComponent.$$tmpDescription = softwareComponent.description;
			softwareComponent.$$tmpType = softwareComponent.type;
		}
		function saveSoftwareComponent(softwareComponent){
			softwareComponent.$$editing = false;
			softwareComponent.description = softwareComponent.$$tmpDescription;
			softwareComponent.type = softwareComponent.$$tmpType;
			var hcCopy = angular.copy(softwareComponent);
			delete hcCopy.id;
			// TODO - Llamar al servidor para guardar el cambio
			SoftwareComponentsResource.update({softwareId: softwareComponent.id}, hcCopy).then(
				function(response){
		  			toaster.pop({type: "success", title: $filter("translate")("success"),body: $filter("translate")("successfully_saved")});
		  		},
		  		function(error){
		  			toaster.pop({type: "error", title: $filter("translate")("error"),body: error});
		  		}
			);
		}

		function deleteSoftwareComponent(softwareComponent){
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
					SoftwareComponentsResource.delete({softwareId: softwareComponent.id}).then(
						function(response){
				  			toaster.pop({type: "success", title: $filter("translate")("success"),body: $filter("translate")("successfully_deleted")});
				  			var index = vm.softwareComponentsGroups[0].components.indexOf(softwareComponent);
				  			if(index != -1){
				  				vm.softwareComponentsGroups[0].components.splice(index,1);
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