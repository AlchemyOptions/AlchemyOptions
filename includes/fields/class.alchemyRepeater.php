<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if( ! class_exists( 'Alchemy_Repeater_Field' ) ) {

    class Alchemy_Repeater_Field extends Alchemy_Field {
        public function __construct( $networkField = false ) {
            parent::__construct( $networkField );

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
                    '<button%1$s data-nonce=\'%5$s\' data-repeatee-id=\'%4$s\' data-repeater-id=\'%3$s\'>%2$s</button><img src="%6$s" class="alchemy__repeater-add-spinner alchemy__repeater-add-spinner--hidden jsAlchemyRepeaterLoader" width="20" height="20" />',
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
                        ) ),
                        get_site_url() . '/wp-includes/images/spinner-2x.gif'
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
                $field[ 'repeatees' ] = $this->parse_value( $field );
            } else {
                $field[ 'repeatees' ] = '';
            }

            return $field;
        }

        public function parse_value( $field ) {
            $repeateesHTML = '';

            if( is_array( $field[ 'value' ] ) && count( $field[ 'value' ] ) > 0 ) {
                foreach ( $field[ 'value' ] as $i => $repeatee ) {

                    $repeateesHTML .= $this->generate_repeatee(array(
                        'id' => $field[ 'id' ],
                        'repeatee_id' => $repeatee[ 'type' ],
                        'index' => $i,
                        'isVisible' => $repeatee[ 'isVisible' ],
                        'timer' => array(),
                        'savedFields' => $repeatee[ 'fields' ]
                    ), true);

                }
            }

            return $repeateesHTML;
        }

        public function generate_repeatee( $data, $ssr = false ) {
            $neededRepeater = $this->find_needed_repeater( $data );

            if( ! $neededRepeater ) {
                return '';
            }

            $optionFields = new Alchemy_Fields_Loader( $this->networkField );

            $repeateesCount = count( $neededRepeater[ 'repeatees' ] );

            $repeateesHTML ="";

            $repeatees = array_filter( $neededRepeater[ 'repeatees' ], function( $repeatee ) use( $data ) {
                return $repeatee[ 'repeatee_id' ] === $data[ 'repeatee_id' ];
            } );

            array_unshift( $repeatees[0][ 'fields' ], array(
                'title' => __( 'Title', 'alchemy-options' ),
                'id' => 'title',
                'type' => 'text',
                'attributes' => array(
                    'class' => 'jsAlchemyRepeateeTitle'
                )
            ) );

            $repeateesFieldIDs = array();

            $repeateeTitle = '';

            foreach( $repeatees[0][ 'fields' ] as $i => $field ) {
                $repeateesFieldIDs[] = array(
                    'id' => $repeatees[ 0 ][ 'fields' ][ $i ][ 'id' ],
                    'type' => $repeatees[ 0 ][ 'fields' ][ $i ][ 'type' ]
                );

                if( isset( $data[ 'savedFields' ], $data[ 'savedFields' ][$repeatees[ 0 ][ 'fields' ][ $i ][ 'id' ]] ) ) {
                    $repeatees[ 0 ][ 'fields' ][ $i ][ 'value' ] = $data[ 'savedFields' ][$repeatees[ 0 ][ 'fields' ][ $i ][ 'id' ]]['value'];

                    if( 'title' === $repeatees[ 0 ][ 'fields' ][ $i ][ 'id' ] ) {
                        $repeateeTitle = $data[ 'savedFields' ][$repeatees[ 0 ][ 'fields' ][ $i ][ 'id' ]]['value'];
                    }
                } else {
                    $repeatees[ 0 ][ 'fields' ][ $i ][ 'value' ] = '';
                }

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

            $repeateeClass = 'repeatee jsAlchemyRepeatee';

            if( ! $ssr ) {
                $repeateeClass .= ' repeatee--expanded';
                $repeateeVisible = "true";
            } else {
                $repeateeVisible = $data[ 'isVisible' ];

                if( $repeateeVisible === 'false' ) {
                    $repeateeClass .= ' repeatee--hidden';
                }
            }

            $repeateesHTML .= sprintf(
                '<div class="%3$s" data-alchemy=\'%2$s\' id="%1$s">',
                    $repeateeID,
                    json_encode( array(
                        'repeater_id' => $data[ 'id' ],
                        'repeatee_title' => $repeatees[0][ 'title' ],
                        'repeatee_id' => $data[ 'repeatee_id' ],
                        'fieldIDs' => $repeateesFieldIDs,
                        'index' => $data[ 'index' ]
                    ) ),
                    $repeateeClass
                );


            $repeateesHTML .= '<input type="hidden" class="jsAlchemyRepeateeVisible" name="' . $repeateeID . '_visible" value="' . $repeateeVisible . '" />';

            $repeateesHTML .= $this->generate_repeatee_toolbar( $repeatees[0], $repeateeID, $repeateesCount, $repeateeVisible, $repeateeTitle );

            $repeateesHTML .= sprintf(
                '<div class="repeatee__content">%1$s</div>',
                    $optionFields->get_fields_html( $repeatees[0][ 'fields' ] )
                );

            $repeateesHTML .= '</div>';

            return $repeateesHTML;
        }

        public function find_needed_repeater( $data ) {
            $savedOptions = $this->networkField
                ? get_option( alch_network_options_id(), array() )
                : get_option( alch_options_id(), array() );

            return $this->filter_repeater( $data, $savedOptions['options'] );
        }

        public function filter_repeater( $data, $fields ) {
            $neededRepeater = false;

            foreach( $fields as $field ) {
                if( ( 'repeater' === $field['type'] || 'nested-repeater' === $field['type'] ) && $field['id'] === $data['id'] ) {
                    $neededRepeater = $field;
                } else if( 'section' === $field['type'] ) {
                    $neededRepeater = $this->filter_repeater( $data, $field['options'] );
                } else if( 'fields-group' === $field['type'] ) {
                    $neededRepeater = $this->filter_repeater( $data, $field['fields'] );
                }
            }

            return $neededRepeater;
        }

        public function generate_repeatee_toolbar( $repeatee, $repeateeID, $repeateesCount, $repeateeVisible, $repeateeTitle ) {
            $repeateeVisible = $repeateeVisible === 'true';
            $toolbarHTML = '';

            $toolbarHTML .= sprintf(
                '<div class="%1$s" title="%2$s">',
                    'repeatee__toolbar alchemy__clearfix jsAlchemyRepeateeToolbar',
                    __( 'Click to edit, drag to reorder', 'alchemy-options' )
            );

            $toolbarHTML .= '<h3 class="repeatee__title jsAlchemyRepeateeTitle">' . $repeateeTitle . '</h3>';

            if( $repeateesCount > 1 ) {
                $toolbarHTML .= sprintf( '<small>%1$s</small>', $repeatee[ 'title' ] );
            }

            $toolbarHTML .= $this->generate_actions_group( $repeateeID, $repeateeVisible );

            $toolbarHTML .= '</div>';

            return $toolbarHTML;
        }

        public function generate_actions_group( $repeateeID, $repeateeVisible ) {
            $actionGroupHTML = '';
            $visibilityIcon = 'dashicons dashicons-visibility';

            if( ! $repeateeVisible ) {
                $visibilityIcon .= ' dashicons-hidden';
            }

            $actionGroupHTML .= '<span class="repeatee__actions button-group">';

            $actionGroupHTML .= sprintf(
                '<button type="button" class="%2$s" data-repeatee-id=\'%1$s\' title="%4$s">%3$s</button>',
                    $repeateeID,
                    'repeatee__btn button button-secondary jsAlchemyRepeateeHide',
                    sprintf( '<span class="%s"></span>', $visibilityIcon ),
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