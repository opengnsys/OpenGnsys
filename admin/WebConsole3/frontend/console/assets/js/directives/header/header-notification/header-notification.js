'use strict';

/**
 * @ngdoc directive
 * @name izzyposWebApp.directive:adminPosHeader
 * @description
 * # adminPosHeader
 */
angular.module('ogWebConsole')
	.directive('headerNotification',function(){
		return {
        templateUrl:'js/directives/header/header-notification/header-notification.html',
        restrict: 'E',
        replace: true,
    	}
	});


