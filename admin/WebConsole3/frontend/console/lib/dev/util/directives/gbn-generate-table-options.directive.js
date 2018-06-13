(function(){
	'use strict';
	angular.module("globunet.utils")
	.directive("generateTableOptions", generateTableOptions);
	
	generateTableOptions.$inject = ['GbnNgTableServiceConfig', '$filter', '$parse', '$templateCache', 'ngTableParams', 'toaster', 'SweetAlert'];
	function generateTableOptions(GbnNgTableServiceConfig, $filter, $parse, $templateCache, ngTableParams, toaster, SweetAlert){

		var template = "";
		var optionTemplates = {};
		var optionFunctions = ["delete"];
		var arrayName = "";

		var getTemplateForOptions = function(options){
			var template = "<div>";

			for(var option = 0; option < options.length; option++){
				if(typeof options[option] === "string"){
					var genFunction = GbnNgTableServiceConfig["get"+options[option].capitalizeFirstLetter()+"Template"];
					if(typeof genFunction === "function"){
						template += genFunction(arrayName);
					}
					else if(optionTemplates[options[option]]){
						template += optionTemplates[options[option]];
					}
				}
			}
			return template+"</div>";
		}


		var generateOptionsTemplate = function(gbnOptions){
			gbnOptions = gbnOptions||{};
			var templateName = gbnOptions.templateName||"gbn-table-options.html";
			var tableOptions = gbnOptions.tableOptions || GbnNgTableServiceConfig.tableOptions;
			arrayName = gbnOptions.arrayName;
			template = getTemplateForOptions(tableOptions);
			$templateCache.put(templateName, template);
		}

		return {
			restrict: "A",
			template: template,
			link: function($scope, $element, $attributes){
				// Preparar las opciones para renderizar las opciones
				if($scope[$attributes.tableOptions]){
					var options = {};
					options.tableOptions = [];
					options.resource = $scope[$attributes.tableOptions].resource;
					options.arrayName = $attributes.arrayName;
					options.templateName = $attributes.generateTableOptions;

					options.tableOptions = $scope[$attributes.tableOptions].options;
				
					for(var index  = 0; index < $scope[$attributes.tableOptions].options.length; index++){
						var op = $scope[$attributes.tableOptions].options[index];
						if($scope[$attributes.tableOptions].templates && $scope[$attributes.tableOptions].templates[op]){
							optionTemplates[op] = $scope[$attributes.tableOptions].templates[op];
						}
						// Si la opcion actual es delete y no existe en el scope ninguna funcion definida, se crea por defecto
						if(op === "delete"){
							// La opcion delete lleva asociada una función por defecto, si no existe en el scope se crea
							if(typeof $scope[GbnNgTableServiceConfig.getDeleteFunctionName(options.arrayName)] !== "function"){
								$scope[GbnNgTableServiceConfig.getDeleteFunctionName(options.arrayName)] = function(id){
									var customClass = GbnNgTableServiceConfig.getCustomErrorDialogClass() ||"";
									SweetAlert.swal({
							            title: "Are you sure?",
							            text: "Your will not be able to recover this imaginary file!",
							            type: "warning",
							            showCancelButton: true,
							            confirmButtonColor: "#DD6B55",
							            confirmButtonText: "Yes, delete it!",
							            cancelButtonText: "No, cancel plx!",
							            closeOnConfirm: true,
							            closeOnCancel: true,
							            customClass: customClass
							        }, function (isConfirm) {
							            if (isConfirm) {
							              $scope[$attributes.tableOptions].resource.delete(id).then(
							                function(sucess){
							                	// Mostrar success generico
					                            var title = $filter('gbnToUTF8')($filter('translate')('globunet.GbnNgTableService.successTitle'));
					                            var message = $filter('gbnToUTF8')($filter('translate')('globunet.GbnNgTableService.successSavingMessage'));
					                            toaster.pop('success', title, message);
					                            // Quitar de la lista el elemento borrado
					                            var index = $scope[$attributes.arrayName].indexOfByKey({id: id},"id");
					                            if(index != -1){
					                            	$scope[$attributes.arrayName].splice(index,1);
					                            }
					                            if(typeof $scope.reloadTable === "function"){
					                            	$scope.reloadTable();
					                            }
							                },
							                function(error){
							                	// Mostrar success generico
					                            var title = $filter('gbnToUTF8')($filter('translate')('globunet.GbnNgTableService.errorTitle'));
					                            var message = $filter('gbnToUTF8')($filter('translate')('globunet.GbnNgTableService.errorDeletingMessage'));
					                            toaster.pop('error', title, message);
							                }
							              );
							                
							            } 
					        		});
								}
							}
							
						}
					}

					

					template = generateOptionsTemplate(options);
				}
			}
		};
	};
})();
