export default function() {
    const $repeaterFields = $('.jsRepeaterField');

    if( $repeaterFields[0] ) {
        $repeaterFields.each((i, el) => {
            const $repeater = $(el);

            $repeater.on('click', '.jsRepeaterAdd', function() {
                const $btn = $(this);
                const repeatees = $btn.data('repeatees');

                console.log(repeatees);
            });
        });
    }
};
