import mediaUploader from './mediaUploader';
import togglePassword from './togglePasswordVisibility';
import colorpicker from './colorpicker';
import datepicker from './datepicker';
import editor from './editor';
import buttonGroup from './buttonGroup';
import imageRadios from './imageRadios';
import slider from './slider';
import postTypeSelect from './postTypeSelect';
import taxonomySelect from './taxonomySelect';
import datalistFunctions from './datalistFunctions';
import conditions from './conditions';

function getThingsGoing(scope = document) {
    const $repeaterFields = $('.jsAlchemyRepeaterField', scope);

    if( $repeaterFields[0] ) {
        $repeaterFields.each((i, el) => {
            const $repeater = $(el);
            const $dropIn = $repeater.children('fieldset').children('.jsAlchemyRepeaterSortable');

            let clickIndex = $dropIn.children().length;

            $repeater.on('click', '.jsAlchemyRepeaterAdd', function(e) {
                e.stopPropagation();

                const $btn = $(this);
                const $loader = $btn.next('.jsAlchemyRepeaterLoader');
                const nonce = $btn.data('nonce');

                $btn.attr('disabled', true);
                $loader.removeClass('alchemy__repeater-add-spinner--hidden');

                $.ajax({
                    'type': 'get',
                    'url': alchemyData.adminURL,
                    'data': {
                        'action': 'alchemy_repeater_item_add',
                        'nonce': [nonce.id, nonce.value],
                        'repeater': $btn.data('repeater-data'),
                        'index': clickIndex
                    },
                    'success': data => {
                        console.log('success');

                        const $data = $(data);

                        $dropIn.append($data);
                        $dropIn.sortable( "refresh" );

                        mediaUploader($data);
                        togglePassword($data);
                        colorpicker($data);
                        datepicker($data);
                        editor($data);
                        buttonGroup($data);
                        imageRadios($data);
                        slider($data);
                        postTypeSelect($data);
                        taxonomySelect($data);
                        datalistFunctions($data);
                        conditions($data);
                        getThingsGoing($data);
                    },
                    'error': err => {
                        console.error('error', err);
                    },
                    'complete': () => {
                        $btn.removeAttr('disabled');
                        $loader.addClass('alchemy__repeater-add-spinner--hidden');
                    }
                });

                clickIndex++;
            });

            $repeater.on('input', '.jsAlchemyRepeateeTitle', function() {
                const $input = $(this);

                $input.closest('.repeatee').children('.jsAlchemyRepeateeToolbar').find('.jsAlchemyRepeateeTitle').text($input.val());
            });

            $repeater.on('click', '.jsAlchemyRepeateeToolbar', function(e) {
                e.stopPropagation();

                const $toolbar = $(this);
                const $repeatee = $toolbar.closest( '.repeatee' );

                if( $repeatee.hasClass('repeatee--expanded') ) {
                    const $editor = $('.jsAlchemyEditorTextarea', $repeatee);
                    const tmcEditor = tinymce.get($editor.attr('id'));

                    $editor.removeClass('tinymce--init');
                    $repeatee.removeClass('repeatee--expanded');

                    $('.wp-editor-tools', $repeatee).remove();

                    if( tmcEditor ) {
                        tmcEditor.remove();
                    }
                } else {
                    editor($repeatee);
                    $repeatee.addClass('repeatee--expanded')
                }
            });

            $repeater.on('click', '.jsAlchemyRepeateeHide', function(e) {
                const $toolbar = $(this);
                const $parent = $toolbar.closest( '.repeatee' );
                const $visibilityInput = $parent.children('.jsAlchemyRepeateeVisible');

                $toolbar.find('span').toggleClass('dashicons-hidden');
                $parent.removeAttr('style').toggleClass('repeatee--hidden');

                $visibilityInput.val( $visibilityInput.val() === 'true' ? 'false' : 'true' );
            });

            $repeater.on('click', '.jsAlchemyRepeateeRemove', function(e) {
                const $toolbar = $(this);
                const $parent = $toolbar.closest( '.repeatee' );

                $parent.fadeOut(() => {
                    $parent.remove();

                    $dropIn.sortable( "refresh" );
                });
            });

            $repeater.on('click', '.repeatee__actions', e => {
                e.stopPropagation();
            });

            $dropIn.sortable({
                placeholder: "repeatee--placeholder",
                opacity: 0.8,
                handle: ".jsAlchemyRepeateeToolbar",
                start: (event, ui) => {
                    $dropIn.find('.repeatee--placeholder').height(ui.helper.height() - 2);

                    saveEditors($repeater);
                },
                stop: (event, ui) => {
                    restoreEditors($repeater);
                }
            });

            $dropIn.disableSelection();
        });

        function saveEditors(repeater) {
            let $editors = $('.jsAlchemyEditorTextarea', repeater);

            $editors.each((i, el) => {
                const $editor = $(el);

                if( $editor.hasClass('tinymce--init') && typeof( tinymce ) !== 'undefined' ) {
                    const $field = $editor.closest('.field--editor');
                    const selfHeight = $editor.closest('.field--editor').outerHeight();
                    const editorHeight = $editor.prev('.mce-tinymce').outerHeight();

                    $editor.next('.field__cover').height(editorHeight);
                    $field.height(selfHeight).addClass('tinymce--destroyed');

                    tinymce.get($editor.attr('id')).destroy();
                }
            });
        }

        function restoreEditors(repeater) {
            let $editors = $('.jsAlchemyEditorTextarea', repeater);

            $editors.each((i, el) => {
                const $editor = $(el);
                const $field = $editor.closest('.field--editor');

                if( $field.hasClass('tinymce--destroyed') && $editor.hasClass('tinymce--init') && typeof( tinymce ) !== 'undefined' ) {

                    const settings = window.tweakSettings($editor.attr('id'));

                    settings.min_height = 250;
                    tinymce.init(settings);

                    $editor.next('.field__cover').removeAttr('style');
                    $field.removeAttr('style').removeClass('tinymce--destroyed');
                }
            });
        }
    }
}

export default getThingsGoing;