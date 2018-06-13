(function(){
	'use strict';
	/**
	 * @ngdoc function
	 * @name ogWebConsole.controller:MainCtrl
	 * @description
	 * # MainCtrl
	 * Controller of the ogWebConsole
	 */
	angular.module(appName).controller('PartitionFormatController', PartitionFormatController);

	PartitionFormatController.$inject = ['$rootScope', '$scope','$state', '$timeout', '$filter', 'ogSweetAlert', 'toaster'];

	function PartitionFormatController($rootScope, $scope, $state, $timeout, $filter, ogSweetAlert, toaster) {
		var vm = this;
		vm.partitionTableTypes = ["MSDOS", "GPT"];
		vm.diskConfig = {};
		vm.partitionTypes = [];

		vm.getSizeInGB = getSizeInGB;
		vm.checkPartitionTableType = checkPartitionTableType;
		vm.addPartition = addPartition;
		vm.addExtendedPartition = addExtendedPartition;
		vm.setPartitionUsage = setPartitionUsage;
		vm.updatePartitionUsage = updatePartitionUsage;
		vm.updateExtendedPartitions = updateExtendedPartitions;
		vm.updateExtendedPartitionsUsage = updateExtendedPartitionsUsage;
		vm.checkPartitionType = checkPartitionType;
		vm.setChartData = setChartData;
		vm.getPartitionColor = getPartitionColor;
		vm.removePartition = removePartition;
		vm.removeExtendedPartition = removeExtendedPartition;
		vm.generateOgInstruction = generateOgInstruction;

		vm.isEXTENDED = isEXTENDED;
		vm.isCACHE = isCACHE;
		vm.isEFI = isEFI;

		vm.partTableTypeIsGPT = partTableTypeIsGPT;

		init();

		function init(){
			if($rootScope.user){
				// Comprobar la selección de clientes
				if($rootScope.selectedClients){

					// Recorrer todos los clientes seleccionados y usar el tamaño del disco de menor tamaño
					var clientsId = Object.keys($rootScope.selectedClients);
					var minSize = 0;
					// por defecto la tabla de particiones msdos
					var parttable = $rootScope.OGCommonService.getPartitionTable({partitionCode: 1});
					if($rootScope.selectedClients[clientsId[0]].partitions[0]){
						minSize = $rootScope.selectedClients[clientsId[0]].partitions[0].size;
						parttable = $rootScope.OGCommonService.getPartitionTable($rootScope.selectedClients[clientsId[0]].partitions[0]);
					}
					
					for(var c = 1; c < clientsId.length; c++){
						if($rootScope.selectedClients[clientsId[0]].partitions[0].size < minSize){
							minSize = $rootScope.selectedClients[clientsId[0]].partitions[0].size;

						}
					}

					vm.diskConfig = {
						disk: 1,
						parttable: parttable,
						size: minSize,
						partitions: [
							{
								partition: 0,
								type: "free_space",
								filesystem: "",
								size: minSize,
								usage: 100,
							}
						]
					};
					setChartData(vm.diskConfig);
					vm.partitionTableTypes = $rootScope.constants.partitiontable;
				}
				else{
					// TODO - dar error?
					ogSweetAlert.error($filter("translate")("opengnsys_error"), $filter("translate")("not_clients_selected"));
					$state.go("app.ous");
				}


			}
		}


		function partTableTypeIsGPT(){
			return vm.diskConfig.parttable.type == "GPT";
		}

		function partTableTypeIsMSDOS(){
			return vm.diskConfig.parttable.type == "MSDOS";
		}

		function partTableTypeIsLVM(){
			return vm.diskConfig.parttable.type == "LVM";
		}
		function partTableTypeIsZPOOL(){
			return vm.diskConfig.parttable.type == "ZPOOL";
		}

		function isEFI(partition){
			return partition.type == "EFI";
		}

		function isCACHE(partition){
			return partition.type == "CACHE";	
		}

		function isEXTENDED(partition){
			return partition.type == "EXTENDED";		
		}

		function isWINDOWS(partition){
			return partition.type == "NTFS" || partition.type == "WINDOWS";
		}

		function isLINUX(partition){
			return typeof partition.type == "string" && partition.type.includes("LINUX");
		}

		function isLINUXSWAP(partition){
			return partition.type == "LINUX-SWAP";
		}

		function isDATA(partition){
			return partition.type == "DATA";
		}

		function isUNKNOWN(partition){
			return partition.type == "UNKNOWN";
		}

		function isFreeSpace(partition){
			return partition.type == "free_space";
		}

	
		function convertPartitionType(partition){
			if(partTableTypeIsMSDOS()){
				if(isWINDOWS(partition)){
					partition.type = "NTFS";
				}
				else if(isUNKNOWN(partition)){
					partition.type = "NTFS";
				}
			}
			else if(partTableTypeIsGPT()){
				if(isWINDOWS(partition)){
					partition.type = "WINDOWS";
				}
				else if(isDATA(partition)){
					partition.type = "UNKNOWN";	
				}
			}
			else if(partTableTypeIsLVM()){
				partition.type = "LVM-LV";
			}
			else if(partTableTypeIsZPOOL()){
				partition.type = "ZFS-VOL";
			}
		}	

		function checkPartitionTableType(){
			if(partTableTypeIsMSDOS()){
				if(vm.diskConfig.partitions.length > 5){
					ogSweetAlert.info("opengnsys_info","En MS-DOS sólo puede haber 4 particiones primarias, se creará una extendida con el resto de particiones");
					var tmpPartitions = [];
					var extendedPartition = {
						type: "EXTENDED",
						partitions: [],
						size: 0,
						usage: 0
					};
					var hasCache = ($filter("filter")(vm.diskConfig.partitions, {type: "CACHE"}).length > 0);
					// Si tiene cache se añaden 2 particiones, más la cache y el espacio libre
					var numParts = hasCache?2:3;
					angular.forEach(vm.diskConfig.partitions, function(partition, index){
						convertPartitionType(partition);
						if(index < numParts || isFreeSpace(partition) || isCACHE(partition)){
							tmpPartitions.push(partition);
						}
						else{
							extendedPartition.partitions.push(partition);
							extendedPartition.size += partition.size;
						}
					});
					// Actualizar porcentajes de las particiones extendidas
					for(var p = 0; p < extendedPartition.partitions.length; p++){
						setPartitionUsage(extendedPartition, extendedPartition.partitions[p]);
					}
					tmpPartitions.push(extendedPartition);
					vm.diskConfig.partitions = tmpPartitions;
					updatePartitionUsage(vm.diskConfig.partitions[0]);
				}
				else{
					angular.forEach(vm.diskConfig.partitions, function(partition, index){
						convertPartitionType(partition);
					});
				}
			
			}
			else {
				var tmpPartitions = [];
				// Para particiones GPT se crea una particion EFI en primer lugar de 512M
				if(partTableTypeIsGPT()){
					// Comprobar si existe ya una partición EFI al principio del disco, sino, crearla
					if(!isEFI(vm.diskConfig.partitions[0])){
						tmpPartitions.push({
							type: "EFI",
							size: 512000,
							usage: (512000/vm.diskConfig.size)*100
						});
					}
				}

				angular.forEach(vm.diskConfig.partitions, function(partition, index){
					convertPartitionType(partition);
					if(!isEXTENDED(partition)){
						tmpPartitions.push(partition);
					}
					else{
						angular.forEach(partition.partitions, function(extPart,index){
							convertPartitionType(extPart);
							tmpPartitions.push(extPart);
							setPartitionUsage(vm.diskConfig, extPart);
						});
					}
				});
				vm.diskConfig.partitions = tmpPartitions;
				updatePartitionUsage(vm.diskConfig.partitions[0]);
			}
		}

		function addPartition(){
			// Si el tipo de tabla de particiones es MSDOS, sólo se admiten 4 particiones
			if(partTableTypeIsGPT() || (partTableTypeIsMSDOS() && vm.diskConfig.partitions.length < 5)){
				vm.diskConfig.partitions.push({
					partition: (vm.diskConfig.partitions.length),
					type: partTableTypeIsGPT()?"WINDOWS":"NTFS",
					filesystem: "",
					size: 0,
					usage: 5

				});
				updatePartitionUsage(vm.diskConfig.partitions[vm.diskConfig.partitions.length-1]);
			}
			else if(partTableTypeIsMSDOS()){
				ogSweetAlert.warning("opengnsys_warning","En MS-DOS sólo puede haber 4 particiones primarias, utilice alguna como extendida si necesita más particiones");
			}
			// Actualizar información
			//setChartData(vm.diskConfig);
		}

		function addExtendedPartition(partition){
			partition.partitions.push({
					partition: (partition.partitions.length+1),
					type: "NTFS",
					filesystem: "",
					size: 0,
					usage: 0

			});
			var extendedPartUsage = Math.round(100/partition.partitions.length);
			angular.forEach(partition.partitions, function(extPart, index){
				extPart.usage = extendedPartUsage;
			});
			// Actualiza tamaños en funcion del porcentaje de uso
			updateExtendedPartitions(partition);
		}

		function updateExtendedPartitions(extPartition){
			var parentPartition = $filter("filter")(vm.diskConfig.partitions, {type: "EXTENDED"})[0];
			var totalSize = parentPartition.size;
			angular.forEach(parentPartition.partitions, function(extPart, index){
				extPart.partition = (index+1);
				extPart.size = Math.round((extPart.usage||0)*totalSize/100);
			});
		}

		function updateExtendedPartitionsUsage(extPartition){
			var parentPartition = $filter("filter")(vm.diskConfig.partitions, {type: "EXTENDED"})[0];
			var index = parentPartition.partitions.indexOf(extPartition);
			var nextPart = null;
			// si solo hay una partición el uso es siempre el 100%
			if(parentPartition.partitions.length == 1){
				extPartition.usage = 100;
			}
			else{
				var nextPart = null;
				// el porcentaje que crezca la particion, se le resta a la siguiente o a la anterior si es la ultima
				if(index == parentPartition.partitions.length-1){
					nextPart = parentPartition.partitions[index-1];
				}
				else{
					nextPart = parentPartition.partitions[index+1];
				}
				var restPercent = 100;
				angular.forEach(parentPartition.partitions, function(extPart, index){
					restPercent -= (extPart.usage||0);	// Hay casos en los que se obtiene NaN
				});
				// Le quitamos el porcentaje a la particion contigua hasta que quede con un mínimo de 1
				if(nextPart.usage > (restPercent*-1)){
					nextPart.usage += restPercent;
				}
				else{
					// restamos 1 al resto del porcentaje que será lo que ocupe la particion contigua
					restPercent = Math.abs(restPercent)-(nextPart.usage-1);
					nextPart.usage = 1;
					
					extPartition.usage -= restPercent;
				}
			}
			updateExtendedPartitions(extPartition);
		}

		function removeExtendedPartition(extPartition)
		{
			var parentPartition = $filter("filter")(vm.diskConfig.partitions, {type: "EXTENDED"})[0];
			var index = parentPartition.partitions.indexOf(extPartition);
			if(index != -1){
				parentPartition.partitions.splice(index,1);
			}
			// Comprobamos el % que queda libre ahora
			var freePercent = Math.round(extPartition.usage/parentPartition.partitions.length);
			angular.forEach(parentPartition.partitions, function(extPart, index){
				extPart.usage += freePercent;
				extPart.size = Math.round(extPart.usage*parentPartition.size/100);
			});
		}

		
		function reorderPartitions(){
			var tmpPartitions = [];
			var indexFreeSpace = -1;
			var indexCache = -1;
			angular.forEach(vm.diskConfig.partitions, function(partition,index){
				if(partition.type != "free_space" && !isCACHE(partition)){
					partition.partition = (tmpPartitions.length+1)
					tmpPartitions.push(partition);
				}
				else if(isCACHE(partition)){
					indexCache = index;
				}
				else if(partition.type == "free_space"){
					indexFreeSpace = index;
				}
			});
			// Añadir el espacio libre y la cache
			if(indexFreeSpace != -1){
				vm.diskConfig.partitions[indexFreeSpace].usage = calculateFreeSpace(vm.diskConfig.partitions[indexFreeSpace]);
				tmpPartitions.push(vm.diskConfig.partitions[indexFreeSpace]);
			}
			if(indexCache != -1){
				tmpPartitions.push(vm.diskConfig.partitions[indexCache]);
			}
			vm.diskConfig.partitions = tmpPartitions;
		}

		function setChartData(diskConfig){
			
			var diskChartData = [];
			var usedSpace = 0;
			angular.forEach(diskConfig.partitions, function(partition,index){
				if(partition.size > 0){
					setPartitionUsage(diskConfig, partition);
					if(partition.type == "free_space"){
						partition.usage = calculateFreeSpace(partition);
					}
					// El espacio libre solo se añade si es 0
					if(partition.type != "free_space" || (partition.type == "free_space" && partition.usage > 0)){
						diskChartData.push({
							label: $filter("translate")(partition.os||partition.filesystem||partition.type),
							data: partition.usage,
							usage: partition.usage,
							color: getPartitionColor(partition)
						});
					}
					if(partition.type != "free_space"){
						usedSpace += partition.usage;
					}
				}
			});

			vm.diskConfig.remaining = Math.round(100*(100-usedSpace))/100;
			
			/*diskChartData.push({
				label: $filter("translate")("free_space"),
				data: vm.diskConfig.remaining,
				usage: vm.diskConfig.remaining,
				color: "#bcbcbc"
			});
			/**/
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
			var color="#c5e72b";
			// Para la partición de datos se usa un color específico
			if(isDATA(partition)){
				color="rgb(237,194,64)";
			}
			else if(isEFI(partition)){
				color="#bfe4e5";
			}
			else if(isWINDOWS(partition)){
				color="#00c0ef";
			}
			else if(isLINUXSWAP(partition)){
				color="#545454";
			}
			else if(isLINUX(partition)){
				color="#605ca8";	
			}
			else if(isCACHE(partition)){
				color="#FC5A5A";
			}
			else if(isFreeSpace(partition)){
				color="#bcbcbc";
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
				+ series.usage + "%</div>";
		}

		function getSizeInGB(size){
			size = size/(1024*1024);
			return Math.round(size*100)/100;
		}

		function setPartitionUsage(diskConfig, partition){
			partition.usage = Math.round(((partition.size*100)/diskConfig.size)*100)/100;
		}

		function checkPartitionType(partition){
			var ok = true;
			if(isCACHE(partition)){
				// Comprobar si ya hay alguna partición como CACHE
				if($filter("filter")(vm.diskConfig.partitions, {type: "CACHE"}).length > 1){
					ogSweetAlert.error("opengnsys_error","Solo debe haber una CACHE");
					partition.type = "NTFS";
					ok = false;
				}
			}
			else if(isEXTENDED(partition)){
				// Comprobar si ya hay alguna partición como EXTENDIDA
				if($filter("filter")(vm.diskConfig.partitions, {type: "EXTENDED"}).length > 1){
					ogSweetAlert.error("opengnsys_error", "Solo debe haber una EXTENDIDA");
					partition.type = "NTFS";
					ok = false;
				}
				else{
					partition.partitions = [
						{
							partition: 1,
							type: "NTFS",
							filesystem: "",
							size: partition.size,
							usage: 100

						}
					];
				}
			}
			else if(typeof partition.partitions != "undefined" && partition.partitions.length > 0){
				ok = false;
				ogSweetAlert.question("opengnsys_question", "Esta particion contiene otras partitiones!, si continua, dichas particiones serán eliminadas....",
					function(yes){
						partition.partitions = [];
						updatePartitionUsage(partition);
						$scope.$apply();
					},
					function(cancel){
						// Si contesta no se deja el tipo extendido
						partition.type = "EXTENDED";
						$scope.$apply();
					}
				);
			}

			if(ok){
				updatePartitionUsage(partition);
			}
		}

		function updatePartitionUsage(partition){
			var remaining =  calculateFreeSpace(partition);
			if(partition.usage > remaining){
				partition.usage = remaining;
			}
			partition.size = Math.round(vm.diskConfig.size*partition.usage/100);
			setChartData(vm.diskConfig);
			reorderPartitions();
			// Si es una partición extendida
			if(typeof partition.partitions !== "undefined" && partition.partitions.length > 0){
				updateExtendedPartitions(partition.partitions[0]);
				$scope.$broadcast("refresh-extended-partitions", partition.partitions);
			}
		}

		function calculateFreeSpace(asignedPartition){
			var usedSpace = 0;
			angular.forEach(vm.diskConfig.partitions, function(partition,index){
				if(partition != asignedPartition && partition.type != "free_space"){
					usedSpace += partition.usage||0;
				}
			});
			return  Math.round(100*(100-usedSpace))/100;
		}

		function removePartition(partition){
			var index = vm.diskConfig.partitions.indexOf(partition);
			if(index != -1){
				vm.diskConfig.partitions.splice(index,1);
			}

			setChartData(vm.diskConfig);

		}


//var RC='@';
//document.fdatosejecucion.atributos.value="scp="+escape(document.fdatos.codigo.value)+RC;


		/**/
		function generateOgInstruction(){
			var initPartitionTable = "ogCreatePartitionTable "+vm.diskConfig.disk+" "+vm.diskConfig.parttable.type+"\n";
			initPartitionTable += 'ogEcho log session "[0]  $MSG_HELP_ogCreatePartitions"\n';
 			initPartitionTable += 'ogEcho session "[10] $MSG_HELP_ogUnmountAll '+vm.diskConfig.disk+'"\n';
 			initPartitionTable += "ogUnmountAll " + vm.diskConfig.disk + " 2>/dev/null\n";
 			initPartitionTable += "ogUnmountCache\n";
 			initPartitionTable += 'ogEcho session "[30] $MSG_HELP_ogUpdatePartitionTable '+vm.diskConfig.disk+'"\n';
 			initPartitionTable += "ogDeletePartitionTable "+vm.diskConfig.disk+"\n"; 
 			initPartitionTable += "ogUpdatePartitionTable "+vm.diskConfig.disk+"\n";

 			var createPartitions = 'ogEcho session "[60] $MSG_HELP_ogListPartitions '+vm.diskConfig.disk+'"\n';
 			createPartitions += 'ogExecAndLog command session ogListPartitions '+vm.diskConfig.disk+'\n'; 

			var cacheInstruction = "";
			var partitionList = "";
			var formatInstructions = "";
			angular.forEach(vm.diskConfig.partitions, function(partition, index){
				if(partition.type != "free_space"){
					// La unica particion especial es la 4 que es cache, para el resto
					if(!isCACHE(partition)){
						partitionList += " "+partition.type + ":" + partition.size;
						if(isEXTENDED(partition)){
							for(var p = 0; p < partition.partitions.length; p++){
								partitionList += " "+partition.partitions[p].type + ":" + partition.partitions[p].size;
								if(partition.partitions[p].format == true){
									formatInstructions += "ogUnmount "+ vm.diskConfig.disk + " " + (partition.partition + (partition.partitions[p].partition - 1)) + "\n";
									formatInstructions += "ogFormat "+ vm.diskConfig.disk + " " + (partition.partition + (partition.partitions[p].partition - 1)) + "\n";
								}
							}
						}
						if(partition.format == true){
							formatInstructions += "ogUnmount "+ vm.diskConfig.disk + " " + partition.partition + "\n";
							formatInstructions += "ogFormat "+ vm.diskConfig.disk + " " + partition.partition + "\n";
						}
					}
					else{
						cacheInstruction = 'ogEcho session "[50] $MSG_HELP_ogCreateCache"\n';
						cacheInstruction += 'initCache '+vm.diskConfig.disk + " "+partition.size + ' NOMOUNT &>/dev/null\n';

						if(partition.format == true){
							formatInstructions += "ogUnmountCache\n";
							formatInstructions += "ogFormatCache\n";
						}
					}
				}
			});

			createPartitions += 'ogEcho session "[70] $MSG_HELP_ogCreatePartitions   '+partitionList+'"\n';
			createPartitions += "ogExecAndLog command ogCreatePartitions "+vm.diskConfig.disk + partitionList+"\n";
   			createPartitions += 'ogEcho session "[80] $MSG_HELP_ogSetPartitionActive '+vm.diskConfig.disk+' 1"\n'
   			createPartitions += "ogSetPartitionActive "+vm.diskConfig.disk+" 1\n";
 			createPartitions += 'ogEcho log session "[100] $MSG_HELP_ogListPartitions '+vm.diskConfig.disk+'"\n';
 			createPartitions += "ogUpdatePartitionTable "+vm.diskConfig.disk+"\n";
 			createPartitions += "ms-sys /dev/sda | grep unknow && ms-sys /dev/sda\n";
 			createPartitions += "ogExecAndLog command session log ogListPartitions "+vm.diskConfig.disk+"\n";

 			$rootScope.OGCommandsService.ogInstructions = initPartitionTable+cacheInstruction+createPartitions+formatInstructions;
		}

	  };
})();