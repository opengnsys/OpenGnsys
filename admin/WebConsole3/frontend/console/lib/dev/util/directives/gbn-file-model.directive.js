(function(){
    'use strict';
    /** 
     * globunet-v-size directive.
     */
    angular.module("globunet.utils")
    .directive('gbnFileModel', gbnFileModel);

    gbnFileModel.$inject = ['$rootScope', '$compile', '$interpolate', '$window'];
    function gbnFileModel($rootScope, $compile, $interpolate, $window) {

        return {
        	restrict: 'A',
        	scope: {
	            gbnFileModel: "="
	        },
            link: function (scope, element, attributes) {
            	scope.loaded = "0";
            	var progressBar = '<input type="file" placeholder="{{\'file\'|translate}}" name="attachment">\
            	<uib-progressbar value="loaded" type="success">\
					{{loaded}}%\
				</uib-progressbar>';

            	element.html(progressBar);
    			$compile(element.contents())(scope);

    			// Asignar el evento de cambio de fichero al input correspondiente
    			var inputFile = angular.element(attributes.inputSelector);

				inputFile.bind("change", function (changeEvent) {
				    scope.$apply(function () {

				    	if(attributes.multiple){
							// or all selected files:
					        scope.gbnFileModel = changeEvent.target.files;
				    	}
				    	else{
				        	scope.gbnFileModel = changeEvent.target.files[0];
					    }
					    if(attributes.fileData == "true"){
					    	var files = [];
					    	if(!scope.gbnFileModel.length){
					    		files.push(scope.gbnFileModel);
					    	}
					    	else{
					    		files = scope.gbnFileModel;
					    	}
					    	for(var index = 0; index < files.length; index++){
					    		var file = files[index];
							    // Comprobar si debemos pasarlo a datos
						    	var reader = new FileReader();
				                reader.onload = function (loadEvent) {
				                    scope.$apply(function () {
				                        file.data = loadEvent.target.result;
				                    });
				                }
				                reader.onprogress = function(progressEvent){
				                	scope.loaded = Math.round(progressEvent.loaded/progressEvent.total*100);
				                }
				                reader.readAsDataURL(file);
				            }
			            }
				    });
				});                
            }
        };
    };
})();