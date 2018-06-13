(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName).controller('EditClientController', EditClientController);

	EditClientController.$inject = ['$rootScope', '$scope','$state', '$timeout', 'toaster', 'ClientsResource', 'RepositoriesResource', 'HardwareProfilesResource', 'NetbootsResource', 'OGCommonService'];

	function EditClientController($rootScope, $scope, $state, $timeout, toaster, ClientsResource, RepositoriesResource, HardwareProfilesResource, NetbootsResource, OGCommonService) {
		var vm = this;
		vm.client = {};
		vm.getSizeInGB = getSizeInGB;
		vm.setPartitionPercentOfDisk = setPartitionPercentOfDisk;
		vm.setChartData =  setChartData;
		vm.repositories = [];
		vm.hardwareProfiles = [];
		vm.netboots = [];
		vm.save = save;

		init();


		function init(){
			if($rootScope.user){
				loadFormOptions();
				loadClient();
				loadRepositories();
				loadNetboots();
				if(!$rootScope.hardwareProfiles){
					HardwareProfilesResource.query({ouid: $rootScope.user.ou.id}).then(
						function(response){
							vm.hardwareProfiles = response;
							$rootScope.hardwareProfiles = response;
						},
						function(error){
							alert(error);
						}
					);
				}else{
					vm.hardwareProfiles = $rootScope.hardwareProfiles;
				}
			}
		}

		function loadRepositories(){
			if(!$rootScope.repositories){
				//{ouid: $rootScope.user.ou.id}
				RepositoriesResource.query().then(
					function(result){
						vm.repositories = result;
					},
					function(error){
						alert(error);
					}
				);
			}
			else{
				vm.repositories = $rootScope.repositories;
			}
		};

		function loadFormOptions(){
			ClientsResource.options().then(
				function(result){
					vm.formOptions = result;
				}
			);
		};

		function loadClient(){
			ClientsResource.get({ouid: $rootScope.user.ou.id, labId: $state.params.labId, clientId: $state.params.clientId}).then(
				function(response){
					vm.client = response;
					vm.client.oglive = {
						directory: vm.client.oglive
					}
					// La partición 0 es la configuración del disco
					vm.client.diskConfig = OGCommonService.getDisksConfigFromPartitions(vm.client.partitions);

					// Aplicamos las transformaciones oportunas para calcular porcentajes
					angular.forEach(vm.client.diskConfig, function(diskConfig,index){
						setChartData(diskConfig);
					});
					// Seleccionarlo para ejecutar comandos
					$rootScope.selectedClients = $rootScope.selectedClients || [];
					vm.client.selected = true;
					$rootScope.selectedClients[vm.client.id] = vm.client;

				},
				function(error){
					alert(error);
				}
			);
		}

		function loadNetboots(){
			NetbootsResource.query().then(
				function(result){
					vm.netboots = result;
				},
				function(error){
					toaster.pop({type: "error", title: "error",body: error});
				}
			);
		}

		function save(){
  			var result = $rootScope.ValidateFormsService.validateForms(vm);

            // Si no hubo ningun error se guardan todas las pgms
           	if(result == true){
           		// Si se indicó un padre en la url, se añade dicha propiedad
           		var clientCopy = JSON.parse(angular.toJson(vm.client));
           		clientCopy.repository = (clientCopy.repository)?clientCopy.repository.id:null;
           		clientCopy.hardwareProfile = (clientCopy.hardwareProfile)?clientCopy.hardwareProfile.id:null;
           		clientCopy.oglive = (clientCopy.oglive)?clientCopy.oglive.directory:null;
           		clientCopy.netboot = (clientCopy.netboot)?clientCopy.netboot.id:null;
           		// TODO - quitar
           		delete clientCopy.id;
           		delete clientCopy.diskConfig;
           		delete clientCopy.partitions;
           		delete clientCopy.status;
           		delete clientCopy.selected;

	  			ClientsResource.update({clientId: vm.client.id}, clientCopy).then(
		  			function(response){
		  				$state.go("app.ous",{},{reload: true});
			  			$timeout(function(){
			  				toaster.pop({type: "success", title: "success",body: "Successfully saved"});
			  			},0);
			  			
			  		},
			  		function(error){
			  			toaster.pop({type: "error", title: "error",body: error});
			  		}
		  		);
	  		}
  		}



		function setChartData(diskConfig){
			var diskChartData = [];
			angular.forEach(diskConfig.partitions, function(partition,index){
				if(partition.size > 0){
					setPartitionPercentOfDisk(diskConfig, partition);
					diskChartData.push({
						label: partition.os||partition.filesystem,
						data: partition.percentOfDisk,
						percentOfDisk: partition.percentOfDisk,
						color: getPartitionColor(partition)
					});
				}
			});
			var diskChartOptions = {
				series: {
					pie: {
						show: true,
						radius: 1,
						innerRadius: 0.5,
						label: {
							show: true,
							radius: 2 / 3,
							formatter: labelFormatter,
							threshold: 0.05
						}
					}
				},
				legend: {
					show: true
				}
		    };

		    diskConfig.diskChartData = diskChartData;
		    diskConfig.diskChartOptions = diskChartOptions;
		}

		function getPartitionColor(partition){
			var color="";
			// Para la partición de datos se usa un color específico
			if(partition.osName == "DATA"){
				color="rgb(237,194,64)";
			}
			else if(partition.filesystem == "NTFS"){
				color="#00c0ef";
			}
			else if(partition.filesystem.match("EXT")){
				color="#605ca8";	
			}
			else if(partition.filesystem.match("LINUX-SWAP")){
				color="#545454";
			}
			else if(partition.filesystem.match("CACHE")){
				color="#FC5A5A";
			}
			return color;
		}

		/*
		* Custom Label formatter
		* ----------------------
		*/
		function labelFormatter(label, series) {
			return '<div style="font-size:13px; text-align:center; padding:2px; color: #000; font-weight: 600;">'
				+ "<br>"
				+ series.percentOfDisk + "%</div>";
		}

		function getSizeInGB(size){
			size = size/(1024*1024);
			return Math.round(size*100)/100;
		}

		function setPartitionPercentOfDisk(diskConfig, partition){
			partition.percentOfDisk = Math.round(((partition.size*100)/diskConfig.size)*100)/100;
		}

	  };
})();