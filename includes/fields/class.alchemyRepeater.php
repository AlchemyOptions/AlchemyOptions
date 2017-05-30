<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if( ! class_exists( 'Alchemy_Repeater_Field' ) ) {

    class Alchemy_Repeater_Field extends Alchemy_Field {
        public function __construct() {
            parent::__construct();

            $this->template = '
                <div class="alchemy__field field field--repeater jsAlchemyRepeaterField" id="field--{{ID}}" data-alchemy=\'{"id":"{{ID}}","type":"repeater"}\'>
                    <fieldset>
                        <legend class="field__label">{{TITLE}}</legend>
                        <div class="field__description">
                            <p>{{DESCRIPTION}}</p>
                        </div>
                        <div class="jsAlchemyRepeaterSortable">
                            {{REPEATEES}}
                        </div>
                    </fieldset>
                    {{ADD}}
                </div>
            ';

            include_once( ALCHEMY_OPTIONS_PLUGIN_DIR . 'includes/class.alchemyFieldsLoader.php' );
        }

        public function normalize_field_keys( $field ) {
            $field = parent::normalize_field_keys( $field );

            if( ! isset( $field[ 'repeatees' ] ) || count( $field[ 'repeatees' ] ) == 0 ) {
                return '';
            }

            $repeateesCount = count( $field[ 'repeatees' ] );

            if( 1 == $repeateesCount ) {
                $addBtn = sprintf(
                    '<button%1$s data-nonce=\'%5$s\' data-repeatee-id=\'%4$s\' data-repeater-id=\'%3$s\'>%2$s</button>',
                        $this->concat_attributes( array(
                            'class' => 'alchemy__repeater-add button button-primary jsAlchemyRepeaterAdd',
                            'type' => 'button'
                        ) ),
                        __( 'Add new', 'alchemy-options' ),
                        $field[ 'id' ],
                        $field[ 'repeatees' ][0][ 'repeatee_id' ],
                        json_encode( array(
                            'id' => $field[ 'id' ] . '_repeater_nonce',
                            'value' => wp_create_nonce( $field[ 'id' ] . '_repeater_nonce' )
                        ) )
                );
            } else {
                $addBtn = sprintf(
                    '<button%1$s data-nonce=\'%4$s\' data-repeater-id=\'%3$s\'>%2$s</button>',
                        $this->concat_attributes( array(
                            'class' => 'alchemy__repeater-add button button-primary jsAlchemyRepeaterAddType',
                            'type' => 'button'
                        ) ),
                        __( 'Choose type {{icon}}', 'alchemy-options' ),
                        $field[ 'id' ],
                        json_encode( array(
                            'id' => $field[ 'id' ] . '_repeater_nonce',
                            'value' => wp_create_nonce( $field[ 'id' ] . '_repeater_nonce' )
                        ) )
                );
            }

            $field[ 'add' ] = $addBtn;

            if( $field[ 'value' ] ) {
                $field[ 'repeatees' ] = $this->parse_value( $field[ 'value' ] );
            } else {
                $field[ 'repeatees' ] = '';
            }

            return $field;
        }

        public function parse_value( $value ) {
            /*
             * $value
             *
             * array(
             *      array(
                        'title' => 'Repeater field',
             *          'type' => 'repeater-fields',
             *          'values' => array(
             *              'organization' => 'Some text',
             *              'description' => 'Some description',
             *              'radio' => 'some value'
             *          )
             *      ),
             *      array(
                        'title' => 'Repeater field',
             *          'type' => 'repeater-fields',
             *          'values' => array(
             *              'organization' => 'Some text',
             *              'description' => 'Some description',
             *              'radio' => 'some value'
             *          )
             *      ),
             * )
             * */

            $repeateesHTML = '';

            return $repeateesHTML;
        }

        public function generate_repeatee( $data ) {
            $savedOptions = get_option( alch_options_id(), array() );
            $optionFields = new Alchemy_Fields_Loader();

            $neededRepeater = array_filter( $savedOptions[ 'options' ], function( $option ) use( $data ) {
                return $option[ 'id' ] === $data[ 'id' ];
            } )[0];

            if( ! $neededRepeater ) {
                return '';
            }

            $repeateesHTML ="";

            $repeatees = array_filter( $neededRepeater[ 'repeatees' ], function( $repeatee ) use( $data ) {
                return $repeatee[ 'repeatee_id' ] === $data[ 'repeatee_id' ];
            } );

            array_unshift( $repeatees[0][ 'fields' ], array(
                'title' => __( 'Title', 'alchemy-options' ),
                'id' => 'title',
                'type' => 'text',
            ) );

            foreach( $repeatees[0][ 'fields' ] as $i => $field ) {
                $repeatees[ 0 ][ 'fields' ][ $i ][ 'id' ] = sprintf(
                    '%1$s_%2$s_%3$s_%4$s',
                        $data[ 'id' ],
                        $data[ 'repeatee_id' ],
                        $repeatees[ 0 ][ 'fields' ][ $i ][ 'id' ],
                        $data[ 'index' ]
                    );
            }

            $repeateeID = sprintf(
                '%1$s_%2$s_%3$s',
                    $data[ 'id' ],
                    $data[ 'repeatee_id' ],
                    $data[ 'index' ]
                );

            $repeateesHTML .= sprintf(
                '<div class="repeatee repeatee--expanded jsAlchemyRepeatee" data-alchemy=\'%2$s\' id="%1$s">',
                    $repeateeID,
                    json_encode( array(
                        'repeater_id' => $data[ 'id' ],
                        'repeatee_title' => $repeatees[0][ 'title' ],
                        'repeatee_id' => $data[ 'repeatee_id' ],
                        'index' => $data[ 'index' ]
                    ) )
                );

            $repeateesHTML .= '<input type="hidden" class="jsAlchemyRepeateeVisible" name="' . $repeateeID . '_visible" value="true" />';

            $repeateesHTML .= $this->generate_repeatee_toolbar( $repeatees[0], $repeateeID );

            $repeateesHTML .= sprintf(
                '<div class="repeatee__content">%1$s</div>',
                    $optionFields->get_fields_html( $repeatees[0][ 'fields' ] )
                );

            $repeateesHTML .= '</div>';

            return $repeateesHTML;
        }

        public function generate_repeatee_toolbar( $repeatee, $repeateeID ) {
            $toolbarHTML = '';

            $toolbarHTML .= sprintf(
                '<div class="%1$s" title="%2$s">',
                    'repeatee__toolbar alchemy__clearfix jsAlchemyRepeateeToolbar',
                    __( 'Click to edit, drag to reorder', 'alchemy-options' )
            );
            $toolbarHTML .= sprintf( '<h3 class="repeatee__title jsAlchemyRepeateeTitle"> </h3><small>%1$s</small>', $repeatee[ 'title' ] );
            $toolbarHTML .= $this->generate_actions_group( $repeateeID );

            $toolbarHTML .= '</div>';

            return $toolbarHTML;
        }

        public function generate_actions_group( $repeateeID ) {
            $actionGroupHTML = '';

            $actionGroupHTML .= '<span class="repeatee__actions button-group">';

            $actionGroupHTML .= sprintf(
                '<button type="button" class="%2$s" data-repeatee-id=\'%1$s\' title="%4$s">%3$s</button>',
                $repeateeID,
                'repeatee__btn button button-secondary jsAlchemyRepeateeHide',
                '<span class="dashicons dashicons-visibility"></span>',
                __( 'Exclude this item from showing', 'alchemy-options' )
            );

            $actionGroupHTML .= sprintf(
                '<button type="button" class="%2$s" data-repeatee-id=\'%1$s\' title="%4$s">%3$s</button>',
                $repeateeID,
                'repeatee__btn button button-secondary jsAlchemyRepeateeTiming',
                '<span class="dashicons dashicons-clock"></span>',
                __( 'Choose when to show this item', 'alchemy-options' )
            );

            $actionGroupHTML .= sprintf(
                '<button type="button" class="%2$s" data-repeatee-id=\'%1$s\' title="%4$s">%3$s</button>',
                $repeateeID,
                'repeatee__btn button button-secondary jsAlchemyRepeateeRemove',
                '<span class="dashicons dashicons-trash"></span>',
                __( 'Delete this item', 'alchemy-options' )
            );

            $actionGroupHTML .= '</span>';

            return $actionGroupHTML;
        }
    }
}