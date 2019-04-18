/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./classes-auto/gutenberg/private-layout/js/index.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./classes-auto/gutenberg/private-layout/css/create-button.css":
/*!*********************************************************************!*\
  !*** ./classes-auto/gutenberg/private-layout/css/create-button.css ***!
  \*********************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ "./classes-auto/gutenberg/private-layout/js/components/PrivateLayoutCreateButton.js":
/*!******************************************************************************************!*\
  !*** ./classes-auto/gutenberg/private-layout/js/components/PrivateLayoutCreateButton.js ***!
  \******************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_0__);
function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

/**
 * @since 2.5.2
 * @author Riccardo Strobbia
 * A wrapper for a button to create a private layout or re-connect an existing one and redirect to its editor
 */

var Component = wp.element.Component;
var __ = wp.i18n.__;

var PrivateLayoutCreateButton =
/*#__PURE__*/
function (_Component) {
  _inherits(PrivateLayoutCreateButton, _Component);

  /**
   *
   * @param props
   */
  function PrivateLayoutCreateButton(props) {
    var _this;

    _classCallCheck(this, PrivateLayoutCreateButton);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(PrivateLayoutCreateButton).call(this, props));

    _defineProperty(_assertThisInitialized(_assertThisInitialized(_this)), "stopUsingPrivateLayoutCallback", function () {
      _this.setState({
        isPrivateLayoutInUse: false
      });
    });

    _defineProperty(_assertThisInitialized(_assertThisInitialized(_this)), "handleClick", function (event) {
      event.preventDefault();

      if (!_this.state.userCanEditPrivate) {
        return false;
      }

      if (_this.state.hasPrivateLayout && !_this.state.isPrivateLayoutInUse) {
        _this.setState({
          isPrivateLayoutInUse: true
        });

        _this.updatePrivateLayout(event);
      } else if (!_this.state.hasPrivateLayout && !_this.state.isPrivateLayoutInUse) {
        _this.createPrivateLayout(event);
      }
    });

    _defineProperty(_assertThisInitialized(_assertThisInitialized(_this)), "createPrivateLayout", function (event) {
      if (_.isObject(DDLayout) && DDLayout.new_layout_dialog instanceof DDLayout.NewLayoutDialog) {
        DDLayout.new_layout_dialog.privateLayoutNewTop(event);
      } else {
        var createLayout = new DDLayout.NewLayoutDialog();
        createLayout.privateLayoutNewTop(event);
      }
    });

    _defineProperty(_assertThisInitialized(_assertThisInitialized(_this)), "updatePrivateLayoutStates", function (layoutId) {
      if (typeof layoutId !== 'undefined' && layoutId !== 0) {
        _this.setState({
          hasPrivateLayout: true,
          isPrivateLayoutInUse: true
        });
      }
    });

    _defineProperty(_assertThisInitialized(_assertThisInitialized(_this)), "updatePrivateLayout", function (event) {
      var updateManager = new DDLayout.UseLayoutsAsPageBuilderManager(jQuery, jQuery(event.target));
      updateManager.update_status(event);
    });

    _defineProperty(_assertThisInitialized(_assertThisInitialized(_this)), "getClassNames", function () {
      var classes = 'layouts-create-private-layout-button-wrap';
      return _this.state.isPrivateLayoutInUse ? classes + ' hidden' : classes;
    });

    _defineProperty(_assertThisInitialized(_assertThisInitialized(_this)), "getIconButton", function () {
      if (_this.state.userCanEditPrivate) {
        return wp.element.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_0__["IconButton"], {
          "data-layout_type": "private",
          "data-layout_id": _this.state.postId,
          "data-post_type": _this.state.postType,
          "data-content_id": _this.state.postId,
          "data-editor": "editor",
          isDefault: true,
          isLarge: true,
          onClick: _this.handleClick,
          className: "button-primary-toolset js-layout-private-use",
          icon: "<i class='icon-layouts-logo fa fa-wpv-custom ont-icon-18 ont-color-white'></i>"
        }, __('Content Layout Editor', 'ddl-layouts'));
      } else {
        return wp.element.createElement(_wordpress_components__WEBPACK_IMPORTED_MODULE_0__["IconButton"], {
          isDefault: true,
          isLarge: true,
          onClick: _this.doNothing,
          className: "button-primary-toolset js-layout-private-use",
          icon: "<i class='icon-layouts-logo fa fa-wpv-custom ont-icon-18 ont-color-white'></i>",
          disabled: true
        }, __('Content Layout Editor', 'ddl-layouts'));
      }
    });

    _defineProperty(_assertThisInitialized(_assertThisInitialized(_this)), "doNothing", function (event) {
      event.preventDefault();
      console.log(__('User doesn\'t have the rights to edit layouts', 'ddl-layouts'));
      return false;
    });

    _this.state = {
      hasPrivateLayout: _this.props.hasPrivateLayout ? true : false,
      isPrivateLayoutInUse: _this.props.isPrivateLayoutInUse ? true : false,
      editorUrl: _this.props.editorUrl,
      postId: _this.props.postId,
      postType: _this.props.postType,
      userCanEditPrivate: _this.props.userCanEditPrivate === '1' ? true : false
    };

    _this.handleClick.bind(_assertThisInitialized(_assertThisInitialized(_this)));

    Toolset.hooks.addAction('ddl_private_layout_usage_stopped', _this.stopUsingPrivateLayoutCallback);
    Toolset.hooks.addAction('ddl_private_layout_created', _this.updatePrivateLayoutStates, 10, 1);
    return _this;
  }
  /**
   * @return void
   */


  _createClass(PrivateLayoutCreateButton, [{
    key: "render",

    /**
     *
     * @return {*}
     */
    value: function render() {
      return wp.element.createElement("div", {
        className: this.getClassNames()
      }, this.getIconButton());
    }
  }]);

  return PrivateLayoutCreateButton;
}(Component);

