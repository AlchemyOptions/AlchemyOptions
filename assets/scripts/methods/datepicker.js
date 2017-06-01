export default function () {
    const $datepickers = $('.jsAlchemyDatepickerInput');

    if( $datepickers[0] ) {
        $datepickers.each((i, el) => {
            const $datepicker = $(el);

            $datepicker.datepicker({
                dateFormat: 'yy-mm-dd',
            });
        });
    }
}