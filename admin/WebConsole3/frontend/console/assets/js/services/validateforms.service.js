(function(){
	'use strict';
	angular.module(appName)
	.service("ValidateFormsService", ValidateFormsService);

	ValidateFormsService.$inject = ["$filter", '$q', "$rootScope", "$parse", "toaster"];

	function ValidateFormsService($filter, $q, $rootScope, $parse, toaster){
		var self = this;

		self.validateForm = validateForm;
		self.validateForms = validateForms;

		return self;

		function validateForm(form){
			var result = true;
            var firstError = null;
            if (form.$invalid) {
                var field = null, firstError = null;
                for (field in form) {
                    if (field[0] != '$') {
                        if (firstError === null && !form[field].$valid) {
                            firstError = form[field].$name;
                        }
                        if (form[field].$pristine) {
                            form[field].$dirty = true;
                        }
                    }
                }
                angular.element('.ng-invalid[name=' + firstError + ']').focus();
                toaster.pop({type: "error", title: "error",body: firstError});

                result = false;
            }
	        return result;
		}
		function validateForms(vm){
			var result = true;
            var firstError = null;
            var forms = angular.element("#content form");
            for(var p = 0; p < forms.length; p++){
            	var form = $parse(angular.element(forms[p]).attr("name"))({vm: vm});
            	 if (form.$invalid) {
	                var field = null, firstError = null;
	                for (field in form) {
	                    if (field[0] != '$') {
	                        if (firstError === null && !form[field].$valid) {
	                            firstError = form[field].$name;
	                        }
	                        if (form[field].$pristine) {
	                            form[field].$dirty = true;
	                        }
	                    }
	                }
	                angular.element('.ng-invalid[name=' + firstError + ']').focus();
	                toaster.pop({type: "error", title: "error",body: firstError});

	                result = false;
	            }

            }
            return result;
		}
		
	}

})();