/* harmony default export */ __webpack_exports__["default"] = (PrivateLayoutCreateButton);

/***/ }),

/***/ "./classes-auto/gutenberg/private-layout/js/index.js":
/*!***********************************************************!*\
  !*** ./classes-auto/gutenberg/private-layout/js/index.js ***!
  \***********************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var react_dom__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react-dom */ "react-dom");
/* harmony import */ var react_dom__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react_dom__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _components_PrivateLayoutCreateButton__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./components/PrivateLayoutCreateButton */ "./classes-auto/gutenberg/private-layout/js/components/PrivateLayoutCreateButton.js");
/* harmony import */ var _css_create_button_css__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../css/create-button.css */ "./classes-auto/gutenberg/private-layout/css/create-button.css");
/* harmony import */ var _css_create_button_css__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_css_create_button_css__WEBPACK_IMPORTED_MODULE_2__);
/**
 * This will load on the Add or Edit Post pages if the wp-components dependency is available (WordPress 5.0+ or
 * Gutenberg plugin active) and add an element to the top bar.
 *
 * @since 2.5.2
 */



/**
 *
 * @type {*|{}}
 */

var DDLayout = DDLayout || {};
/**
 *
 * @constructor
 */

DDLayout.PrivateLayoutCreateButtonMain = function () {
  var settings = PrivateLayoutCreateButtonSettings,
      hasPrivateLayout = settings.hasPrivateLayout,
      isPrivateLayoutInUse = settings.isPrivateLayoutInUse,
      postId = settings.postId,
      editorUrl = settings.editorUrl,
      userCanEditPrivate = settings.userCanEditPrivate,
      postType = settings.postType;
  /**
   * @return void
   */

  this.init = function () {
    deferAddingButton(deferAddingButton);
  };
  /**
   * @return void
   */


  var addButton = function addButton() {
    var $editorToolbar = jQuery('#editor.block-editor__container .edit-post-header-toolbar');

    if ($editorToolbar.length === 0) {
      return;
    } // Manually append the placeholder for our component.


    var placeholderId = 'layouts-create-private-layout-button';
    $editorToolbar.append("<div id=\"".concat(placeholderId, "\"></div>"));
    react_dom__WEBPACK_IMPORTED_MODULE_0___default.a.render(wp.element.createElement(_components_PrivateLayoutCreateButton__WEBPACK_IMPORTED_MODULE_1__["default"], {
      hasPrivateLayout: hasPrivateLayout,
      isPrivateLayoutInUse: isPrivateLayoutInUse,
      postId: postId,
      editorUrl: editorUrl,
      userCanEditPrivate: userCanEditPrivate,
      postType: postType
    }), document.querySelector('#' + placeholderId));
  };
  /**
  	 * @param deferCallback
   * @return void
   * Recursive method based on time very much like setInterval()
   * It may be necessary to defer the operation several times until the editor is ready.
   */


  var deferAddingButton = function deferAddingButton(deferCallback) {
    var $editor = jQuery('#editor.block-editor__container');

    if ($editor.length === 0) {
      // This most probably means we're in the classic editor already.
      return;
    }

    var $editorToolbar = $editor.find('.edit-post-header-toolbar');

    if ($editorToolbar.length === 0) {
      setTimeout(_.partial(deferCallback, deferCallback), 1);
      return;
    }

    addButton();
  };
}; // In the block editor, show a button inside a metabox for creating or re-connect a private layout.


jQuery(document).ready(function () {
  var privateLayoutCreateButtonMain = new DDLayout.PrivateLayoutCreateButtonMain();
  privateLayoutCreateButtonMain.init();
});

/***/ }),

/***/ "@wordpress/components":
/*!***************************************!*\
  !*** external "window.wp.components" ***!
  \***************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = window.wp.components;

/***/ }),

/***/ "react-dom":
/*!***************************!*\
  !*** external "ReactDOM" ***!
  \***************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = ReactDOM;

/***/ })

