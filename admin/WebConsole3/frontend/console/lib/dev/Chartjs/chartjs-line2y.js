(function(){

  'use strict';

  Chart.types.Line.extend({
    name: "Line2Y",
    getScale: function(data) {
        var startPoint = this.options.scaleFontSize;
        var endPoint = this.chart.height - (this.options.scaleFontSize * 1.5) - 5;
        return Chart.helpers.calculateScaleRange(
            data,
            endPoint - startPoint,
            this.options.scaleFontSize,
            this.options.scaleBeginAtZero,
            this.options.scaleIntegersOnly);
    },
    initialize: function (data) {
        var helpers = Chart.helpers;
        var y2datasetLabels = [];
        var y2data = [];
        var y1data = [];
        data.datasets.forEach(function (dataset, i) {
            if (dataset.y2axis == true) {
              y2datasetLabels.push(dataset.label);
              y2data = y2data.concat(dataset.data);
            } else {
              y1data = y1data.concat(dataset.data);
            }
        });

        // use the helper function to get the scale for both datasets
        var y1Scale = this.getScale(y1data);
        this.y2Scale = this.getScale(y2data);
        var normalizingFactor = y1Scale.max / this.y2Scale.max;

        /* update y2 datasets
        this.datasets.forEach(function(dataset) {
            if (y2datasetLabels.indexOf(dataset.label) !== -1) {
                dataset.points.forEach(function (e, j) {
                    dataset.points[j].value = e.value * normalizingFactor;
                })
            }
        })
        /**/

        // denormalize tooltip for y2 datasets
        this.options.multiTooltipTemplate = function (d) {
            if (y2datasetLabels.indexOf(d.datasetLabel) !== -1) 
                return Math.round(d.value / normalizingFactor, 6);
            else 
                return d.value;
        }

        //Chart.types.Line.prototype.initialize.apply(this, arguments);

      /*** Codigo original initialize de chartjs **/
      //Declare the extension of the default point, to cater for the options passed in to the constructor
      this.PointClass = Chart.Point.extend({
        strokeWidth : this.options.pointDotStrokeWidth,
        radius : this.options.pointDotRadius,
        display: this.options.pointDot,
        hitDetectionRadius : this.options.pointHitDetectionRadius,
        ctx : this.chart.ctx,
        inRange : function(mouseX){
          return (Math.pow(mouseX-this.x, 2) < Math.pow(this.radius + this.hitDetectionRadius,2));
        }
      });

      this.datasets = [];

      //Set up tooltip events on the chart
      if (this.options.showTooltips){
        helpers.bindEvents(this, this.options.tooltipEvents, function(evt){
          var activePoints = (evt.type !== 'mouseout') ? this.getPointsAtEvent(evt) : [];
          this.eachPoints(function(point){
            point.restore(['fillColor', 'strokeColor']);
          });
          helpers.each(activePoints, function(activePoint){
            activePoint.fillColor = activePoint.highlightFill;
            activePoint.strokeColor = activePoint.highlightStroke;
          });
          this.showTooltip(activePoints);
        });
      }

      //Iterate through each of the datasets, and build this into a property of the chart
      helpers.each(data.datasets,function(dataset){

        var datasetObject = {
          label : dataset.label || null,
          fillColor : dataset.fillColor,
          strokeColor : dataset.strokeColor,
          pointColor : dataset.pointColor,
          pointStrokeColor : dataset.pointStrokeColor,
          points : []
        };

        this.datasets.push(datasetObject);


        helpers.each(dataset.data,function(dataPoint,index){
          if(dataset.y2axis == true){
            dataPoint = dataPoint*normalizingFactor;
          }
          //Add a new point for each piece of data, passing any required data to draw.
          datasetObject.points.push(new this.PointClass({
            value : dataPoint,
            label : data.labels[index],
            datasetLabel: dataset.label,
            strokeColor : dataset.pointStrokeColor,
            fillColor : dataset.pointColor,
            highlightFill : dataset.pointHighlightFill || dataset.pointColor,
            highlightStroke : dataset.pointHighlightStroke || dataset.pointStrokeColor
          }));
        },this);

        this.buildScale(data.labels);


        this.eachPoints(function(point, index){
          helpers.extend(point, {
            x: this.scale.calculateX(index),
            y: this.scale.endPoint
          });
          point.save();
        }, this);

      },this);


      this.render();
      /******/

        
    },
    draw: function () {
        this.scale.xScalePaddingRight = this.scale.xScalePaddingLeft;
        Chart.types.Line.prototype.draw.apply(this, arguments);

        this.chart.ctx.textAlign = 'left';
        this.chart.ctx.textBaseline = "middle";
        this.chart.ctx.fillStyle = "#666";
        var yStep = (this.scale.endPoint - this.scale.startPoint) / this.y2Scale.steps
        for (var i = 0, y = this.scale.endPoint, label = this.y2Scale.min; 
             i <= this.y2Scale.steps; 
             i++) {
                this.chart.ctx.fillText(label, this.chart.width - this.scale.xScalePaddingRight + 10, y);
                y -= yStep;
                label += this.y2Scale.stepValue
        }
    }
  });
})();