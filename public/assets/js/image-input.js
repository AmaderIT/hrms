'use strict';

// Class definition
var KTImageInputDemo = function () {
    // Private functions
    var initDemos = function () {
        new KTImageInput('kt_image_4');
    }

    return {
        init: function() {
            initDemos();
        }
    };
}();

KTUtil.ready(function() {
    KTImageInputDemo.init();
});
