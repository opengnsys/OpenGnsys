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
      .directive('fixedToolboxbar', fixedToolboxbar);

      fixedToolboxbar.$inject = ['$window', '$parse'];


      function fixedToolboxbar($window, $parse) {

           return {
            link: function($scope, element, $attrs, ngModel) {
                    var fixClass = $attrs.fixClass || 'fixed';
                    var headerClass = $attrs.headerClass || 'main-header';
                    // Grab as much info as possible 
                      // outside the scroll handler for performace reasons.
                    var header             = document.querySelector('.'+headerClass);
                    var headerHeight = 0;
                    var titleHeight = 0;
                    if(header){
                      headerHeight = window.getComputedStyle(header).height.split('px')[0];
                      titleHeight = window.getComputedStyle(element[0]).height.split('px')[0];
                      // Scroll handler to toggle classes.
                      window.addEventListener('scroll', stickyScroll, false);
                    }

                    function stickyScroll(e) {
                      if( window.pageYOffset > (headerHeight - titleHeight ) / 2 ) {
                        element.addClass(fixClass);
                      }

                      if( window.pageYOffset == 0 || window.pageYOffset < (headerHeight - titleHeight ) / 2 ) {
                        element.removeClass(fixClass);
                      }
                    }

            }
        }
    };
})();
 