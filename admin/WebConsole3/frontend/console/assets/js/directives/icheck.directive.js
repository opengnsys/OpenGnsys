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
      .directive('icheck', icheckDirective);

      icheckDirective.$inject = ['$timeout', '$parse'];


      function icheckDirective($timeout, $parse) {

           return {
            require: 'ngModel',
            link: function($scope, element, $attrs, ngModel) {
                return $timeout(function() {
                    var value;
                    value = $attrs['value'];

                    $scope.$watch($attrs['ngModel'], function(newValue){
                        $(element).iCheck('update');
                    });

                    $attrs.checkboxClass = $attrs.checkboxClass || 'icheckbox_flat-aero';
                    $attrs.radioClass = $attrs.radioClass || 'iradio_flat-aero';

                    return $(element).iCheck({
                        checkboxClass: $attrs.checkboxClass,
                        radioClass: $attrs.radioClass

                    }).on('ifChanged', function(event) {
                        if ($(element).attr('type') === 'checkbox' && $attrs['ngModel']) {
                            $scope.$apply(function() {
                                return ngModel.$setViewValue(event.target.checked);
                            });
                        }
                        if ($(element).attr('type') === 'radio' && $attrs['ngModel']) {
                            return $scope.$apply(function() {
                                return ngModel.$setViewValue(value);
                            });
                        }
                    });
                });
            }
        }
    };
})();
 