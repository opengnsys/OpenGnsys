(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName).controller('EditNetbootController', EditNetbootController);

	EditNetbootController.$inject = ['$rootScope', '$filter','$state', '$timeout', 'toaster', 'NetbootsResource'];

	function EditNetbootController($rootScope, $filter, $state, $timeout, toaster, NetbootsResource) {
		var vm = this;
		vm.netboot = {};
		vm.save = save;

		init();


		function init(){
			if($rootScope.user){
				loadFormOptions();
				loadNetboot();
			}
		}

		function loadFormOptions(){
			NetbootsResource.options().then(
				function(result){
					vm.formOptions = result;
				}
			);
		};

		function loadNetboot(){
			NetbootsResource.get({netbootId: $state.params.netbootId}).then(
				function(response){
					vm.netboot = response;
				},
				function(error){
					alert(error);
				}
			);
		}

		function save(){
  			var result = $rootScope.ValidateFormsService.validateForms(vm);

            // Si no hubo ningun error se guardan todas las pgms
           	if(result == true){
           		var id = vm.netboot.id;
           		delete vm.netboot.id;
           		// Si se indicó un padre en la url, se añade dicha propiedad
	  			NetbootsResource.update({netbootId: id}, vm.netboot).then(
		  			function(response){
		  				$state.go("app.netboot",{},{reload: true});
			  			$timeout(function(){
			  				toaster.pop({type: "success", title: $filter("translate")("success"),body: $filter("translate")("successfully_saved")});
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