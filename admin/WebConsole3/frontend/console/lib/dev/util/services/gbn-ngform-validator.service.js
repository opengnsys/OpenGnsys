(function(){
	'use strict';
	angular.module("globunet.utils")
    .service("GbnNgFormValidatorService", GbnNgFormValidatorService);

    GbnNgFormValidatorService.$inject = ['$filter','toaster'];
    function GbnNgFormValidatorService($filter, toaster){

		this.validate = function(form, showErrorToaster, errorTitle, errorMessage, successCallback, errorCallback) {
			var result = true;
            var firstError = null;
            if (form.$invalid) {
                var field = null, firstError = null;
                for (field in form) {
                    if (field[0] != '$') {
                        if (firstError === null && !form[field].$valid) {
                            firstError = form[field];
                        }
                        if (form[field].$pristine) {
                            form[field].$dirty = true;
                        }
                    }
                }
                angular.element('.ng-invalid[name=' + firstError.$name + ']').focus();
                var fieldName = $filter("gbnToUTF8")($filter("translate")(firstError.$name));
                // Buscamos si existe un label
                var label = angular.element('.ng-invalid[name=' + firstError.$name + ']').siblings('label');
                if(label.length > 1){
                    fieldName = label[0].text();
                }
                var fieldError = $filter("gbnToUTF8")($filter("translate")(Object.keys(firstError.$error)[0]));

                // Si se quiere mostrar un toaster con el error generico
                if(showErrorToaster == true){
                	var title = errorTitle||$filter('gbnToUTF8')($filter('translate')('globunet.GbnNgFormValidatorService.errorTitle'));
                	var message = errorMessage || $filter('gbnToUTF8')($filter('translate')('globunet.GbnNgFormValidatorService.errorMessage',{field: fieldName,error: fieldError}));
                	toaster.pop('error', title, message);
                }
                result = false;
                if(typeof errorCallback === "function"){
                	errorCallback(firstError);
                }
            }

            if(result == true && typeof successCallback === "function"){
            	successCallback();
            }

            return result;
        }

        this.validateAndSave = function(form, object, Resource, showErrorToaster, errorTitle, errorMessage, successCallback, errorCallback){
            var self = this;
            if(self.validate(form,showErrorToaster, errorTitle, errorMessage) == true){
                Resource.save(object).then(
                    function(success){
                        // Si hay funcion para llamar se llama, sino, no hacemos nada
                        if(typeof successCallback == "function"){
                            successCallback(success);
                        }
                        else{
                            // Mostrar success generico
                            var title = errorTitle||$filter('gbnToUTF8')($filter('translate')('globunet.GbnNgFormValidatorService.successTitle'));
                            var message = errorMessage || $filter('gbnToUTF8')($filter('translate')('globunet.GbnNgFormValidatorService.successSavingMessage'));
                            toaster.pop('success', title, message);
                        }
                    },
                    function(error){
                        if(typeof errorCallback == "function"){
                            errorCallback(error);
                        }
                        else{
                            // Mostrar error generico
                            var title = errorTitle||$filter('gbnToUTF8')($filter('translate')('globunet.GbnNgFormValidatorService.errorTitle'));
                            var message = errorMessage || $filter('gbnToUTF8')($filter('translate')('globunet.GbnNgFormValidatorService.errorSavingMessage'));
                            toaster.pop('error', title, message);
                        }
                    }
                )
            }
        }

        this.validateAndUpdate = function(form, object, Resource, showErrorToaster, errorTitle, errorMessage, successCallback, errorCallback){
            var self = this;
            if(self.validate(form,showErrorToaster, errorTitle, errorMessage) == true){
                Resource.update({id: object.id}, object).then(
                    function(success){
                        // Si hay funcion para llamar se llama, sino, no hacemos nada
                        if(typeof successCallback == "function"){
                            successCallback(success);
                        }
                        else{
                            // Mostrar success generico
                            var title = errorTitle||$filter('gbnToUTF8')($filter('translate')('globunet.GbnNgFormValidatorService.successTitle'));
                            var message = errorMessage || $filter('gbnToUTF8')($filter('translate')('globunet.GbnNgFormValidatorService.successSavingMessage'));
                            toaster.pop('success', title, message);
                        }
                    },
                    function(error){
                        if(typeof errorCallback == "function"){
                            errorCallback(error);
                        }
                        else{
                            // Mostrar error generico
                            var title = errorTitle||$filter('gbnToUTF8')($filter('translate')('globunet.GbnNgFormValidatorService.errorTitle'));
                            var message = errorMessage || $filter('gbnToUTF8')($filter('translate')('globunet.GbnNgFormValidatorService.errorSavingMessage'));
                            toaster.pop('error', title, message);
                        }
                    }
                )
            }
        }

		return this;
	};
})();
