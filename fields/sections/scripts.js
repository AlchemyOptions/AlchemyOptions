"use strict";

(function(window, $) {
    const $sections = $('.jsAlchemySectionsField');

    if( $sections[0] ) {
        $sections.each((i, el) => {
            const $section = $(el);
            const $tabs = $section.children('.field__content').children('.jsAlchemySectionsTabs').children('.jsAlchemySectionsTab');
            const $tabBtns = $section.children('.field__content').children('.jsAlchemySectionsNav').children('.jsAlchemySectionsButton');

            $section.on('click', '.jsAlchemySectionsButton', function() {
                const $btn = $(this);

                $tabBtns.removeClass('field__tab-btn--active');
                $btn.addClass('field__tab-btn--active');

                $tabs.hide().filter((i, el) => {
                    return $(el).data('controlled-by') === $btn.data('controls')
                }).show();
            });
        });
    }
})(window, jQuery);