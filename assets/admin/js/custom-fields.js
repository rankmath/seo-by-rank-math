!function(){"use strict";var t={n:function(n){var e=n&&n.__esModule?function(){return n.default}:function(){return n};return t.d(e,{a:e}),e},d:function(n,e){for(var o in e)t.o(e,o)&&!t.o(n,o)&&Object.defineProperty(n,o,{enumerable:!0,get:e[o]})},o:function(t,n){return Object.prototype.hasOwnProperty.call(t,n)}},n=jQuery,e=t.n(n),o=lodash;function r(t){return r="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},r(t)}function i(t,n){for(var e=0;e<n.length;e++){var o=n[e];o.enumerable=o.enumerable||!1,o.configurable=!0,"value"in o&&(o.writable=!0),Object.defineProperty(t,(i=o.key,u=void 0,u=function(t,n){if("object"!==r(t)||null===t)return t;var e=t[Symbol.toPrimitive];if(void 0!==e){var o=e.call(t,n||"default");if("object"!==r(o))return o;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===n?String:Number)(t)}(i,"string"),"symbol"===r(u)?u:String(u)),o)}var i,u}var u=function(){function t(){!function(t,n){if(!(t instanceof n))throw new TypeError("Cannot call a class as a function")}(this,t),this.getContent=function(t){return e()(this.fields).each((function(n,o){t+=e()(o).val()})),t},this.init(),this.hooks(),this.events()}var n,r,u;return n=t,(r=[{key:"init",value:function(){this.fields=this.getFields(),this.getContent=this.getContent.bind(this)}},{key:"hooks",value:function(){wp.hooks.addFilter("rank_math_content","rank-math",this.getContent,11)}},{key:"events",value:function(){e()(this.fields).each((function(t,n){e()(n).on("keyup change",(0,o.debounce)((function(){rankMathEditor.refresh("content")}),500))}))}},{key:"getFields",value:function(){var t=[];return e()("#the-list > tr:visible").each((function(n,o){var r=e()("#"+o.id+"-key").val();-1!==e().inArray(r,rankMath.analyzeFields)&&t.push("#"+o.id+"-value")})),t}}])&&i(n.prototype,r),u&&i(n,u),Object.defineProperty(n,"prototype",{writable:!1}),t}();e()((function(){setTimeout((function(){new u}),500)}))}();