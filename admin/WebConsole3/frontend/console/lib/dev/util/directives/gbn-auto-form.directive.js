(function(){
    'use strict';
    /** 
     * globunet-auto-form directive.
     */
    angular.module("globunet.utils")
    .provider('gbnAutoFormConfig', gbnAutoFormConfig)
    .directive('gbnAutoForm', gbnAutoForm);

    gbnAutoFormConfig.$inject = [];
    function gbnAutoFormConfig(){
        var self = this;
        self.templates = {
            text:'<div class="form-group"\ ng-class="{{ngClass}}">\
                    <label for="{{field}}">{{label}}\
                        <span ng-if="{{required}}" class="symbol required"></span>\
                    </label>\
                    <input class="form-control" type="text" {{required}} {{contraints}} ng-model="{{model}}" name="{{field}}">\
                </div>',
            textarea:'<div class="form-group"\ ng-class="{{ngClass}}">\
                <label for="{{field}}">{{label}}\
                    <span ng-if="{{required}}" class="symbol required"></span>\
                </label>\
                <textarea class="form-control" type="text" {{required}} {{contraints}} ng-model="{{model}}" name="{{field}}"></textarea>\
            </div>',
            email:'<div class="form-group"\ ng-class="{{ngClass}}">\
                    <label for="{{field}}">{{label}}\
                        <span ng-if="{{required}}" class="symbol required"></span>\
                    </label>\
                    <input class="form-control" type="email" {{required}} {{contraints}} ng-model="{{model}}" name="{{field}}">\
                </div>',
            password:'<div class="form-group"\ ng-class="{{ngClass}}">\
                    <label for="{{field}}">{{label}}\
                        <span ng-if="{{required}}" class="symbol required"></span>\
                    </label>\
                    <input class="form-control" type="password" {{required}} {{contraints}} ng-model="{{model}}" name="{{field}}">\
                </div>',
            number:'<div class="form-group"\ ng-class="{{ngClass}}">\
                    <label for="{{field}}">{{label}}\
                        <span ng-if="{{required}}" class="symbol required"></span>\
                    </label>\
                    <input class="form-control" type="number" {{required}} {{contraints}} ng-model="{{model}}" name="{{field}}">\
                </div>',
            checkbox: '<div class="checkbox clip-check check-primary">\
                        <input type="checkbox" id="{{field}}" value="{{value}}" checked="" ng-model="{{model}}">\
                        <label for="{{field}}">\
                            {{label}}\
                        </label>\
                    </div>',
            select: '<div class="form-group" ng-class="{{ngClass}}">\
                        <label for="{{field}}">{{label}}\
                            <span ng-if="{{required}}" class="symbol required"></span>\
                        </label>\
                        <select {{contraints}} {{ngChange}} class="form-control" {{repeater}} {{required}} ng-model="{{model}}" name="{{field}}">\
                            {{options}}\
                        </select>\
                    </div>',
            "select-multiple": '<div class="form-group" ng-class="{{ngClass}}">\
                                    <label for="{{field}}">{{label}}\
                                        <span ng-if="{{options.required}}" class="symbol required"></span>\
                                    </label>\
                                    <gbn-select2 select-class="select2 form-control" multiple="multiple" name="{{field}}" collection="{{options.value}}" placeholder="{{placeholder}}" {{options.required}} ng-model="{{model}}" option-value="{{options.output}}">\
                                      <gbn-select2-options>\
                                        {{options.label}}\
                                      </gbn-select2-options>\
                                    </gbn-select2>\
                                </div>',
            file:'<div class="form-group"\ ng-class="{{ngClass}}">\
                    <label for="{{field}}">{{label}}\
                        <span ng-if="{{required}}" class="symbol required"></span>\
                    </label>\
                    <input class="form-control" type="file" {{required}} {{contraints}} ng-model="{{model}}" name="{{field}}">\
                </div>',
            date: '<div class="form-group"\ ng-class="{{ngClass}}">\
                        <label for="{{field}}" class="for-date">{{label}}\
                            <span ng-if="{{required}}" class="symbol required"></span>\
                        </label>\
                        <p class="input-group">\
                            <input type="text" placeholder="{{dateHandler.format}}" class="form-control" uib-datepicker-popup="{{dateHandler.format}}" ng-model="{{model}}" name="{{field}}" is-open="dateHandler.getStatus(\'{{field}}\').open" datepicker-options="dateOptions" date-disabled="disabled(date, mode)" ng-required="{{required}}" close-text="Close" placeholder="{{dateHandler.format}}" />\
                            <span class="input-group-btn">\
                                <button type="button" class="btn btn-default" ng-click="dateHandler.open($event,\'{{field}}\')">\
                                    <i class="glyphicon glyphicon-calendar"></i>\
                                </button>\
                            </span>\
                        </p>\
                    </div>',
            time: '<div class="form-group"\ ng-class="{{ngClass}}">\
                    <label for="{{field}}" class="for-date">{{label}}\
                        <span ng-if="{{required}}" class="symbol required"></span>\
                    </label>\
                    <p class="input-group">\
                        <div uib-timepicker ng-model="{{model}}" hour-step="1" minute-step="5" show-meridian="ismeridian"></div>\
                    </p>\
                </div>',         
            button:'<button type="submit" class="{{buttonCssClass}}">{{buttonText}}</button>'
        }

        self.setTemplates = setTemplates;

        function setTemplates(templates){
            angular.extend(self.templates, templates);
        }

        this.$get = function(){
            return self;
        }
    }

    gbnAutoForm.$inject = ['$rootScope', '$compile', '$parse', '$interpolate', '$filter', 'gbnDateHandler', 'gbnAutoFormConfig'];
    function gbnAutoForm($rootScope, $compile, $parse, $interpolate, $filter, gbnDateHandler, gbnAutoFormConfig) {
        // Añadir todas las plantillas al templateCache
        var templates = gbnAutoFormConfig.templates;

        var template = "<div>void form</div>";

        var getFieldType = function(option){
            var type = option.type;
            var result = type;
            if(type === "string"){
                result = "text";
            }
            else if(type === "integer"){
                result = "number";
            }
            else if(type ==="choice" || type === "select"){
                result = "select";
                if(option.multiple){
                    result = "select-multiple";
                }
            }
            return result;
        }

        var getFieldLabel = function(field, option){
            var result = option.label;

            if(!result){
                result = field;
            }
            
            return $filter("translate")(result);
        }

        var getCssClass = function(formName, fieldName, field){
            var result = "";
            // Añadirlo a un form group
            formName = formName || "Form";
            var dirty = formName+"."+fieldName+".$dirty";
            var invalid = formName+"."+fieldName+".$invalid";
            var valid = formName+"."+fieldName+".$valid";
            var customCss = "";
            if(field.css){
                customCss = ",'"+field.css +"': true";
            }  
            
            result = "{'has-error':"+dirty+" && "+ invalid+", 'has-success':"+valid+customCss+"}";

            return result;
        }

        var getTemplate = function(scope, optionsVar, model, formName){
            var output = "";
            var fields = $parse(optionsVar)(scope).fields;
            var useRows = false;
            if(fields.rows){
                useRows = true;
            }
            var rows = fields.rows||[fields];
            // Comprobamos si tenemos filas en el formulario
            for(var row in rows){
                fields = rows[row];
                if(useRows)
                    output += "<div class='row'>";
                for(var index in fields){
                    var field = fields[index];
                     // Si tiene un valor por defecto se asigna aquí
                    if(field.default){
                        if(!$parse(model)(scope))
                            $parse(model+"={}")(scope);
                        $parse(model)(scope)[index] = field.default;
                    }
                    // Solo renderizamos aquellos campos visibles
                    if(typeof field.visible === "undefined" || field.visible === true){
                        // Comprobar el tipo para saber que tenemos que renderizar
                        var type = getFieldType(field);
                        // Obtener la plantilla
                        var template = templates[type];
                        // Preparar el contexto para renderizar la plantilla
                        var context = {};
                        context.field = index;
                        context.label = getFieldLabel(index, field);
                        context.ngClass = getCssClass(formName, index, field);
                        // Si existe la opcion required all directamente asignamos true
                        context.required = field.required||$parse(optionsVar)(scope).required === "all"?"required='true'":"";
                        // Añadimos el modelo
                        context.model = model+"."+index;

                        context.contraints = "";
                        if(typeof field.read_only !== "undefined" && field.read_only == true){
                            context.contraints += (field.type === "select" ||field.type === "select-multiple")?(" disabled = true"):(" readOnly = true ");
                        }
                        if(typeof field.max_length !== "undefined"){
                            context.contraints += " maxlength='"+field.max_length+"' ";
                        }
                        if(typeof field.min_length !== "undefined"){
                            context.contraints += " minlength='"+field.max_length+"' ";
                        }
                        if(typeof field.max !== "undefined"){
                            context.contraints += " max='"+field.max+"' ";
                        }
                        if(typeof field.min !== "undefined"){
                            context.contraints += " min='"+field.min+"' ";
                        }
                        if(typeof field.pattern !== "undefined"){
                            context.contraints += " pattern='"+field.pattern+"' ";
                        }
                        if(type === "date"){
                            context.dateHandler = $rootScope.dateHandler;
                        }

                        if(type === "select"){
                            context.options = "";
                            if(field.options.ngChange){
                                context.ngChange = "ng-change='"+field.options.ngChange+"'";
                            }
                            var value, label;
                            // Si el campo options es de tipo array, lo usamos directamente
                            if(Array.isArray(field.options)){
                               
                                for(var op = 0; op < field.options.length; op++){
                                    // si es un objeto usamos key, value
                                    if(typeof field.options[op] == "object"){
                                        label = $filter("translate")(Object.keys(field.options[op])[0]);
                                        value = field.options[op][label];
                                    }
                                    else{
                                        value = field.options[op];
                                        label = $filter("translate")(field.options[op]);
                                    }
                                    context.options +="<option value='"+value+"' translate='"+label+"'>"+label+"</option>";
                                }
                            }
                            else{
                                if(field.options.source){
                                    var optionLabel = field.options.label?("item as item."+field.options.label)+" for item in ":"key|translate for (key , value) in ";
                                    var trackby = field.options.trackby||"";
                                    if(trackby === "" && !field.options.label){
                                        trackby = " track by value";
                                    }
                                    context.repeater = "ng-options='"+optionLabel + field.options.source + trackby + "'";
                                }
                            }
                        }
                        else if(type === "select-multiple"){
                            context.options = field.options;
                            context.optionLabel = field.optionLabel||"'element $index'";
                            if((typeof field.options.required === "boolean" && field.options.required === true) || ((typeof field.options.required === "string" && field.options.required === "true"))){
                                context.options.required = "required='true'";
                            }
                            else{
                                context.required = "";
                            }
                        }
                        if(type === "submit"){
                            template = templates["button"];
                            template = "<div class='"+field.css+"'>"+template+"</div>";
                            context = {
                                buttonCssClass: "btn btn-primary",
                                buttonText: "Submit"
                            };
                            context.buttonCssClass = field.buttonCss || context.buttonCssClass;
                            context.buttonText = field.label || context.buttonText;
                        }
                        // Si no encontramos la plantilla no se muestra nada
                        if(template){
                            var exp = $interpolate(template);
                            output += exp(context);
                        }
                    }
                }
                if(useRows)
                   output += "</div>";
            }
            // Renderizamos el botón de submit si existe            
            if($parse(optionsVar)(scope).submitButton){
                context = {
                    buttonCssClass: "btn btn-primary",
                    buttonText: "Submit"
                };
                context.buttonCssClass = $parse(optionsVar)(scope).submitButton.cssClass || context.buttonCssClass;
                context.buttonText = $parse(optionsVar)(scope).submitButton.text || context.buttonText;
            
                output += $interpolate(templates["button"])(context);
            }

            return output;
        }
        // Comprobar si en el rootScope está el DateHandler de Globunet, sino, asignarlo
        if(!$rootScope.dateHandler){
            $rootScope.dateHandler = gbnDateHandler;
        }

        return {
            link: function (scope, element, attributes) {
                
                scope.$watch(attributes.formOptions, function(){
                    template = getTemplate(scope, attributes.formOptions, attributes.formModel, attributes.name);
                    element.html(template);
                    $compile(element.contents())(scope);
                });
            },
            template: template
        };
    };
})();
