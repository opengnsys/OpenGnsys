(function(){
	'use strict';

	angular.module(appName)
	.factory('ogSweetAlert', ogSweetAlert)
	.directive('ogSweetAlertScope', ogSweetAlertScope);

	ogSweetAlert.$inject = [ '$rootScope', '$filter', '$compile', '$parse', '$timeout'];
	ogSweetAlertScope.$inject = ['$window', '$parse'];

	function ogSweetAlert($rootScope, $filter, $compile, $parse, $timeout ) {

		var swal = window.swal;

		//public methods
		var self = {

			swal: function ( arg1, arg2, arg3, options ) {
				options = options||{};
				$rootScope.$evalAsync(function(){
					if( typeof(arg2) === 'function' ) {
						// Si se indicó un scope y texto html, se compila para que angular tome el control
						if(options.scope && arg1.html){
							options.scope = angular.extend($rootScope.$new(), options.scope);
							arg1.onBeforeOpen = function(swalContainer){
								var contentContainer = angular.element(swalContainer).children("#swal2-content");
								var innerHtml = $compile(arg1.html)(options.scope);
								contentContainer.html("");
								contentContainer.html(innerHtml);
							}
						}
						swal( arg1).then(
							function(isConfirm){
								$rootScope.$evalAsync( function(){
									arg2(isConfirm, options.scope);
								});
							}, arg3 );
					} else {
						swal(arg1).then(arg2, arg3 );
					}
				});
			},
			success: function(title, message) {
				$rootScope.$evalAsync(function(){
					swal( title, message, 'success' );
				});
			},
			error: function(title, message) {
				$rootScope.$evalAsync(function(){
					swal( title, message, 'error' );
				});
			},
			warning: function(title, message) {
				$rootScope.$evalAsync(function(){
					swal( title, message, 'warning' );
				});
			},
			info: function(title, message) {
				$rootScope.$evalAsync(function(){
					swal( title, message, 'info' );
				});
			},
			question: function(title, message, okcallback, cancelcallback){
				$rootScope.$evalAsync(function(){
					swal({
		  			   title: title,
					   text: message,
					   type: "info",
					   showCancelButton: true,
					   cancelButtonText: $filter("translate")("no"),
					   cancelButtonClass: "default",
					   confirmButtonClass: "primary",
					   confirmButtonText: $filter("translate")("yes")

			  		}).then(okcallback, cancelcallback);
		  		});
			},
			showInputError: function(message) {
				$rootScope.$evalAsync(function(){
		      swal.showInputError( message );
		    });
			},
			close: function() {
				$rootScope.$evalAsync(function(){
		        swal.close();
		    });
			}
		};

		return self;
	};

	function ogSweetAlertScope($window, $parse) {

           return {
            link: function($scope, element, $attrs, ngModel) {
            		console.log("");
            }
        }
    };


})();