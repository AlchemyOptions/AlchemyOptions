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
                        <div class="jsAlchemyRepeaterSortable">
                            {{REPEATEES}}
                        </div>
                    </fieldset>
                    {{ADD}}
                    <div class="field__description">
                        <p>{{DESCRIPTION}}</p>
                    </div>
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
            $addBtn = '';

            if( 1 == $repeateesCount ) {
                $addBtn = sprintf(
                    '<button%1$s data-nonce=\'%5$s\' data-repeatee-id=\'%4$s\' data-repeater-id=\'%3$s\'>%2$s</button>',
                        $this->concat_attributes( array(
                            'class' => 'button button-primary jsAlchemyRepeaterAdd',
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
                            'class' => 'button button-primary jsAlchemyRepeaterAddType',
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

            foreach( $repeatees[0][ 'fields' ] as $i => $field ) {
                $repeatees[0][ 'fields' ][ $i ][ 'id' ] = sprintf( '%1$s_%2$s_%3$s_%4$s', $data[ 'id' ], $data[ 'repeatee_id' ], $repeatees[0][ 'fields' ][ $i ][ 'id' ], $data[ 'index' ] );
            }

            $repeateeID = sprintf( '%1$s_%2$s_%3$s', $data[ 'id' ], $data[ 'repeatee_id' ], $data[ 'index' ] );

            $repeateesHTML .= '<div class="repeatee jsAlchemyRepeatee" id="' . $repeateeID . '">';
            $repeateesHTML .= '<input type="hidden" name="' . $repeateeID . '" value="" />';

            $repeateesHTML .= $this->generate_repeatee_toolbar( $repeatees[0], $repeateeID );
            $repeateesHTML .= $optionFields->get_fields_html( $repeatees[0][ 'fields' ] );

            $repeateesHTML .= '</div>';

            return $repeateesHTML;
        }

        public function generate_repeatee_toolbar( $repeatee, $repeateeID ) {
            $toolbarHTML = '';

            $toolbarHTML .= '<div class="repeatee__toolbar alchemy__clearfix">';
            $toolbarHTML .= sprintf( '<small>%1$s</small>', $repeatee[ 'title' ] );

            $toolbarHTML .= '<span class="repeatee__actions">';
            $toolbarHTML .= '<span class="dashicons dashicons-move jsAlchemyRepeateeSortableHandle"></span>';
            $toolbarHTML .= sprintf( '<button type="button" class="button button-primary jsAlchemyHideRepeatee" data-repeatee-id=\'%s\'><span class="dashicons dashicons-visibility"></span></button>', $repeateeID );
            $toolbarHTML .= sprintf( '<button type="button" class="button button-primary jsAlchemyRepeateeTiming" data-repeatee-id=\'%s\'><span class="dashicons dashicons-clock"></span></button>', $repeateeID );
            $toolbarHTML .= sprintf( '<button type="button" class="button button-primary jsAlchemyRepeateeRemove" data-repeatee-id=\'%s\'><span class="dashicons dashicons-trash"></span></button>', $repeateeID );
            $toolbarHTML .= '</span>';

            $toolbarHTML .= '</div>';

            return $toolbarHTML;
        }
    }
}