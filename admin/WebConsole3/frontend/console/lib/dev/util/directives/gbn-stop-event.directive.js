(function(){
    'use strict';
    /** 
     * gbn-stop-event directive.
     */
    angular.module("globunet.utils")
    .directive('gbnStopEvent', gbnStopEvent);

    gbnStopEvent.$inject = ["$rootScope"];
    function gbnStopEvent($rootScope) {
        
        return {
        restrict: 'A',
        	link: function(scope, element, attr) {
            	element.on(attr.gbnStopEvent, function(e) {
                	e.stopPropagation();
                	e.stopImmediatePropagation();
            	});
        	}
    	};
    };
})();