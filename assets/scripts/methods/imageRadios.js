export default function(scope = document) {
    const $imageRadios = $('.jsAlchemyImageRadios', scope);

    if( $imageRadios[0] ) {
        $imageRadios.each((i, el) => {
            const $el = $(el);
            const $labels = $el.find('label');

            $labels.filter((i, el) => {
                return ! $(el).hasClass('field__image-label--disabled')
            }).attr('tabindex', 0);
            $el.find('input').attr('tabindex', -1);

            $el.on('click', '.jsAlchemyImageRadioLabel', function(){
                markLabel($labels, $(this));
            });

            $el.on('keypress', '.jsAlchemyImageRadioLabel', function(e){
                if( $.inArray( e.which, [13, 32] ) !== -1 ) {
                    e.preventDefault();

                    markLabel($labels, $(this));
                }
            });
        });

        function markLabel(labels, label){
            if( label.hasClass( 'field__image-label--disabled' ) ) {
                return;
            }

            labels.removeClass('field__image-label--active');
            label.addClass('field__image-label--active').prev('input').prop('checked', true);
        }
    }
}