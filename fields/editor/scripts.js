"use strict";

(function(window, document, $){
    window.AO = window.AO || {};

    const $document = $(document);

    AO.tinymce = {
        create_editors: $editors => {
            $editors.each((i, el) => {
                const $editor = $(el);
                const settings = $.extend({}, AO.tinymce.tweak_editor_settings( $editor.attr('id') ), {
                    init_instance_callback: editor => {
                        const $container = $(editor.editorContainer);

                        if( $container[0] ) {
                            $container.addClass('wp-editor-container').before(
                                `<div id="wp-${editor.id}-editor-tools" class="wp-editor-tools hide-if-no-js">
                                    <div id="wp-${editor.id}-media-buttons" class="wp-media-buttons">
                                    <button type="button" class="button insert-media add_media" data-editor="${editor.id}">
                                    <span class="wp-media-buttons-icon"></span> Add Media
                                    </button>
                                    </div>
                                </div>`);

                        }
                    },
                    min_height: 250
                });

                if( typeof tinymce !== 'undefined' && ! $editor.hasClass('tinymce--init') ) {
                    tinymce.init(settings);

                    $editor.addClass('tinymce--init').on('mousedown', function (e) {
                        e.preventDefault();
                    });
                }
            });
        },
        destroy_editors: $editors => {
            $editors.each((i, el) => {
                const $el = $(el);
                const tmcEditor = tinymce.get($el.attr('id'));

                $el.removeClass('tinymce--init');

                if( tmcEditor ) {
                    $el.val(tmcEditor.getContent());

                    tmcEditor.destroy();
                }
            });
        },
        save_editors($editors) {
            $editors.each((i, el) => {
                const $editor = $(el);

                if( $editor.hasClass('tinymce--init') && typeof( tinymce ) !== 'undefined' ) {
                    const $field = $editor.closest('.field--editor');
                    const selfHeight = $editor.closest('.field--editor').outerHeight();
                    const editorHeight = $editor.prev('.mce-tinymce').outerHeight();
                    const editorWidth = $editor.prev('.mce-tinymce').width();

                    $editor.next('.field__cover').height(editorHeight).width(editorWidth);
                    $field.height(selfHeight).addClass('tinymce--destroyed');

                    tinymce.get($editor.attr('id')).destroy();
                }
            });
        },
        restore_editors($editors) {
            $editors.each((i, el) => {
                const $editor = $(el);
                const $field = $editor.closest('.field--editor');

                if( $field.hasClass('tinymce--destroyed') && $editor.hasClass('tinymce--init') && typeof( tinymce ) !== 'undefined' ) {

                    const settings = AO.tinymce.tweak_editor_settings($editor.attr('id'));

                    settings.min_height = 250;
                    tinymce.init(settings);

                    $editor.next('.field__cover').removeAttr('style');
                    $field.removeAttr('style').removeClass('tinymce--destroyed');
                }
            });
        },
        tweak_editor_settings: id => {
            const tweakedSettings = {};

            $.each(AO.tinymce.initialSettings, (key, val) => {
                let newVal = val;

                if( 'string' === typeof val ) {
                    newVal = val.replace('alchemy-temp-editor', id);
                }

                tweakedSettings[key] = newVal;
            });

            return tweakedSettings;
        }
    };

    $(() => {
        let $editors = $('.jsAlchemyEditor');

        if( $editors[0] ) {
            $editors = $editors.filter((i, el) => {
                return $(el).closest('.repeatee').length === 0
            });

            $document.on('tinymce-editor-init', () => {
                AO.tinymce.create_editors($editors);
            });
        }
    });

    $document.on('alch_repeatee_added', (e, data) => {
        const $repeatee = data.repeatee;
        const $editors = $repeatee.children('.repeatee__fields').find('.field--editor').find('.jsAlchemyEditor');

        if( $editors[0] ) {
            AO.tinymce.create_editors( $editors );
        }
    });

    $document.on('alch_repeatee_closed', (e, data) => {
        const $repeatee = data.repeatee;
        const $editors = $repeatee.children('.repeatee__fields').find('.field--editor');

        if( $editors[0] ) {
            $editors.find('.wp-editor-tools').remove();

            AO.tinymce.destroy_editors( $editors.find('.jsAlchemyEditor') );
        }
    });

    $document.on('alch_repeatee_opened', (e, data) => {
        const $repeatee = data.repeatee;
        const $editors = $repeatee.children('.repeatee__fields').find('.field--editor');

        if( $editors[0] ) {
            AO.tinymce.create_editors( $editors.find('.jsAlchemyEditor') );
        }
    });

    AO.get_editor_value = id => {
        const $area = $(`#${id}`);

        let value = '';

        if( $area.hasClass('tinymce--init') && typeof( tinymce ) !== 'undefined' ) {
            value = tinymce.get(id).getContent();
        } else {
            value = $area.val()
        }

        return Promise.resolve( {
            'type': 'editor',
            'id': id,
            'value': value
        } );
    };
})(window, document, jQuery);