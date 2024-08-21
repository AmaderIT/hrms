// Class definition
var KTFormRepeater = function() {
    var demo = function() {
        $('#kt_repeater_1').repeater({
            initEmpty: false,
            defaultValues: { 'text-input': 'foo' },
            show: function () {
                $(this).slideDown();
                $('.item').select2();
                },
            hide: function (deleteElement) { $(this).slideUp(deleteElement); }
        });

        $('#kt_repeater_2').repeater({
            initEmpty: false,
            defaultValues: { 'text-input': 'foo' },
            show: function() { $(this).slideDown(); },
            hide: function(deleteElement) { $(this).slideUp(deleteElement);}
        });

        $('#kt_repeater_3').repeater({
            initEmpty: false,
            defaultValues: { 'text-input': 'foo' },
            show: function() { $(this).slideDown(); },
            hide: function(deleteElement) { $(this).slideUp(deleteElement);}
        });
    };

    return {
        init: function() {
            demo();
        }
    };
}();

jQuery(document).ready(function() {
    KTFormRepeater.init();
});
