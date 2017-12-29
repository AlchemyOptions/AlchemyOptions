<?php

/**
 * @package Alchemy_Options\Includes\Fields
 *
 */

namespace Alchemy_Options\Includes\Fields;

use Alchemy_Options\Includes;

//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if( ! class_exists( __NAMESPACE__ . '\Repeater' ) ) {

    class Repeater extends Includes\Field {
        private $repeater;

        public function __construct( $networkField = false, $options = array() ) {
            parent::__construct( $networkField, $options );

            $this->template = '
                <div class="alchemy__field alchemy__clearfix field field--repeater jsAlchemyRepeaterField" id="field--{{ID}}" data-alchemy=\'{"id":"{{ID}}","type":"repeater"{{TYPED}}}\'>
                    <fieldset>
                    <input type="hidden" name="{{ID}}" class="jsRepeaterHidden" />
                        <div class="field__side">
                            <legend class="field__label">{{TITLE}}</legend>
                            {{DESCRIPTION}}
                        </div>
                        <div class="field__content">
                            <div class="jsAlchemyRepeaterSortable">
                                {{REPEATEES}}
                            </div>
                            {{ADD}}
                        </div>
                    </fieldset>
                </div>
            ';
        }

        public function normalize_field_keys( $field ) {
            $field = parent::normalize_field_keys( $field );

            $this->repeater = array_values( $this->find_needed_repeater( $field ) )[0];

            if( ! $this->repeater ) {
                return '';
            }

            $this->repeater['isTyped'] = isset( $this->repeater['field-types'] ) && alch_is_not_empty_array( $this->repeater['field-types'] );

            if( $field[ 'value' ] ) {
                $field[ 'repeatees' ] = $this->parse_value( $field );
            } else {
                $field[ 'repeatees' ] = '';
            }

            $field['typed'] = $this->repeater['isTyped'] ? ',"typed":1' : ',"typed":0';

            $field[ 'add' ] = $this->generate_add_new_button( $field );

            return $field;
        }

        public function generate_add_new_button( $field ) {
            if( $this->repeater['isTyped'] ) {
                $attributes = array(
                    'class' => 'alchemy__repeater-add button button-primary jsAlchemyRepeaterAddType',
                    'type' => 'button'
                );
                $text = __( 'Add from type', 'alchemy-options' ) . '<span class="dashicons dashicons-arrow-down-alt2"></span>';

                $typeList = "<div class='type-list jsTypeList'><ul>";
                    foreach( $this->repeater['field-types'] as $fieldtype ) {
                        $typeList .= sprintf(
                            '<li><button%2$s data-nonce=\'%4$s\' data-repeater-data=\'%3$s\'>%1$s</button></li>',
                            $fieldtype['title'],
                            $this->concat_attributes(array(
                                'class' => 'alchemy__repeater-add button button-secondary jsAlchemyRepeaterAdd',
                                'type' => 'button'
                            )),
                            json_encode(array(
                                'id' => $field['id'],
                                'repeater' => array(
                                    'simple' => ! $this->repeater['isTyped'],
                                    'type' => $field['repeater']['type'],
                                    'type-id' => $fieldtype['id']
                                )
                            )),
                            json_encode( array(
                                'id' => $field['id'] . '_' . $fieldtype['id'] . '_repeater_nonce',
                                'value' => wp_create_nonce( $field['id'] . '_' . $fieldtype['id'] . '_repeater_nonce' )
                            ) )
                        );
                    }
                $typeList .= "</ul></div>";
            } else {
                $attributes = array(
                    'class' => 'alchemy__repeater-add button button-primary jsAlchemyRepeaterAdd',
                    'type' => 'button'
                );
                $text = __( 'Add new', 'alchemy-options' );
                $typeList = "";
            }

            $addBtn = sprintf(
                '<div class="alchemy__add-new"><button%1$s data-nonce=\'%4$s\' data-repeater-data=\'%3$s\'>%2$s</button><img src="%5$s" class="alchemy__repeater-add-spinner alchemy__repeater-add-spinner--hidden jsAlchemyRepeaterLoader" width="20" height="20" />%6$s</div>',
                $this->concat_attributes( $attributes ),
                $text,
                json_encode( array(
                    'id' => $field['id'],
                    'repeater' => array(
                        'simple' => ! $this->repeater['isTyped'],
                        'type' => $field['repeater']['type']
                    )
                ) ),
                json_encode( array(
                    'id' => $field[ 'id' ] . '_repeater_nonce',
                    'value' => wp_create_nonce( $field[ 'id' ] . '_repeater_nonce' )
                ) ),
                get_site_url() . '/wp-includes/images/spinner-2x.gif',
                $typeList
            );

            return $addBtn;
        }

        public function parse_value( $field ) {
            $repeateesHTML = '';

            if( isset( $field['value'] ) && alch_is_not_empty_array( $field['value'] ) ) {
                foreach ( $field[ 'value' ] as $i => $repeatee ) {
                    $settings = array(
                        'id' => $field['id'],
                        'repeater' => array(
                            'simple' => 'true',
                            'type' => $field['repeater']['type']
                        ),
                        'index' => $i,
                        'isVisible' => $repeatee[ 'isVisible' ],
                        'timer' => array(),
                        'savedFields' => $repeatee[ 'fields' ]
                    );

                    if( isset( $repeatee['typeID'] ) ) {
                        $settings['repeater']['type-id'] = $repeatee['typeID'];
                    }

                    $repeateesHTML .= $this->generate_repeatee( $settings, true );

                }
            }

            return $repeateesHTML;
        }

        public function generate_repeatee( $data, $ssr = false ) {
            $neededRepeater = array_values( $this->find_needed_repeater( $data ) )[0];

            if( ! $neededRepeater ) {
                return '';
            }

            $neededRepeater['isTyped'] = isset( $neededRepeater['field-types'] ) && alch_is_not_empty_array( $neededRepeater['field-types'] );

            $optionFields = new Includes\Fields_Loader( $this->networkField, $this->options, $data['id'] );
            $repeateeTitle = '';
            $repeateeType = array(
                'text' => "",
                'id' => "",
            );

            $repeateesHTML = "";

            if( $neededRepeater['isTyped'] ) {
                $repeateeID = sprintf(
                    '%s_%s_%s',
                    $data['id'],
                    $data['repeater']['type-id'],
                    $data['index']
                );
            } else {
                $repeateeID = sprintf(
                    '%s_%s',
                    $data['id'],
                    $data['index']
                );
            }

            $fieldIDs = array(
                array(
                    'id' => 'title',
                    'type' => 'text',
                )
            );

            $data['fields'] = array(
                array(
                    'title' => __( 'Title', 'alchemy-options' ),
                    'id' => 'title',
                    'type' => 'text',
                    'attributes' => array(
                        'class' => 'jsAlchemyRepeateeTitle'
                    )
                )
            );

            if( $neededRepeater['isTyped'] ) {
                $fieldType = array_filter($neededRepeater['field-types'], function( $type ) use( $data ) {
                    return $type['id'] === $data['repeater']['type-id'];
                });

                $fieldType = array_values( $fieldType );

                $neededRepeater['fields'] = $fieldType[0]['fields'];

                $repeateeType['text'] = "<small>(" . $fieldType[0]['title'] . ")</small>";
                $repeateeType['id'] = $fieldType[0]['id'];
            }

            //Sections field is top-level only
            $neededRepeater['fields'] = array_filter($neededRepeater['fields'], function( $field ) {
                return $field['type'] !== 'sections';
            });

            foreach( $neededRepeater['fields'] as $field ) {
                $fieldIDs[] = array(
                    'id' => $field['id'],
                    'type' => $field['type']
                );

                $data['fields'][] = $field;
            }

            $values = isset( $data['savedFields'] ) ? $data['savedFields'] : [];

            foreach ( $data['fields'] as $id => $field ) {
                if ( 'title' == $data['fields'][ $id ]['id'] && isset( $values[ $field['id'] ] ) ) {
                    $repeateeTitle = $values[ $field['id'] ]['value'];
                }

                $data['fields'][ $id ]['id'] = sprintf(
                    '%s_%s',
                    $repeateeID,
                    $field['id']
                );

                $data['fields'][ $id ]['name'] = $data['fields'][ $id ]['id'];

                if ( isset( $values[ $field['id'] ] ) ) {
                    $data['fields'][ $id ]['value'] = $values[ $field['id'] ]['value'];
                }
            }

            $repeateeClass = 'repeatee jsAlchemyRepeatee';

            if( ! $ssr ) {
                if( isset( $data['isVisible'] ) ) {
                    $repeateeClass .= ' repeatee--expanded';
                    $repeateeVisible = $data['isVisible'];

                    if( $data['isVisible'] === 'false' ) {
                        $repeateeClass .= ' repeatee--hidden';
                    }
                } else {
                    $repeateeClass .= ' repeatee--expanded';
                    $repeateeVisible = "true";
                }
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
                    'fieldIDs' => $fieldIDs,
                    'repeateeTypeID' => $repeateeType['id']
                ) ),
                $repeateeClass
            );

            $repeateesHTML .= '<input type="hidden" class="jsAlchemyRepeateeVisible" name="' . $repeateeID . '_alchemy_visible" value="' . $repeateeVisible . '" />';

            $repeateesHTML .= $this->generate_repeatee_toolbar( $repeateeID, $repeateeVisible, $repeateeTitle, $repeateeType['text'] );

            $repeateesHTML .= sprintf(
                '<div class="repeatee__content">%1$s</div>',
                $optionFields->get_fields_html( $data[ 'fields' ] )
            );

            $repeateesHTML .= '</div>';

            return $repeateesHTML;
        }

        public function find_needed_repeater( $data ) {
            $savedRepeaters = get_option( alch_repeaters_id(), array() );

            if( isset( $savedRepeaters ) && alch_is_not_empty_array( $savedRepeaters ) ) {
                return array_filter( $savedRepeaters, function( $repeater ) use( $data ) {
                    return $repeater['id'] === $data['repeater']['type'];
                } );
            }

            return false;
        }

        public function generate_repeatee_toolbar( $repeateeID, $repeateeVisible, $repeateeTitle, $repeateeType ) {
            $repeateeVisible = $repeateeVisible === 'true';
            $toolbarHTML = '';

            $toolbarHTML .= sprintf(
                '<div class="%1$s" title="%2$s">',
                'repeatee__toolbar alchemy__clearfix--right jsAlchemyRepeateeToolbar',
                __( 'Click to edit, drag to reorder', 'alchemy-options' )
            );

            $toolbarHTML .= '<h3 class="repeatee__title jsAlchemyRepeateeTitle">' . $repeateeTitle . '</h3>' . $repeateeType;

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
                '<button type="button" class="%2$s" data-nonce=\'%5$s\' data-repeatee-id=\'%1$s\' title="%4$s">%3$s</button>',
                $repeateeID,
                'repeatee__btn button button-secondary jsAlchemyRepeateeCopy',
                '<span class="dashicons dashicons-admin-page"></span>',
                __( 'Copy this item', 'alchemy-options' ),
                json_encode(array(
                    'id' => $repeateeID . '_copy_nonce',
                    'value' => wp_create_nonce( $repeateeID . '_copy_nonce' )
                ))
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