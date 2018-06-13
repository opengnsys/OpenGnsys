(function(){
    'use strict';
    /** 
     * globunet-v-size directive.
     */
    angular.module("globunet.utils")
    .directive('gbnVSize', gbnVSize);

    gbnVSize.$inject = ['$rootScope', '$compile', '$interpolate', '$window'];
    function gbnVSize($rootScope, $compile, $interpolate, $window) {
        
        var setElementHeight = function(element, attributes){
            var self =  this;

            // Comprobar si se especificó el atributo gbn-v-size-children, en tal caso la altura se asigna a los hijos
            if(typeof attributes.gbnVSizeChildren !== "undefined"){
                var selector = attributes.gbnVSizeChildren;
                var height = element.height();
                angular.forEach(element.find("."+selector+""),function(elem, index){
                   angular.element(elem).css("height",height);
                });

            }
            else{
                var screenHeight = $window.innerHeight;
                var height = screenHeight;
                // Comprobar los atributos para asignar el height del elemento
                if(attributes.gbnVSizeFromBottom){
                    
                    // Si es un porcentaje, quiere decir aplicar respecto a la altura de la pantalla
                    if(/[0-9]+%/.test(attributes.gbnVSizeFromBottom)){

                    }
                    // Calcular altura de la pantalla y restar lo que nos llegue
                    else if(/[0-9]+/.test(attributes.gbnVSizeFromBottom)){
                        height = screenHeight - parseFloat(attributes.gbnVSizeFromBottom);
                    }
                }
                // Aplicar algura calculada al elemento
                element.css("height", height+"px");
            }
        }

        return {
            link: function (scope, element, attributes) {
                setElementHeight(element, attributes);
                // Añadimos un watcher al evento de redimensionar para redimensionar el elemento
                angular.element($window).bind("resize", function (event,window) {
                    setElementHeight(element, attributes);
                });
                
            }
        };
    };
})();
