(function(){
	'use strict';
	/**
	 * @ngdoc directive
	 * @name gbnGenerateTableParams
	 * @module globunet.utils
	 * @restrict E
	 *
	 * @description
	 * Esta directiva genera de forma automatica los parametros necesarios para una ng-table así como su función para recargar los datos, ordenar y filtrar
	 * a partir del modelo.
	 * @param search-model {string} variable del scope que actua como campo de búsqueda
	 * @param filter-cols {array} Columnas a filtrar mediante search-model
	 * @param array {string} nombre del array del que se obtendrán los datos (debe estar en el scope)
	 *
	 */
	angular.module("globunet.utils")
	.directive("gbnGenerateTableParams", GbnGenerateTableParams);


	GbnGenerateTableParams.$inject = ['$filter','$parse', '$templateCache', 'ngTableParams'];

	function GbnGenerateTableParams($filter, $parse, $templateCache, ngTableParams){

		return {
			restrict: "E",
			link: function($scope, $element, $attributes){
				var arrayObject = null;
				var tableParams = $attributes.tableParams||"tableParams";
				var reloadFunction = "reload"+tableParams.toUpperCase();

				// Creamos la funcion reloadTable para recargar los datos
				function reloadTable(tableParams){
					if($scope[tableParams]){
						$scope[tableParams].reload();
					}
				}
				// Campo de búsqueda
				if($attributes.searchModel){
					$scope.$watch($attributes.searchModel, function(n,o){
						reloadTable(tableParams);
					});
				}
				// Columnas a filtrar
				var props = [];
				if($attributes.filterCols){
					props = $parse($attributes.filterCols)($scope);
				}

				var array = $attributes.array;
				arrayObject = $parse($attributes.array)($scope)
				var initialSize = arrayObject?arrayObject.length:0;

				$scope.$watchCollection($attributes.array, function(n,o){
					arrayObject = n;
					reloadTable(tableParams);
				});


				$scope[tableParams] = new ngTableParams({
			        page: 1, // show first page
			        count: 10, // count per page
			        filter: props
			    }, {
			        total: initialSize, // length of data
			        getData: function ($defer, params) {
			        	var orderedData = [];
			        	if(arrayObject){
				            // use build-in angular filter
				            // Ordenar los datos
				            if(Array.isArray(arrayObject)){
					            orderedData = params.sorting() ? $filter('gbnNestedOrderBy')(arrayObject, params.orderBy()) : arrayObject;
					        }
				            // Filtrar los datos
				            // Construir el filtro si existe una fuente para ello
				            var filter = {};
				            /**/
				            if($attributes.searchModel){
				            	// Si no hay propiedades se filtrarían todos los atributos del objeto
				            	if(props){
						            for(var prop = 0; prop < props.length; prop++){
						            	filter[props[prop]] = $parse($attributes.searchModel)($scope);
						            }
						        }
						        else{
						        	filter = $parse($attributes.searchModel)($scope);
						        }
					        }
					        /**/
				            orderedData = params.filter() ? $filter('gbnNestedFilter')(orderedData, filter) : orderedData;

				            orderedData = orderedData.slice((params.page() - 1) * params.count(), params.page() * params.count());
				            params.total(arrayObject.length);
				        }
			            // set total for recalc pagination
			            $defer.resolve(orderedData);
			        }
			    });

			}
		};
	};
})();
