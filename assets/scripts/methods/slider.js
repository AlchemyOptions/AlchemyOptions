export default function (scope = document) {
    const $sliders = $('.jsAlchemySlider', scope);

    if( $sliders[0] ) {
        $sliders.each((i, el) => {
            const $slider = $(el);
            const data = $slider.data('values');

            if( ! data ) {
                return;
            }

            const $input = $slider.next('.jsAlchemySliderInput');
            const min = data.min ? data.min : 0;
            const max = data.max ? data.max : 100;
            const step = data.step ? data.step : 1;

            $slider.slider({
                min: min,
                max: max,
                range: "min",
                step: step,
                value: $input.val(),
                slide: function( event, ui ) {
                    $input.val(ui.value);
                }
            });
        });
    }
}