(function(){
    'use strict';
    /** 
     * gbn-stop-event directive.
     */
    angular.module("globunet.utils")
    .directive('gbnGojs', gbnGojs);

	gbnGojs.$inject = ["$interpolate", "$timeout"];

    function gbnGojs($interpolate, $timeout) {
    	var template ='<div id="{{diagramId}}" style="background-color: white; border: solid 1px black; width: 100%; height: 550px"></div>';
    	var bluegrad = '#90CAF9';
		var pinkgrad = '#F48FB1';
		var orangegrad = '#ff9f33';

	    return {
        	restrict: 'E',
        	template: template,
        	scope:{
        		diagramId: "@",
        		diagramData: "=",
        		diagramHandler: "=",
        		diagramOptions: "="

        	},
        	link: function(scope, element, attr) {
        		var goMake = go.GraphObject.make;
        		var initialized = false;
        		
        		scope.diagramId = scope.diagramId||"myDiagramDiv";
        		// Añadir al elemento la plantilla
        		//element.append($interpolate(template)(scope));

        		scope.$watchCollection("diagramData", function(n,o,e){
        			if(n){
        				if(!initialized){
        					initialized = true;
        					scope.init();
        				}
		    			// create the model for the family tree
						scope.myDiagram.model = new go.TreeModel(scope.diagramData);
					}
        		});
        		

			    if(scope.diagramHandler){
			    	scope.diagramHandler.zoomToFit = zoomToFit;
			    	scope.diagramHandler.centerOnRoot = centerOnRoot;
			    	scope.diagramHandler.zoomIn = zoomIn;
			    	scope.diagramHandler.zoomOut = zoomOut;
			    }
			    scope.init = init;

			    //$timeout(init(),0);


			    function init(){
			    	// Si no existen converters se crean a vacío para que no falle
			    	angular.extend(scope.diagramOptions.converters,{});
			    	scope.layoutOptions = { angle: 90, nodeSpacing: 10, layerSpacing: 40, layerStyle: go.TreeLayout.LayerUniform };
	        		var diagramOptions = {
			          "toolManager.hoverDelay": 100,  // 100 milliseconds instead of the default 850
			          allowCopy: false,
			          // create a TreeLayout for the family tree
			          layout:  goMake(go.TreeLayout, scope.layoutOptions),
			          initialDocumentSpot: go.Spot.TopCenter,
				      initialViewportSpot: go.Spot.TopCenter
			        }

			        scope.myDiagram = goMake(go.Diagram, scope.diagramId, diagramOptions);// must be the ID or reference to div
			        // Set up a Part as a legend, and place it directly on the diagram
				    // Leyenda
					scope.myDiagram.add(getLeyend());


					// replace the default Node template in the nodeTemplateMap
				    scope.myDiagram.nodeTemplate =
				      goMake(go.Node, "Auto", {deletable: false, toolTip: getTooltipTemplate() },
				        new go.Binding("text", "name"),
				        goMake(go.Shape, "Rectangle",
				          { fill: "lightgray", // color default
				            stroke: null, strokeWidth: 0,
				            stretch: go.GraphObject.Fill,
				            alignment: go.Spot.Center },getColorBrushConverter()),
				          //new go.Binding("fill", "color", colorBrushConverter)), // alternative color
				        goMake(go.TextBlock,
				          { font: "700 12px Droid Serif, sans-serif",
				            textAlign: "center",
				            margin: 20, maxSize: new go.Size(80, NaN) },
				          new go.Binding("text", "name")) // name puede cambiarse por otro atributo aqui y en el JSON para ser texto mostrado en la caja
				      );

				      // define the Link template
				    scope.myDiagram.linkTemplate =
				      goMake(go.Link,  // the whole link panel
				        { routing: go.Link.Orthogonal, corner: 5, selectable: false },
				        goMake(go.Shape, { strokeWidth: 3, stroke: '#424242' }));  // the gray link shape
    			}

			    function getLeyend(){
			    	var result = {};
			    	if(scope.diagramOptions.legend){
			    		var title = scope.diagramOptions.legend.title||"Leyend Title";
			    		var options = [];
			    		for(var i = 0; i < scope.diagramOptions.legend.inputs.length; i++){
			    			var input = scope.diagramOptions.legend.inputs[i];
			    			var color = null;
			    			var text = "legend "+i;
			    			if(input.color){
			    				color = goMake(go.Shape, "Rectangle", { desiredSize: new go.Size(30, 30), fill: input.color, margin: 5 })
			    			}
			    			if(input.text){
			    				text = goMake(go.TextBlock, input.text,{ font: "700 13px Droid Serif, sans-serif" });
			    			}
			    			options.push(goMake(go.Panel,"Horizontal", {row: i+1,alignment: go.Spot.Left},color,text));
			    		}

			    		var point = new go.Point(-100, 100);
						return goMake(go.Part, "Table", { position: point, selectable: false },
							goMake(go.TextBlock, title, { row: 0, font: "700 14px Droid Serif, sans-serif" }),
							options
						);
					}
				}

				
			     // define tooltips for nodes
			    function getTooltipTemplate(){
			    	var result = null;
			    	if(scope.diagramOptions.converters.tooltipTextConverter){
			    		var _function = scope.diagramOptions.converters.tooltipTextConverter._function;
				    	result = goMake(go.Adornment, "Auto",
							        goMake(go.Shape, "Rectangle",
							          { fill: "whitesmoke", stroke: "black" }),
							        goMake(go.TextBlock,
							          { font: "bold 8pt Helvetica, bold Arial, sans-serif",
							            wrap: go.TextBlock.WrapFit,
							            margin: 5 },
							          new go.Binding("text", "", _function))
							      );
				    }
				    return result;
			    }

			    // define Converters to be used for Bindings
			    function getColorBrushConverter() { // convierte color por defecto a el definido en el JSON
			    	var result = {};
			    	if(scope.diagramOptions.converters.colorBrushConverter){
			    		var property = scope.diagramOptions.converters.colorBrushConverter.property||"fill";
			    		var field = scope.diagramOptions.converters.colorBrushConverter.field||"color";
			    		var _function = scope.diagramOptions.converters.colorBrushConverter._function;
			    		result = new go.Binding(property, field, _function);
					}
					return result;
			    }

			    function zoomToFit(){
			    	scope.myDiagram.zoomToFit();
			    }

			    function centerOnRoot(){
			    	scope.myDiagram.scale = 1;
			     	scope.myDiagram.scrollToRect(myDiagram.findNodeForKey(0).actualBounds);
			    }

			    function zoomOut(){
			    	scope.myDiagram.scale -= 0.1;
			    }
			    function zoomIn(){
			    	scope.myDiagram.scale += 0.1;
			    }

        	}
    	};
	};
})();