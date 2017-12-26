export default function(scope = document) {
    let $editors = $('.jsAlchemyEditorTextarea', scope);

    if( $editors ) {
        if( scope.length && scope.length > 0 ) {
            getThingsGoing();
        } else {
            $(document).on('tinymce-editor-init', () => {
                getThingsGoing(true);
            });
        }

        function getThingsGoing(initial) {
            const initialSettings = tinyMCEPreInit.mceInit['alchemy-temp-editor'];

            if( initial ) {
                window.tweakSettings = function(id) {
                    const tweakedSettings = {};

                    $.each(initialSettings, (key, val) => {
                        let newVal = val;

                        if( 'string' === typeof val ) {
                            newVal = val.replace('alchemy-temp-editor', id);
                        }

                        tweakedSettings[key] = newVal;
                    });

                    return tweakedSettings;
                };

                $editors = $editors.filter((i, el) => {
                    return $(el).closest('.repeatee__content').length === 0
                });
            }

            $editors.each((i, el) => {
                const $editor = $(el);
                const settings = $.extend({}, window.tweakSettings( $editor.attr('id') ), {
                    init_instance_callback: editor => {
                        const $container = $(editor.editorContainer);

                        if( $container[0] ) {
                            $container.addClass('wp-editor-container').before(
                                `<div id="wp-${editor.id}-editor-tools" class="wp-editor-tools hide-if-no-js">
                                    <div id="wp-${editor.id}-media-buttons" class="wp-media-buttons">
                                    <button type="button" id="insert-media-button" class="button insert-media add_media" data-editor="${editor.id}">
                                    <span class="wp-media-buttons-icon"></span> Add Media
                                    </button>
                                    </div>
                                </div>`);

                        }
                    }
                });

                settings.min_height = 250;

                if( typeof tinymce !== 'undefined' && ! $editor.hasClass('tinymce--init') ) {
                    tinymce.init(settings);
                    $editor.addClass('tinymce--init');
                }
            });

            $editors.on('mousedown', function (e) {
                e.preventDefault();
            });
        }
    }
}