export default function (scope = document) {
    const $datepickers = $('.jsAlchemyDatepickerInput', scope);

    if( $datepickers[0] ) {
        $datepickers.each((i, el) => {
            const $datepicker = $(el);

            $datepicker.datepicker({
                dateFormat: 'yy-mm-dd',
            });
        });
    }
}