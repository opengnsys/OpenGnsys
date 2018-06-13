(function(){
	'use strict';

	angular.module('gbn-lte-admin',[])
	.provider('lteAdminConfig', lteAdminConfig)
	.service('lteAdminInitService',lteAdminInitService);

	lteAdminInitService.$inject = ['lteAdminConfig','$timeout', '$document', '$rootScope'];

	function lteAdminConfig(){
		var self = this;
		var defaultOpts = {
		  //Add slimscroll to navbar menus
		  //This requires you to load the slimscroll plugin
		  //in every page before app.js
		  navbarMenuSlimscroll: true,
		  navbarMenuSlimscrollWidth: "3px", //The width of the scroll bar
		  navbarMenuHeight: "200px", //The height of the inner menu
		  //General animation speed for JS animated elements such as box collapse/expand and
		  //sidebar treeview slide up/down. This options accepts an integer as milliseconds,
		  //'fast', 'normal', or 'slow'
		  animationSpeed: 500,
		  //Sidebar push menu toggle button selector
		  sidebarToggleSelector: "[data-toggle='offcanvas']",
		  //Activate sidebar push menu
		  sidebarPushMenu: true,
		  //Activate sidebar slimscroll if the fixed layout is set (requires SlimScroll Plugin)
		  sidebarSlimScroll: true,
		  //Enable sidebar expand on hover effect for sidebar mini
		  //This option is forced to true if both the fixed layout and sidebar mini
		  //are used together
		  sidebarExpandOnHover: false,
		  //BoxRefresh Plugin
		  enableBoxRefresh: true,
		  //Bootstrap.js tooltip
		  enableBSToppltip: true,
		  BSTooltipSelector: "[data-toggle='tooltip']",
		  //Enable Fast Click. Fastclick.js creates a more
		  //native touch experience with touch devices. If you
		  //choose to enable the plugin, make sure you load the script
		  //before AdminLTE's app.js
		  enableFastclick: true,
		  //Control Sidebar Options
		  enableControlSidebar: true,
		  controlSidebarOptions: {
		    //Which button should trigger the open/close event
		    toggleBtnSelector: "[data-toggle='control-sidebar']",
		    //The sidebar selector
		    selector: ".control-sidebar",
		    //Enable slide over content
		    slide: true
		  },
		  //Box Widget Plugin. Enable this plugin
		  //to allow boxes to be collapsed and/or removed
		  enableBoxWidget: true,
		  //Box Widget plugin options
		  boxWidgetOptions: {
		    boxWidgetIcons: {
		      //Collapse icon
		      collapse: 'fa-minus',
		      //Open icon
		      open: 'fa-plus',
		      //Remove icon
		      remove: 'fa-times'
		    },
		    boxWidgetSelectors: {
		      //Remove button selector
		      remove: '[data-widget="remove"]',
		      //Collapse button selector
		      collapse: '[data-widget="collapse"]'
		    }
		  },
		  //Direct Chat plugin options
		  directChat: {
		    //Enable direct chat by default
		    enable: true,
		    //The button to open and close the chat contacts pane
		    contactToggleSelector: '[data-widget="chat-pane-toggle"]'
		  },
		  //Define the set of colors to use globally around the website
		  colors: {
		    lightBlue: "#3c8dbc",
		    red: "#f56954",
		    green: "#00a65a",
		    aqua: "#00c0ef",
		    yellow: "#f39c12",
		    blue: "#0073b7",
		    navy: "#001F3F",
		    teal: "#39CCCC",
		    olive: "#3D9970",
		    lime: "#01FF70",
		    orange: "#FF851B",
		    fuchsia: "#F012BE",
		    purple: "#8E24AA",
		    maroon: "#D81B60",
		    black: "#222222",
		    gray: "#d2d6de"
		  },
		  //The standard screen sizes that bootstrap uses.
		  //If you change these in the variables.less file, change
		  //them here too.
		  screenSizes: {
		    xs: 480,
		    sm: 768,
		    md: 992,
		    lg: 1200
		  }
		};

		self.setOptions = setOptions;
		self.options = defaultOpts;

		self.$get = function(){
			return self;
		}

		function setOptions(options){
			//Extend options if external options exist
			angular.extend(defaultOpts, options);
		};


	}


	function lteAdminInitService(lteAdminConfig, $timeout, $document, $rootScope){
		var self =  this;
		var sidebarInit = false;
		var toggleSelectorInit = false;
		var controlSidebarInit = false;
		var slimscrollInit = false;
		var fastClickInit = false;
		var buttonToggleInit = false;
		var boxWidgetInit = false;

		var $body = angular.element("body");
		
		
		//Set up the object
		self.layout = layout();
		self.pushMenu = pushMenu();
		self.tree = tree;
		self.controlSidebar = controlSidebar();
		self.boxWidget = boxWidget();
		self.init = init;

		return self;

		//////////////////////////////
		function init(){
			// Inicializar la plantilla
			$rootScope.$on("$viewContentLoaded",function(event){
				$timeout(function(){load();},0);
			});
		}

		function load(){
			//Easy access to options
			var o = lteAdminConfig.options;

			//Fix for IE page transitions
			if($body.hasClass("hold-transition"))
				$body.removeClass("hold-transition");

			//Activate the layout maker
			self.layout.activate();
			

			//Enable sidebar tree view controls
			if(angular.element(".sidebar").length > 0 && !sidebarInit){
				self.tree('.sidebar');
				sidebarInit = true;
			}

			//Enable control sidebar
			if (o.enableControlSidebar && !controlSidebarInit) {
				if(angular.element(o.controlSidebarOptions.selector).length > 0 && angular.element(o.controlSidebarOptions.toggleBtnSelector).length > 0){
					self.controlSidebar.activate();
					controlSidebarInit = true;
				}
			}

			//Add slimscroll to navbar dropdown
			if (o.navbarMenuSlimscroll && typeof $.fn.slimscroll != 'undefined' && !slimscrollInit) {
				if(angular.element(".navbar .menu").length > 0){
					angular.element(".navbar .menu").slimscroll({
					  height: o.navbarMenuHeight,
					  alwaysVisible: false,
					  size: o.navbarMenuSlimscrollWidth
					}).css("width", "100%");
				}
				slimscrollInit = true;
			}

			//Activate sidebar push menu
			if (o.sidebarPushMenu && angular.element(o.sidebarToggleSelector).length > 0 && sidebarInit && !toggleSelectorInit) {
				self.pushMenu.activate(o.sidebarToggleSelector);
				toggleSelectorInit = true;
			}

			//Activate Bootstrap tooltip
			if (o.enableBSToppltip) {
				$body.tooltip({
				  selector: o.BSTooltipSelector
				});
			}

			//Activate box widget
			if (o.enableBoxWidget && !boxWidgetInit) {
				self.boxWidget.activate();
				boxWidgetInit = true;
			}

			//Activate fast click
			if (o.enableFastclick && typeof FastClick != 'undefined' && !fastClickInit) {
				FastClick.attach(document.body);
				fastClickInit = true;
			}

			//Activate direct chat widget
			if (o.directChat.enable) {
				$document.on('click', o.directChat.contactToggleSelector, function () {
				  var box = angular.element(this).parents('.direct-chat').first();
				  box.toggleClass('direct-chat-contacts-open');
				});
			}

			/*
			* INITIALIZE BUTTON TOGGLE
			* ------------------------
			*/
			if(angular.element('.btn-group[data-toggle="btn-toggle"]').length > 0 && !buttonToggleInit){
				angular.element('.btn-group[data-toggle="btn-toggle"]').each(function () {
					var group = angular.element(this);
					angular.element(this).find(".btn").on('click', function (e) {
					  group.find(".btn.active").removeClass("active");
					  angular.element(this).addClass("active");
					  e.preventDefault();
					});

				});
				buttonToggleInit = true;
			}
		}


		/* Layout
		 * ======
		 * Fixes the layout height in case min-height fails.
		 *
		 * @type Object
		 * @usage lteAdminInitService.layout.activate()
		 *        lteAdminInitService.layout.fix()
		 *        lteAdminInitService.layout.fixSidebar()
		 */
		function layout(){
			var _this = {};
			var eventAdded = false;

			_this.activate = activateLayout;
			_this.fix = fixLayout;
			_this.fixSidebar = fixSidebarLayout;

			return _this;

			////////////////////////////////////////

			function activateLayout(){
				_this.fix();
				_this.fixSidebar();
				// Solo añadimos el evento una vez
				if(!eventAdded && angular.element(".wrapper").length !== 0){
					angular.element(window, ".wrapper").resize(function () {
						_this.fix();
						_this.fixSidebar();
					});
					eventAdded = true;
				}
			}

			function fixLayout(){
				//Get window height and the wrapper height
				var neg = angular.element('.main-header').outerHeight() + angular.element('.main-footer').outerHeight();
				var window_height = angular.element(window).height();
				var sidebar_height = angular.element(".sidebar").height();
				//Set the min-height of the content and sidebar based on the
				//the height of the document.
				if ($body.hasClass("fixed")) {
					angular.element(".content-wrapper, .right-side").css('min-height', window_height - angular.element('.main-footer').outerHeight());
				} 
				else {
					var postSetWidth;
					if (window_height >= sidebar_height) {
						angular.element(".content-wrapper, .right-side").css('min-height', window_height - neg);
						postSetWidth = window_height - neg;
					} 
					else {
						angular.element(".content-wrapper, .right-side").css('min-height', sidebar_height);
						postSetWidth = sidebar_height;
					}

					//Fix for the control sidebar height
					var controlSidebar = angular.element(lteAdminConfig.options.controlSidebarOptions.selector);
					if (typeof controlSidebar !== "undefined") {
						if (controlSidebar.height() > postSetWidth)
							angular.element(".content-wrapper, .right-side").css('min-height', controlSidebar.height());
					}

				}
			}

			function fixSidebarLayout(){
				var _return = false;
				// Make sure the body tag has the .fixed class
				if (!$body.hasClass("fixed")) {
					if (typeof $.fn.slimScroll != 'undefined') {
						angular.element(".sidebar").slimScroll({destroy: true}).height("auto");
					}
					_return = true;
				} 
				else if (typeof $.fn.slimScroll == 'undefined' && window.console) {
					window.console.error("Error: the fixed layout requires the slimscroll plugin!");
				}
				if(!_return){
					//Enable slimscroll for fixed layout
					if (lteAdminConfig.options.sidebarSlimScroll) {
						if (typeof $.fn.slimScroll != 'undefined') {
							//Destroy if it exists
							angular.element(".sidebar").slimScroll({destroy: true}).height("auto");
							//Add slimscroll
							angular.element(".sidebar").slimscroll({
								height: (angular.element(window).height() - angular.element(".main-header").height()) + "px",
								color: "rgba(0,0,0,0.2)",
								size: "3px"
							});
						}
					}
				}
			}

		}

		/* PushMenu()
		 * ==========
		 * Adds the push menu functionality to the sidebar.
		 *
		 * @type Function
		 * @usage: lteAdminInitService.pushMenu("[data-toggle='offcanvas']")
		 */
		function pushMenu(){
			var _this = {};

			_this.activate = activatePushMenu;
			_this.expandOnHover = expandOnHoverPushMenu;
			_this.expand = expandPushMenu;
			_this.collapse = collapsePushMenu;

			return _this;

			////////////////////////////////

			function activatePushMenu(toggleBtn) {
				//Get the screen sizes
				var screenSizes = lteAdminConfig.options.screenSizes;

				//Enable sidebar toggle
				$document.on('click', toggleBtn, function (e) {
					e.preventDefault();

					//Enable sidebar push menu
					if (angular.element(window).width() > (screenSizes.sm - 1)) {
					  if ($body.hasClass('sidebar-collapse')) {
					    $body.removeClass('sidebar-collapse').trigger('expanded.pushMenu');
					  } 
					  else {
					    $body.addClass('sidebar-collapse').trigger('collapsed.pushMenu');
					  }
					}
					//Handle sidebar push menu for small screens
					else {
					  if ($body.hasClass('sidebar-open')) {
					    $body.removeClass('sidebar-open').removeClass('sidebar-collapse').trigger('collapsed.pushMenu');
					  } 
					  else {
					    $body.addClass('sidebar-open').trigger('expanded.pushMenu');
					  }
					}
				});

				angular.element(".content-wrapper").click(
					function () {
					    //Enable hide menu when clicking on the content-wrapper on small screens
					    if (angular.element(window).width() <= (screenSizes.sm - 1) && $body.hasClass("sidebar-open")) {
					      $body.removeClass('sidebar-open');
					    }
					}
				);

				//Enable expand on hover for sidebar mini
				if (lteAdminConfig.options.sidebarExpandOnHover || ($body.hasClass('fixed')&& $body.hasClass('sidebar-mini'))) {
					_this.expandOnHover();
				}
			};

			function expandOnHoverPushMenu() {
			  var screenWidth = lteAdminConfig.options.screenSizes.sm - 1;
			  //Expand sidebar on hover
			  angular.element('.main-sidebar').hover(function () {
			    if ($body.hasClass('sidebar-mini')&& $body.hasClass('sidebar-collapse') && angular.element(window).width() > screenWidth) {
			      _this.expand();
			    }
			  }, function () {
			    if ($body.hasClass('sidebar-mini') && $body.hasClass('sidebar-expanded-on-hover') && angular.element(window).width() > screenWidth) {
			      _this.collapse();
			    }
			  });
			};

			function expandPushMenu() {
			  $body.removeClass('sidebar-collapse').addClass('sidebar-expanded-on-hover');
			};

			function collapsePushMenu() {
			  if ($body.hasClass('sidebar-expanded-on-hover')) {
			    $body.removeClass('sidebar-expanded-on-hover').addClass('sidebar-collapse');
			  }
			}

		};

		/* Tree()
		 * ======
		 * Converts the sidebar into a multilevel
		 * tree view menu.
		 *
		 * @type Function
		 * @Usage: lteAdminInitService.tree('.sidebar')
		 */
		function tree(menu) {
			var animationSpeed = lteAdminConfig.options.animationSpeed;
			$document.on('click', menu + ' li a', function (e) {
				//Get the clicked link and the next element
				var $this = angular.element(this);
				var checkElement = $this.next();

				//Check if the next element is a menu and is visible
				if ((checkElement.is('.treeview-menu')) && (checkElement.is(':visible')) && (!$body.hasClass('sidebar-collapse'))) {
					//Close the menu
					checkElement.slideUp(animationSpeed, function () {
					  checkElement.removeClass('menu-open');
					  //Fix the layout in case the sidebar stretches over the height of the window
					  //_this.layout.fix();
					});
					checkElement.parent("li").removeClass("active");
				}
				//If the menu is not visible
				else if ((checkElement.is('.treeview-menu')) && (!checkElement.is(':visible'))) {
					//Get the parent menu
					var parent = $this.parents('ul').first();
					//Close all open menus within the parent
					var ul = parent.find('ul:visible').slideUp(animationSpeed);
					//Remove the menu-open class from the parent
					ul.removeClass('menu-open');
					//Get the parent li
					var parent_li = $this.parent("li");

					//Open the target menu and add the menu-open class
					checkElement.slideDown(animationSpeed, function () {
					  //Add the class active to the parent li
					  checkElement.addClass('menu-open');
					  parent.find('li.active').removeClass('active');
					  parent_li.addClass('active');
					  //Fix the layout in case the sidebar stretches over the height of the window
					  self.layout.fix();
					});
				}
				// Si el elemento no tiene asociado ningún menu, seleccionamos el li sin desplegar menú
				else {
					//Get the parent menu
					var parent = $this.parents('ul').first();
					// Quitar la clase active a cualquier otro li que haya seleccionado
					parent.find('li.active').removeClass("active");
					//Close all open menus within the parent
					var ul = parent.find('ul:visible').slideUp(animationSpeed);
					//Remove the menu-open class from the parent
					ul.removeClass('menu-open');
					//Get the parent li
					var parent_li = $this.parent("li");
					parent_li.addClass('active');
					//Fix the layout in case the sidebar stretches over the height of the window
					self.layout.fix();
					// Si estamos en la version movil, y el menu está desplegado, lo cerramos
					if ($body.hasClass('sidebar-open')) {
						$body.removeClass('sidebar-open').removeClass('sidebar-collapse').trigger('collapsed.pushMenu');
					} 
				}
				//if this isn't a link, prevent the page from being redirected
				if (checkElement.is('.treeview-menu')) {
					e.preventDefault();
				}
			});
		};

		/* ControlSidebar
		* ==============
		* Adds functionality to the right sidebar
		*
		* @type Object
		* @usage $.AdminLTE.controlSidebar.activate(options)
		*/
		function controlSidebar() {
			var _this = {};

			_this.activate = activateSidebar;
			_this.open = openSidebar;
			_this.close = closeSidebar;
			_this._fix = _fixSidebar;
			_this._fixForFixed = _fixForFixedSidebar;
			_this._fixForContent = _fixForContentSidebar;

			return _this;

			//instantiate the object
			function activateSidebar() {
			  //Update options
			  var o = lteAdminConfig.options.controlSidebarOptions;
			  //Get the sidebar
			  var sidebar = angular.element(o.selector);
			  //The toggle button
			  var btn = angular.element(o.toggleBtnSelector);

			  //Listen to the click event
			  btn.on('click', function (e) {
			    e.preventDefault();
			    //If the sidebar is not open
			    if (!sidebar.hasClass('control-sidebar-open')
			        && !$body.hasClass('control-sidebar-open')) {
			      //Open the sidebar
			      _this.open(sidebar, o.slide);
			    } else {
			      _this.close(sidebar, o.slide);
			    }
			  });

			  //If the body has a boxed layout, fix the sidebar bg position
			  var bg = angular.element(".control-sidebar-bg");
			  _this._fix(bg);

			  //If the body has a fixed layout, make the control sidebar fixed
			  if ($body.hasClass('fixed')) {
			    _this._fixForFixed(sidebar);
			  } else {
			    //If the content height is less than the sidebar's height, force max height
			    if (angular.element('.content-wrapper, .right-side').height() < sidebar.height()) {
			      _this._fixForContent(sidebar);
			    }
			  }
			}

			//Open the control sidebar
			function openSidebar(sidebar, slide) {
			  //Slide over content
			  if (slide) {
			    sidebar.addClass('control-sidebar-open');
			  } else {
			    //Push the content by adding the open class to the body instead
			    //of the sidebar itself
			    $body.addClass('control-sidebar-open');
			  }
			}
			//Close the control sidebar
			function closeSidebar(sidebar, slide) {
			  if (slide) {
			    sidebar.removeClass('control-sidebar-open');
			  } else {
			    $body.removeClass('control-sidebar-open');
			  }
			}

			function _fixSidebar(sidebar) {
			  var _this = this;
			  if ($body.hasClass('layout-boxed')) {
			    sidebar.css('position', 'absolute');
			    sidebar.height($(".wrapper").height());
			    angular.element(window).resize(function () {
			      _this._fix(sidebar);
			    });
			  } else {
			    sidebar.css({
			      'position': 'fixed',
			      'height': 'auto'
			    });
			  }
			}

			function _fixForFixedSidebar(sidebar) {
			  sidebar.css({
			    'position': 'fixed',
			    'max-height': '100%',
			    'overflow': 'auto',
			    'padding-bottom': '50px'
			  });
			}

			function _fixForContentSidebar(sidebar) {
			  angular.element(".content-wrapper, .right-side").css('min-height', sidebar.height());
			}
		};

		/* BoxWidget
		* =========
		* BoxWidget is a plugin to handle collapsing and
		* removing boxes from the screen.
		*
		* @type Object
		* @usage $.AdminLTE.boxWidget.activate()
		*        Set all your options in the main lteAdminConfig.options object
		*/
		function boxWidget(){
			var _this = {};

			_this.selectors = lteAdminConfig.options.boxWidgetOptions.boxWidgetSelectors;
			_this.icons = lteAdminConfig.options.boxWidgetOptions.boxWidgetIcons;
			_this.animationSpeed = lteAdminConfig.options.animationSpeed;
			_this.activate = activateBoxWidget;
			_this.collapse = collapseBoxWidget;
			_this.remove = removeBoxWidget;

			return _this;

			////////////////////////////////

			function activateBoxWidget(_box) {
				var _this = this;
				if (!_box) {
					_box = $document; // activate all boxes per default
				}
				//Listen for collapse event triggers
				angular.element(_box).on('click', _this.selectors.collapse, function (e) {
					e.preventDefault();
					_this.collapse(angular.element(this));
				});

				//Listen for remove event triggers
				angular.element(_box).on('click', _this.selectors.remove, function (e) {
					e.preventDefault();
					_this.remove(angular.element(this));
				});
			};

			function collapseBoxWidget(element) {
				var _this = this;
				//Find the box parent
				var box = element.parents(".box").first();
				//Find the body and the footer
				var box_content = box.find("> .box-body, > .box-footer, > form  >.box-body, > form > .box-footer");
				if (!box.hasClass("collapsed-box")) {
					//Convert minus into plus
					element.children(":first")
					.removeClass(_this.icons.collapse)
					.addClass(_this.icons.open);
					//Hide the content
					box_content.slideUp(_this.animationSpeed, function () {
						box.addClass("collapsed-box");
					});
				} 
				else {
					//Convert plus into minus
					element.children(":first")
					.removeClass(_this.icons.open)
					.addClass(_this.icons.collapse);
					//Show the content
					box_content.slideDown(_this.animationSpeed, function () {
						box.removeClass("collapsed-box");
					});
				}
			};

			function removeBoxWidget(element) {
				var _this = this;
				//Find the box parent
				var box = element.parents(".box").first();
				box.slideUp(_this.animationSpeed);
			}
		};

	}



	
})();