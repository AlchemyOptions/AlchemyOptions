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
        }

        public function normalize_field_keys( $field ) {
            $field = parent::normalize_field_keys( $field );

            if( $field[ 'value' ] ) {
                $field[ 'repeatees' ] = $this->parse_value( $field );
            } else {
                $field[ 'repeatees' ] = '';
            }

            $field[ 'add' ] = $this->generate_add_new_button( false, $field );

            return $field;
        }

        public function generate_add_new_button( $hasRepeatees, $field ) {
            $addBtn = '';

            if( $hasRepeatees ) {
                $addBtn = sprintf(
                    '<button%1$s data-nonce=\'%4$s\' data-repeater-id=\'%3$s\'>%2$s</button>',
                    $this->concat_attributes( array(
                        'class' => 'alchemy__repeater-add button button-primary jsAlchemyRepeaterAddType',
                        'type' => 'button'
                    ) ),
                    __( 'Choose type', 'alchemy-options' ),
                    $field['id'],
                    json_encode( array(
                        'id' => $field['id'] . '_repeater_nonce',
                        'value' => wp_create_nonce( $field['id'] . '_repeater_nonce' )
                    ) )
                );
            } else {
                $addBtn = sprintf(
                    '<button%1$s data-nonce=\'%4$s\' data-repeater-data=\'%3$s\'>%2$s</button><img src="%5$s" class="alchemy__repeater-add-spinner alchemy__repeater-add-spinner--hidden jsAlchemyRepeaterLoader" width="20" height="20" />',
                    $this->concat_attributes( array(
                        'class' => 'alchemy__repeater-add button button-primary jsAlchemyRepeaterAdd',
                        'type' => 'button'
                    ) ),
                    __( 'Add new', 'alchemy-options' ),
                    json_encode( array(
                        'id' => $field['id'],
                        'repeater' => array(
                            'simple' => true,
                            'type' => $field['_repeater-type']
                        )
                    ) ),
                    json_encode( array(
                        'id' => $field[ 'id' ] . '_repeater_nonce',
                        'value' => wp_create_nonce( $field[ 'id' ] . '_repeater_nonce' )
                    ) ),
                    get_site_url() . '/wp-includes/images/spinner-2x.gif'
                );
            }

            return $addBtn;
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
            $neededRepeater = array_values( $this->find_needed_repeater( $data ) )[0];

            if( ! $neededRepeater ) {
                return '';
            }

            $optionFields = new Alchemy_Fields_Loader( $this->networkField );

            $repeateesHTML ="";
            $repeateeID = sprintf(
                '%s_%s_%s',
                $data['id'],
                $data['repeater']['type'],
                $data['index']
            );

            $data['fields'] = array_map( function( $field ) use( $data, $repeateeID ) {
                $field['id'] = sprintf(
                    '%s_%s',
                    $repeateeID,
                    $field['id']
                );

                return $field;
            }, $neededRepeater['fields'] );

            array_unshift( $data[ 'fields' ], array(
                'title' => __( 'Title', 'alchemy-options' ),
                'id' => sprintf(
                    '%s_%s',
                    $repeateeID,
                    'title'
                ),
                'type' => 'text',
                'attributes' => array(
                    'class' => 'jsAlchemyRepeateeTitle'
                )
            ) );

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
                    'index' => $data[ 'index' ]
                ) ),
                $repeateeClass
            );

            $repeateesHTML .= '<input type="hidden" class="jsAlchemyRepeateeVisible" name="' . $repeateeID . '_visible" value="' . $repeateeVisible . '" />';

            $repeateesHTML .= $this->generate_repeatee_toolbar( $repeateeID, $repeateeVisible, '' );

            $repeateesHTML .= sprintf(
                '<div class="repeatee__content">%1$s</div>',
                $optionFields->get_fields_html( $data[ 'fields' ] )
            );

            $repeateesHTML .= '</div>';

            return $repeateesHTML;
        }

        public function find_needed_repeater( $data ) {
            $savedOptions = $this->networkField
                ? get_option( alch_network_options_id(), array() )
                : get_option( alch_options_id(), array() );

            if( isset( $savedOptions['repeaters'] ) ) {
                return array_filter( $savedOptions['repeaters'], function( $repeater ) use( $data ) {
                    return $repeater['id'] === $data['repeater']['type'];
                } );
            }

            return false;
        }

        public function generate_repeatee_toolbar( $repeateeID, $repeateeVisible, $repeateeTitle ) {
            $repeateeVisible = $repeateeVisible === 'true';
            $toolbarHTML = '';

            $toolbarHTML .= sprintf(
                '<div class="%1$s" title="%2$s">',
                'repeatee__toolbar alchemy__clearfix jsAlchemyRepeateeToolbar',
                __( 'Click to edit, drag to reorder', 'alchemy-options' )
            );

            $toolbarHTML .= '<h3 class="repeatee__title jsAlchemyRepeateeTitle">' . $repeateeTitle . '</h3>';

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