/******/ });
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vd2VicGFjay9ib290c3RyYXAiLCJ3ZWJwYWNrOi8vLy4vY2xhc3Nlcy1hdXRvL2d1dGVuYmVyZy9wcml2YXRlLWxheW91dC9jc3MvY3JlYXRlLWJ1dHRvbi5jc3M/Nzk3MyIsIndlYnBhY2s6Ly8vLi9jbGFzc2VzLWF1dG8vZ3V0ZW5iZXJnL3ByaXZhdGUtbGF5b3V0L2pzL2NvbXBvbmVudHMvUHJpdmF0ZUxheW91dENyZWF0ZUJ1dHRvbi5qcyIsIndlYnBhY2s6Ly8vLi9jbGFzc2VzLWF1dG8vZ3V0ZW5iZXJnL3ByaXZhdGUtbGF5b3V0L2pzL2luZGV4LmpzIiwid2VicGFjazovLy9leHRlcm5hbCBcIndpbmRvdy53cC5jb21wb25lbnRzXCIiLCJ3ZWJwYWNrOi8vL2V4dGVybmFsIFwiUmVhY3RET01cIiJdLCJuYW1lcyI6WyJDb21wb25lbnQiLCJ3cCIsImVsZW1lbnQiLCJfXyIsImkxOG4iLCJQcml2YXRlTGF5b3V0Q3JlYXRlQnV0dG9uIiwicHJvcHMiLCJzZXRTdGF0ZSIsImlzUHJpdmF0ZUxheW91dEluVXNlIiwiZXZlbnQiLCJwcmV2ZW50RGVmYXVsdCIsInN0YXRlIiwidXNlckNhbkVkaXRQcml2YXRlIiwiaGFzUHJpdmF0ZUxheW91dCIsInVwZGF0ZVByaXZhdGVMYXlvdXQiLCJjcmVhdGVQcml2YXRlTGF5b3V0IiwiXyIsImlzT2JqZWN0IiwiRERMYXlvdXQiLCJuZXdfbGF5b3V0X2RpYWxvZyIsIk5ld0xheW91dERpYWxvZyIsInByaXZhdGVMYXlvdXROZXdUb3AiLCJjcmVhdGVMYXlvdXQiLCJsYXlvdXRJZCIsInVwZGF0ZU1hbmFnZXIiLCJVc2VMYXlvdXRzQXNQYWdlQnVpbGRlck1hbmFnZXIiLCJqUXVlcnkiLCJ0YXJnZXQiLCJ1cGRhdGVfc3RhdHVzIiwiY2xhc3NlcyIsInBvc3RJZCIsInBvc3RUeXBlIiwiaGFuZGxlQ2xpY2siLCJkb05vdGhpbmciLCJjb25zb2xlIiwibG9nIiwiZWRpdG9yVXJsIiwiYmluZCIsIlRvb2xzZXQiLCJob29rcyIsImFkZEFjdGlvbiIsInN0b3BVc2luZ1ByaXZhdGVMYXlvdXRDYWxsYmFjayIsInVwZGF0ZVByaXZhdGVMYXlvdXRTdGF0ZXMiLCJnZXRDbGFzc05hbWVzIiwiZ2V0SWNvbkJ1dHRvbiIsIlByaXZhdGVMYXlvdXRDcmVhdGVCdXR0b25NYWluIiwic2V0dGluZ3MiLCJQcml2YXRlTGF5b3V0Q3JlYXRlQnV0dG9uU2V0dGluZ3MiLCJpbml0IiwiZGVmZXJBZGRpbmdCdXR0b24iLCJhZGRCdXR0b24iLCIkZWRpdG9yVG9vbGJhciIsImxlbmd0aCIsInBsYWNlaG9sZGVySWQiLCJhcHBlbmQiLCJSZWFjdERPTSIsInJlbmRlciIsImRvY3VtZW50IiwicXVlcnlTZWxlY3RvciIsImRlZmVyQ2FsbGJhY2siLCIkZWRpdG9yIiwiZmluZCIsInNldFRpbWVvdXQiLCJwYXJ0aWFsIiwicmVhZHkiLCJwcml2YXRlTGF5b3V0Q3JlYXRlQnV0dG9uTWFpbiJdLCJtYXBwaW5ncyI6IjtBQUFBO0FBQ0E7O0FBRUE7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBOztBQUVBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBOzs7QUFHQTtBQUNBOztBQUVBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0Esa0RBQTBDLGdDQUFnQztBQUMxRTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBLGdFQUF3RCxrQkFBa0I7QUFDMUU7QUFDQSx5REFBaUQsY0FBYztBQUMvRDs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsaURBQXlDLGlDQUFpQztBQUMxRSx3SEFBZ0gsbUJBQW1CLEVBQUU7QUFDckk7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQSxtQ0FBMkIsMEJBQTBCLEVBQUU7QUFDdkQseUNBQWlDLGVBQWU7QUFDaEQ7QUFDQTtBQUNBOztBQUVBO0FBQ0EsOERBQXNELCtEQUEr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QTZCLENBQUNqQixJQUE5QjtBQUNBLENBSEQsRTs7Ozs7Ozs7Ozs7QUN0RUEsc0M7Ozs7Ozs7Ozs7O0FDQUEsMEIiLCJmaWxlIjoianMvcHJpdmF0ZS1sYXlvdXQtY3JlYXRlLWJ1dHRvbi5qcyIsInNvdXJjZXNDb250ZW50IjpbIiBcdC8vIFRoZSBtb2R1bGUgY2FjaGVcbiBcdHZhciBpbnN0YWxsZWRNb2R1bGVzID0ge307XG5cbiBcdC8vIFRoZSByZXF1aXJlIGZ1bmN0aW9uXG4gXHRmdW5jdGlvbiBfX3dlYnBhY2tfcmVxdWlyZV9fKG1vZHVsZUlkKSB7XG5cbiBcdFx0Ly8gQ2hlY2sgaWYgbW9kdWxlIGlzIGluIGNhY2hlXG4gXHRcdGlmKGluc3RhbGxlZE1vZHVsZXNbbW9kdWxlSWRdKSB7XG4gXHRcdFx0cmV0dXJuIGluc3RhbGxlZE1vZHVsZXNbbW9kdWxlSWRdLmV4cG9ydHM7XG4gXHRcdH1cbiBcdFx0Ly8gQ3JlYXRlIGEgbmV3IG1vZHVsZSAoYW5kIHB1dCBpdCBpbnRvIHRoZSBjYWNoZSlcbiBcdFx0dmFyIG1vZHVsZSA9IGluc3RhbGxlZE1vZHVsZXNbbW9kdWxlSWRdID0ge1xuIFx0XHRcdGk6IG1vZHVsZUlkLFxuIFx0XHRcdGw6IGZhbHNlLFxuIFx0XHRcdGV4cG9ydHM6IHt9XG4gXHRcdH07XG5cbiBcdFx0Ly8gRXhlY3V0ZSB0aGUgbW9kdWxlIGZ1bmN0aW9uXG4gXHRcdG1vZHVsZXNbbW9kdWxlSWRdLmNhbGwobW9kdWxlLmV4cG9ydHMsIG1vZHVsZSwgbW9kdWxlLmV4cG9ydHMsIF9fd2VicGFja19yZXF1aXJlX18pO1xuXG4gXHRcdC8vIEZsYWcgdGhlIG1vZHVsZSBhcyBsb2FkZWRcbiBcdFx0bW9kdWxlLmwgPSB0cnVlO1xuXG4gXHRcdC8vIFJldHVybiB0aGUgZXhwb3J0cyBvZiB0aGUgbW9kdWxlXG4gXHRcdHJldHVybiBtb2R1bGUuZXhwb3J0cztcbiBcdH1cblxuXG4gXHQvLyBleHBvc2UgdGhlIG1vZHVsZXMgb2JqZWN0IChfX3dlYnBhY2tfbW9kdWxlc19fKVxuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5tID0gbW9kdWxlcztcblxuIFx0Ly8gZXhwb3NlIHRoZSBtb2R1bGUgY2FjaGVcbiBcdF9fd2VicGFja19yZXF1aXJlX18uYyA9IGluc3RhbGxlZE1vZHVsZXM7XG5cbiBcdC8vIGRlZmluZSBnZXR0ZXIgZnVuY3Rpb24gZm9yIGhhcm1vbnkgZXhwb3J0c1xuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5kID0gZnVuY3Rpb24oZXhwb3J0cywgbmFtZSwgZ2V0dGVyKSB7XG4gXHRcdGlmKCFfX3dlYnBhY2tfcmVxdWlyZV9fLm8oZXhwb3J0cywgbmFtZSkpIHtcbiBcdFx0XHRPYmplY3QuZGVmaW5lUHJvcGVydHkoZXhwb3J0cywgbmFtZSwgeyBlbnVtZXJhYmxlOiB0cnVlLCBnZXQ6IGdldHRlciB9KTtcbiBcdFx0fVxuIFx0fTtcblxuIFx0Ly8gZGVmaW5lIF9fZXNNb2R1bGUgb24gZXhwb3J0c1xuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5yID0gZnVuY3Rpb24oZXhwb3J0cykge1xuIFx0XHRpZih0eXBlb2YgU3ltYm9sICE9PSAndW5kZWZpbmVkJyAmJiBTeW1ib2wudG9TdHJpbmdUYWcpIHtcbiBcdFx0XHRPYmplY3QuZGVmaW5lUHJvcGVydHkoZXhwb3J0cywgU3ltYm9sLnRvU3RyaW5nVGFnLCB7IHZhbHVlOiAnTW9kdWxlJyB9KTtcbiBcdFx0fVxuIFx0XHRPYmplY3QuZGVmaW5lUHJvcGVydHkoZXhwb3J0cywgJ19fZXNNb2R1bGUnLCB7IHZhbHVlOiB0cnVlIH0pO1xuIFx0fTtcblxuIFx0Ly8gY3JlYXRlIGEgZmFrZSBuYW1lc3BhY2Ugb2JqZWN0XG4gXHQvLyBtb2RlICYgMTogdmFsdWUgaXMgYSBtb2R1bGUgaWQsIHJlcXVpcmUgaXRcbiBcdC8vIG1vZGUgJiAyOiBtZXJnZSBhbGwgcHJvcGVydGllcyBvZiB2YWx1ZSBpbnRvIHRoZSBuc1xuIFx0Ly8gbW9kZSAmIDQ6IHJldHVybiB2YWx1ZSB3aGVuIGFscmVhZHkgbnMgb2JqZWN0XG4gXHQvLyBtb2RlICYgOHwxOiBiZWhhdmUgbGlrZSByZXF1aXJlXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLnQgPSBmdW5jdGlvbih2YWx1ZSwgbW9kZSkge1xuIFx0XHRpZihtb2RlICYgMSkgdmFsdWUgPSBfX3dlYnBhY2tfcmVxdWlyZV9fKHZhbHVlKTtcbiBcdFx0aWYobW9kZSAmIDgpIHJldHVybiB2YWx1ZTtcbiBcdFx0aWYoKG1vZGUgJiA0KSAmJiB0eXBlb2YgdmFsdWUgPT09ICdvYmplY3QnICYmIHZhbHVlICYmIHZhbHVlLl9fZXNNb2R1bGUpIHJldHVybiB2YWx1ZTtcbiBcdFx0dmFyIG5zID0gT2JqZWN0LmNyZWF0ZShudWxsKTtcbiBcdFx0X193ZWJwYWNrX3JlcXVpcmVfXy5yKG5zKTtcbiBcdFx0T2JqZWN0LmRlZmluZVByb3BlcnR5KG5zLCAnZGVmYXVsdCcsIHsgZW51bWVyYWJsZTogdHJ1ZSwgdmFsdWU6IHZhbHVlIH0pO1xuIFx0XHRpZihtb2RlICYgMiAmJiB0eXBlb2YgdmFsdWUgIT0gJ3N0cmluZycpIGZvcih2YXIga2V5IGluIHZhbHVlKSBfX3dlYnBhY2tfcmVxdWlyZV9fLmQobnMsIGtleSwgZnVuY3Rpb24oa2V5KSB7IHJldHVybiB2YWx1ZVtrZXldOyB9LmJpbmQobnVsbCwga2V5KSk7XG4gXHRcdHJldHVybiBucztcbiBcdH07XG5cbiBcdC8vIGdldERlZmF1bHRFeHBvcnQgZnVuY3Rpb24gZm9yIGNvbXBhdGliaWxpdHkgd2l0aCBub24taGFybW9ueSBtb2R1bGVzXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLm4gPSBmdW5jdGlvbihtb2R1bGUpIHtcbiBcdFx0dmFyIGdldHRlciA9IG1vZHVsZSAmJiBtb2R1bGUuX19lc01vZHVsZSA/XG4gXHRcdFx0ZnVuY3Rpb24gZ2V0RGVmYXVsdCgpIHsgcmV0dXJuIG1vZHVsZVsnZGVmYXVsdCddOyB9IDpcbiBcdFx0XHRmdW5jdGlvbiBnZXRNb2R1bGVFeHBvcnRzKCkgeyByZXR1cm4gbW9kdWxlOyB9O1xuIFx0XHRfX3dlYnBhY2tfcmVxdWlyZV9fLmQoZ2V0dGVyLCAnYScsIGdldHRlcik7XG4gXHRcdHJldHVybiBnZXR0ZXI7XG4gXHR9O1xuXG4gXHQvLyBPYmplY3QucHJvdG90eXBlLmhhc093blByb3BlcnR5LmNhbGxcbiBcdF9fd2VicGFja19yZXF1aXJlX18ubyA9IGZ1bmN0aW9uKG9iamVjdCwgcHJvcGVydHkpIHsgcmV0dXJuIE9iamVjdC5wcm90b3R5cGUuaGFzT3duUHJvcGVydHkuY2FsbChvYmplY3QsIHByb3BlcnR5KTsgfTtcblxuIFx0Ly8gX193ZWJwYWNrX3B1YmxpY19wYXRoX19cbiBcdF9fd2VicGFja19yZXF1aXJlX18ucCA9IFwiXCI7XG5cblxuIFx0Ly8gTG9hZCBlbnRyeSBtb2R1bGUgYW5kIHJldHVybiBleHBvcnRzXG4gXHRyZXR1cm4gX193ZWJwYWNrX3JlcXVpcmVfXyhfX3dlYnBhY2tfcmVxdWlyZV9fLnMgPSBcIi4vY2xhc3Nlcy1hdXRvL2d1dGVuYmVyZy9wcml2YXRlLWxheW91dC9qcy9pbmRleC5qc1wiKTtcbiIsIi8vIGV4dHJhY3RlZCBieSBtaW5pLWNzcy1leHRyYWN0LXBsdWdpbiIsIi8qKlxuICogQHNpbmNlIDIuNS4yXG4gKiBAYXV0aG9yIFJpY2NhcmRvIFN0cm9iYmlhXG4gKiBBIHdyYXBwZXIgZm9yIGEgYnV0dG9uIHRvIGNyZWF0ZSBhIHByaXZhdGUgbGF5b3V0IG9yIHJlLWNvbm5lY3QgYW4gZXhpc3Rpbmcgb25lIGFuZCByZWRpcmVjdCB0byBpdHMgZWRpdG9yXG4gKi9cbmltcG9ydCB7IEljb25CdXR0b24gfSBmcm9tICdAd29yZHByZXNzL2NvbXBvbmVudHMnO1xuY29uc3QgeyBDb21wb25lbnQgfSA9IHdwLmVsZW1lbnQ7XG5jb25zdCB7IF9fIH0gPSB3cC5pMThuO1xuXG5jbGFzcyBQcml2YXRlTGF5b3V0Q3JlYXRlQnV0dG9uIGV4dGVuZHMgQ29tcG9uZW50IHtcblx0LyoqXG5cdCAqXG5cdCAqIEBwYXJhbSBwcm9wc1xuXHQgKi9cblx0Y29uc3RydWN0b3IoIHByb3BzICkge1xuXHRcdHN1cGVyKCBwcm9wcyApO1xuXG5cdFx0dGhpcy5zdGF0ZSA9IHtcblx0XHRcdGhhc1ByaXZhdGVMYXlvdXQ6IHRoaXMucHJvcHMuaGFzUHJpdmF0ZUxheW91dCA/IHRydWUgOiBmYWxzZSxcblx0XHRcdGlzUHJpdmF0ZUxheW91dEluVXNlOiB0aGlzLnByb3BzLmlzUHJpdmF0ZUxheW91dEluVXNlID8gdHJ1ZSA6IGZhbHNlLFxuXHRcdFx0ZWRpdG9yVXJsOiB0aGlzLnByb3BzLmVkaXRvclVybCxcblx0XHRcdHBvc3RJZDogdGhpcy5wcm9wcy5wb3N0SWQsXG5cdFx0XHRwb3N0VHlwZTogdGhpcy5wcm9wcy5wb3N0VHlwZSxcblx0XHRcdHVzZXJDYW5FZGl0UHJpdmF0ZTogdGhpcy5wcm9wcy51c2VyQ2FuRWRpdFByaXZhdGUgPT09ICcxJyA/IHRydWUgOiBmYWxzZSxcblx0XHR9O1xuXG5cdFx0dGhpcy5oYW5kbGVDbGljay5iaW5kKCB0aGlzICk7XG5cdFx0VG9vbHNldC5ob29rcy5hZGRBY3Rpb24oICdkZGxfcHJpdmF0ZV9sYXlvdXRfdXNhZ2Vfc3RvcHBlZCcsIHRoaXMuc3RvcFVzaW5nUHJpdmF0ZUxheW91dENhbGxiYWNrICk7XG5cdFx0VG9vbHNldC5ob29rcy5hZGRBY3Rpb24oICdkZGxfcHJpdmF0ZV9sYXlvdXRfY3JlYXRlZCcsIHRoaXMudXBkYXRlUHJpdmF0ZUxheW91dFN0YXRlcywgMTAsIDEgKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBAcmV0dXJuIHZvaWRcblx0ICovXG5cdHN0b3BVc2luZ1ByaXZhdGVMYXlvdXRDYWxsYmFjayA9ICgpID0+IHtcblx0XHR0aGlzLnNldFN0YXRlKCB7IGlzUHJpdmF0ZUxheW91dEluVXNlOiBmYWxzZSB9ICk7XG5cdH1cblx0LyoqXG5cdCAqXG5cdCAqIEBwYXJhbSBldmVudFxuXHQgKiBAcmV0dXJuIHtib29sZWFufVxuXHQgKi9cblx0aGFuZGxlQ2xpY2sgPSAoIGV2ZW50ICkgPT4ge1xuXHRcdGV2ZW50LnByZXZlbnREZWZhdWx0KCk7XG5cdFx0aWYgKCAhIHRoaXMuc3RhdGUudXNlckNhbkVkaXRQcml2YXRlICkge1xuXHRcdFx0cmV0dXJuIGZhbHNlO1xuXHRcdH1cblx0XHRpZiAoIHRoaXMuc3RhdGUuaGFzUHJpdmF0ZUxheW91dCAmJiAhIHRoaXMuc3RhdGUuaXNQcml2YXRlTGF5b3V0SW5Vc2UgKSB7XG5cdFx0XHR0aGlzLnNldFN0YXRlKCB7IGlzUHJpdmF0ZUxheW91dEluVXNlOiB0cnVlIH0gKTtcblx0XHRcdHRoaXMudXBkYXRlUHJpdmF0ZUxheW91dCggZXZlbnQgKTtcblx0XHR9IGVsc2UgaWYgKCAhIHRoaXMuc3RhdGUuaGFzUHJpdmF0ZUxheW91dCAmJiAhIHRoaXMuc3RhdGUuaXNQcml2YXRlTGF5b3V0SW5Vc2UgKSB7XG5cdFx0XHR0aGlzLmNyZWF0ZVByaXZhdGVMYXlvdXQoIGV2ZW50ICk7XG5cdFx0fVxuXHR9XG5cdC8qKlxuXHQgKlxuXHQgKiBAcGFyYW0gZXZlbnRcblx0ICovXG5cdGNyZWF0ZVByaXZhdGVMYXlvdXQgPSAoZXZlbnQgKSA9PiB7XG5cdFx0aWYgKCBfLmlzT2JqZWN0KCBERExheW91dCApICYmIERETGF5b3V0Lm5ld19sYXlvdXRfZGlhbG9nIGluc3RhbmNlb2YgRERMYXlvdXQuTmV3TGF5b3V0RGlhbG9nICkge1xuXHRcdFx0RERMYXlvdXQubmV3X2xheW91dF9kaWFsb2cucHJpdmF0ZUxheW91dE5ld1RvcCggZXZlbnQgKTtcblx0XHR9IGVsc2Uge1xuXHRcdFx0Y29uc3QgY3JlYXRlTGF5b3V0ID0gbmV3IERETGF5b3V0Lk5ld0xheW91dERpYWxvZygpO1xuXHRcdFx0Y3JlYXRlTGF5b3V0LnByaXZhdGVMYXlvdXROZXdUb3AoIGV2ZW50ICk7XG5cdFx0fVxuXHR9XG5cdC8qKlxuXHQgKlxuXHQgKiBAcGFyYW0gbGF5b3V0SWRcblx0ICovXG5cdHVwZGF0ZVByaXZhdGVMYXlvdXRTdGF0ZXMgPSAoIGxheW91dElkICkgPT4ge1xuXHRcdGlmICh0eXBlb2YgbGF5b3V0SWQgIT09ICd1bmRlZmluZWQnICYmIGxheW91dElkICE9PSAwKXtcblx0XHRcdHRoaXMuc2V0U3RhdGUoIHsgaGFzUHJpdmF0ZUxheW91dDogdHJ1ZSwgaXNQcml2YXRlTGF5b3V0SW5Vc2U6IHRydWUgfSApO1xuXHRcdH1cblx0fVxuXHQvKipcblx0ICpcblx0ICogQHBhcmFtIGV2ZW50XG5cdCAqL1xuXHR1cGRhdGVQcml2YXRlTGF5b3V0ID0gKGV2ZW50ICkgPT4ge1xuXHRcdGNvbnN0IHVwZGF0ZU1hbmFnZXIgPSBuZXcgRERMYXlvdXQuVXNlTGF5b3V0c0FzUGFnZUJ1aWxkZXJNYW5hZ2VyKCBqUXVlcnksIGpRdWVyeSggZXZlbnQudGFyZ2V0ICkgKTtcblx0XHR1cGRhdGVNYW5hZ2VyLnVwZGF0ZV9zdGF0dXMoIGV2ZW50ICk7XG5cdH1cblx0LyoqXG5cdCAqXG5cdCAqIEByZXR1cm4ge3N0cmluZ31cblx0ICovXG5cdGdldENsYXNzTmFtZXMgPSAoKSA9PiB7XG5cdFx0Y29uc3QgY2xhc3NlcyA9ICdsYXlvdXRzLWNyZWF0ZS1wcml2YXRlLWxheW91dC1idXR0b24td3JhcCc7XG5cblx0XHRyZXR1cm4gdGhpcy5zdGF0ZS5pc1ByaXZhdGVMYXlvdXRJblVzZSA/IGNsYXNzZXMgKyAnIGhpZGRlbicgOiBjbGFzc2VzO1xuXHR9XG5cdC8qKlxuXHQgKlxuXHQgKiBAcmV0dXJuIHsqfVxuXHQgKi9cblx0Z2V0SWNvbkJ1dHRvbiA9ICgpID0+IHtcblx0XHRpZiAoIHRoaXMuc3RhdGUudXNlckNhbkVkaXRQcml2YXRlICkge1xuXHRcdFx0cmV0dXJuICggPEljb25CdXR0b25cblx0XHRcdFx0ZGF0YS1sYXlvdXRfdHlwZT1cInByaXZhdGVcIlxuXHRcdFx0XHRkYXRhLWxheW91dF9pZD17IHRoaXMuc3RhdGUucG9zdElkIH1cblx0XHRcdFx0ZGF0YS1wb3N0X3R5cGU9eyB0aGlzLnN0YXRlLnBvc3RUeXBlIH1cblx0XHRcdFx0ZGF0YS1jb250ZW50X2lkPXsgdGhpcy5zdGF0ZS5wb3N0SWQgfVxuXHRcdFx0XHRkYXRhLWVkaXRvcj1cImVkaXRvclwiXG5cdFx0XHRcdGlzRGVmYXVsdFxuXHRcdFx0XHRpc0xhcmdlXG5cdFx0XHRcdG9uQ2xpY2s9eyB0aGlzLmhhbmRsZUNsaWNrIH1cblx0XHRcdFx0Y2xhc3NOYW1lPVwiYnV0dG9uLXByaW1hcnktdG9vbHNldCBqcy1sYXlvdXQtcHJpdmF0ZS11c2VcIlxuXHRcdFx0XHRpY29uPVwiPGkgY2xhc3M9J2ljb24tbGF5b3V0cy1sb2dvIGZhIGZhLXdwdi1jdXN0b20gb250LWljb24tMTggb250LWNvbG9yLXdoaXRlJz48L2k+XCJcblx0XHRcdD57IF9fKCAnQ29udGVudCBMYXlvdXQgRWRpdG9yJywgJ2RkbC1sYXlvdXRzJyApIH08L0ljb25CdXR0b24+ICk7XG5cdFx0fSBlbHNlIHtcblx0XHRcdHJldHVybiAoIDxJY29uQnV0dG9uXG5cdFx0XHRcdGlzRGVmYXVsdFxuXHRcdFx0XHRpc0xhcmdlXG5cdFx0XHRcdG9uQ2xpY2s9eyB0aGlzLmRvTm90aGluZyB9XG5cdFx0XHRcdGNsYXNzTmFtZT1cImJ1dHRvbi1wcmltYXJ5LXRvb2xzZXQganMtbGF5b3V0LXByaXZhdGUtdXNlXCJcblx0XHRcdFx0aWNvbj1cIjxpIGNsYXNzPSdpY29uLWxheW91dHMtbG9nbyBmYSBmYS13cHYtY3VzdG9tIG9udC1pY29uLTE4IG9udC1jb2xvci13aGl0ZSc+PC9pPlwiXG5cdFx0XHRcdGRpc2FibGVkXG5cdFx0XHQ+eyBfXyggJ0NvbnRlbnQgTGF5b3V0IEVkaXRvcicsICdkZGwtbGF5b3V0cycgKSB9PC9JY29uQnV0dG9uPiApO1xuXHRcdH1cblx0fVxuXHQvKipcblx0ICpcblx0ICogQHBhcmFtIGV2ZW50XG5cdCAqIEByZXR1cm4ge2Jvb2xlYW59XG5cdCAqL1xuXHRkb05vdGhpbmcgPSAoIGV2ZW50ICkgPT4ge1xuXHRcdGV2ZW50LnByZXZlbnREZWZhdWx0KCk7XG5cdFx0Y29uc29sZS5sb2coIF9fKCAnVXNlciBkb2VzblxcJ3QgaGF2ZSB0aGUgcmlnaHRzIHRvIGVkaXQgbGF5b3V0cycsICdkZGwtbGF5b3V0cycgKSApO1xuXHRcdHJldHVybiBmYWxzZTtcblx0fVxuXG5cdC8qKlxuXHQgKlxuXHQgKiBAcmV0dXJuIHsqfVxuXHQgKi9cblx0cmVuZGVyKCkge1xuXHRcdHJldHVybiAoXG5cdFx0XHQ8ZGl2IGNsYXNzTmFtZT17IHRoaXMuZ2V0Q2xhc3NOYW1lcygpIH0+XG5cdFx0XHRcdHsgdGhpcy5nZXRJY29uQnV0dG9uKCkgfTwvZGl2PlxuXHRcdCk7XG5cdH1cbn1cblxuZXhwb3J0IGRlZmF1bHQgUHJpdmF0ZUxheW91dENyZWF0ZUJ1dHRvbjtcbiIsIi8qKlxuICogVGhpcyB3aWxsIGxvYWQgb24gdGhlIEFkZCBvciBFZGl0IFBvc3QgcGFnZXMgaWYgdGhlIHdwLWNvbXBvbmVudHMgZGVwZW5kZW5jeSBpcyBhdmFpbGFibGUgKFdvcmRQcmVzcyA1LjArIG9yXG4gKiBHdXRlbmJlcmcgcGx1Z2luIGFjdGl2ZSkgYW5kIGFkZCBhbiBlbGVtZW50IHRvIHRoZSB0b3AgYmFyLlxuICpcbiAqIEBzaW5jZSAyLjUuMlxuICovXG5pbXBvcnQgUmVhY3RET00gZnJvbSAncmVhY3QtZG9tJztcbmltcG9ydCBQcml2YXRlTGF5b3V0Q3JlYXRlQnV0dG9uIGZyb20gJy4vY29tcG9uZW50cy9Qcml2YXRlTGF5b3V0Q3JlYXRlQnV0dG9uJztcbmltcG9ydCAnLi4vY3NzL2NyZWF0ZS1idXR0b24uY3NzJztcblxuLyoqXG4gKlxuICogQHR5cGUgeyp8e319XG4gKi9cbmNvbnN0IERETGF5b3V0ID0gRERMYXlvdXQgfHwge307XG4vKipcbiAqXG4gKiBAY29uc3RydWN0b3JcbiAqL1xuRERMYXlvdXQuUHJpdmF0ZUxheW91dENyZWF0ZUJ1dHRvbk1haW4gPSBmdW5jdGlvbigpIHtcblx0Y29uc3Qgc2V0dGluZ3MgPSBQcml2YXRlTGF5b3V0Q3JlYXRlQnV0dG9uU2V0dGluZ3MsXG5cdFx0eyBoYXNQcml2YXRlTGF5b3V0LCBpc1ByaXZhdGVMYXlvdXRJblVzZSwgcG9zdElkLCBlZGl0b3JVcmwsIHVzZXJDYW5FZGl0UHJpdmF0ZSwgcG9zdFR5cGUgfSA9IHNldHRpbmdzO1xuXHQvKipcblx0ICogQHJldHVybiB2b2lkXG5cdCAqL1xuXHR0aGlzLmluaXQgPSAoKSA9PiB7XG5cdFx0ZGVmZXJBZGRpbmdCdXR0b24oIGRlZmVyQWRkaW5nQnV0dG9uICk7XG5cdH07XG5cdC8qKlxuXHQgKiBAcmV0dXJuIHZvaWRcblx0ICovXG5cdGNvbnN0IGFkZEJ1dHRvbiA9IGZ1bmN0aW9uKCkge1xuXHRcdGNvbnN0ICRlZGl0b3JUb29sYmFyID0galF1ZXJ5KCAnI2VkaXRvci5ibG9jay1lZGl0b3JfX2NvbnRhaW5lciAuZWRpdC1wb3N0LWhlYWRlci10b29sYmFyJyApO1xuXHRcdGlmICggJGVkaXRvclRvb2xiYXIubGVuZ3RoID09PSAwICkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdC8vIE1hbnVhbGx5IGFwcGVuZCB0aGUgcGxhY2Vob2xkZXIgZm9yIG91ciBjb21wb25lbnQuXG5cdFx0Y29uc3QgcGxhY2Vob2xkZXJJZCA9ICdsYXlvdXRzLWNyZWF0ZS1wcml2YXRlLWxheW91dC1idXR0b24nO1xuXHRcdCRlZGl0b3JUb29sYmFyLmFwcGVuZCggYDxkaXYgaWQ9XCIkeyBwbGFjZWhvbGRlcklkIH1cIj48L2Rpdj5gICk7XG5cblx0XHRSZWFjdERPTS5yZW5kZXIoIDxQcml2YXRlTGF5b3V0Q3JlYXRlQnV0dG9uIGhhc1ByaXZhdGVMYXlvdXQ9eyBoYXNQcml2YXRlTGF5b3V0IH1cblx0XHRcdGlzUHJpdmF0ZUxheW91dEluVXNlPXsgaXNQcml2YXRlTGF5b3V0SW5Vc2UgfSBwb3N0SWQ9eyBwb3N0SWQgfSBlZGl0b3JVcmw9eyBlZGl0b3JVcmwgfVxuXHRcdFx0dXNlckNhbkVkaXRQcml2YXRlPXsgdXNlckNhbkVkaXRQcml2YXRlIH0gcG9zdFR5cGU9eyBwb3N0VHlwZSB9IC8+LCBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnIycgKyBwbGFjZWhvbGRlcklkICkgKTtcblx0fTtcblxuXHQvKipcbiBcdCAqIEBwYXJhbSBkZWZlckNhbGxiYWNrXG5cdCAqIEByZXR1cm4gdm9pZFxuXHQgKiBSZWN1cnNpdmUgbWV0aG9kIGJhc2VkIG9uIHRpbWUgdmVyeSBtdWNoIGxpa2Ugc2V0SW50ZXJ2YWwoKVxuXHQgKiBJdCBtYXkgYmUgbmVjZXNzYXJ5IHRvIGRlZmVyIHRoZSBvcGVyYXRpb24gc2V2ZXJhbCB0aW1lcyB1bnRpbCB0aGUgZWRpdG9yIGlzIHJlYWR5LlxuXHQgKi9cblx0Y29uc3QgZGVmZXJBZGRpbmdCdXR0b24gPSBmdW5jdGlvbiggZGVmZXJDYWxsYmFjayApIHtcblx0XHRjb25zdCAkZWRpdG9yID0galF1ZXJ5KCAnI2VkaXRvci5ibG9jay1lZGl0b3JfX2NvbnRhaW5lcicgKTtcblx0XHRpZiAoICRlZGl0b3IubGVuZ3RoID09PSAwICkge1xuXHRcdFx0Ly8gVGhpcyBtb3N0IHByb2JhYmx5IG1lYW5zIHdlJ3JlIGluIHRoZSBjbGFzc2ljIGVkaXRvciBhbHJlYWR5LlxuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblxuXHRcdGNvbnN0ICRlZGl0b3JUb29sYmFyID0gJGVkaXRvci5maW5kKCAnLmVkaXQtcG9zdC1oZWFkZXItdG9vbGJhcicgKTtcblx0XHRpZiAoICRlZGl0b3JUb29sYmFyLmxlbmd0aCA9PT0gMCApIHtcblx0XHRcdHNldFRpbWVvdXQoIF8ucGFydGlhbCggZGVmZXJDYWxsYmFjaywgZGVmZXJDYWxsYmFjayApLCAxICk7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXG5cdFx0YWRkQnV0dG9uKCk7XG5cdH07XG59O1xuXG4vLyBJbiB0aGUgYmxvY2sgZWRpdG9yLCBzaG93IGEgYnV0dG9uIGluc2lkZSBhIG1ldGFib3ggZm9yIGNyZWF0aW5nIG9yIHJlLWNvbm5lY3QgYSBwcml2YXRlIGxheW91dC5cbmpRdWVyeSggZG9jdW1lbnQgKS5yZWFkeSggZnVuY3Rpb24oKSB7XG5cdGNvbnN0IHByaXZhdGVMYXlvdXRDcmVhdGVCdXR0b25NYWluID0gbmV3IERETGF5b3V0LlByaXZhdGVMYXlvdXRDcmVhdGVCdXR0b25NYWluKCk7XG5cdHByaXZhdGVMYXlvdXRDcmVhdGVCdXR0b25NYWluLmluaXQoKTtcbn0gKTtcbiIsIm1vZHVsZS5leHBvcnRzID0gd2luZG93LndwLmNvbXBvbmVudHM7IiwibW9kdWxlLmV4cG9ydHMgPSBSZWFjdERPTTsiXSwic291cmNlUm9vdCI6IiJ9