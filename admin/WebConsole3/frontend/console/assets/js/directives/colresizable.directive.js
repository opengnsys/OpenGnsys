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
      .directive('colResizable', colResizableDirective);

      colResizableDirective.$inject = ['$timeout', '$parse'];


      function colResizableDirective($timeout, $parse) {
           return {

            	link: function($scope, element, $attrs) {

            		loadColResizable();
                    $scope.$watch($attrs["ngModel"], function(newValue, oldValue){
						if (newValue){
		                	element.colResizable({disable:true});
		                	loadColResizable(newValue);
						}
	                        
                    }, true);

                    // Comprobar si existe un evento que se emitirá para forzar el refresco
                    if($attrs["crForceRefresh"]){
                    	$scope.$on($attrs["crForceRefresh"], function(event, args){
                    		/*
                    		element.colResizable({disable:true});
                    		loadColResizable(args);
                    		*/
                    		//trigger a resize event, so paren-witdh directive will be updated
							$(window).trigger('resize');
                    	});
                    }

                    function loadColResizable(array){
		      			$timeout(function() {
		                    	element.colResizable({
		                    		liveDrag: true,
							      	gripInnerHtml: "<div class='grip'></div>",
							      	draggingClass: "dragging",
							      	onDrag: function(event) {
							        	
							        	//$parse($attrs["crOnDrag"])($scope);
							        	var table = event.currentTarget;
							        	var colsWidth = [];
							        	angular.forEach(table.rows[0].cells,function(column, index){
							        		var percent = Math.round((angular.element(column).width()*100/angular.element(table).width())*100)/100;
							        		if(index < array.length){
								        		var scopeModel = array[index];
								        		scopeModel[$attrs["crUpdateProperty"]] = percent;
								        		$parse($attrs["crOnDrag"])($scope)(scopeModel, array);
								        	}
							        	});
							        	
							        	//$parse($attrs["crOnDrag"])($scope);
							      	},
							      	onResize: function(event){
							      		$scope.$apply();
							      	}
							    });
							});
		      		}
            }
        }
    };
})();
