"use strict";

(function(window, $) {
    window.AO = window.AO || {};

    function correct_fg_ids( alchemy, values ) {
        let neededTypeFields = alchemy['fields-data'];

        $.each(values, (i, value) => {
            value['id'] = neededTypeFields[i]['id'];
        });

        return values;
    }

    AO.get_field_group_value = id => {
        const $field = $(`#${id}`);
        const fields = $field.children('.jsAlchemyField').map((i, field) => {
            const alchemy = $(field).data('alchemy');

            return new Function( `return AO['get_${alchemy.type}_value']('${alchemy.id}');` )(alchemy.id)
        }).get()

        return Promise.all(fields).then(valuesData => {
            return Promise.resolve( {
                'type': 'field_group',
                'id': id,
                'value': correct_fg_ids( $field.data('alchemy'), valuesData )
            } );
        });
    };
})(window, jQuery);