"use strict";

(function(window, $){
    window.AO = window.AO || {};

    AO.get_textarea_value = id => {
        return Promise.resolve( {
            'type': 'textarea',
            'id': id,
            'value': $(`#${id}`).val()
        } );
    };
})(window, jQuery);