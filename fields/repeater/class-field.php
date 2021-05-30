<?php

namespace Alchemy\Fields\Repeater;

use Alchemy\Fields\Field_Interface;
use Alchemy\Includes\Options_Page;
use Alchemy\Options;
use WP_Error;
use WP_REST_Request;

if( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( class_exists( __NAMESPACE__ . '\Field' ) ) {
    return;
}

class Field implements Field_Interface {
    function __construct() {
        add_filter( 'alch_register_field_type', array( $this, 'register_type' ) );
        add_filter( 'alch_get_repeater_option_html', array( $this, 'get_option_html' ), 10, 3 );
        add_filter( 'alch_sanitize_repeater_value', array( $this, 'sanitize_value' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'alch_prepare_repeater_value', array( $this, 'prepare_value' ), 10, 3 );
        add_filter( 'alch_validate_repeater_value', array( $this, 'validate_value' ), 10, 2 );
        add_action( 'rest_api_init', array( $this, 'add_rest_endpoints' ) );
    }

    function enqueue_assets() : void {
        wp_register_script(
            'alch_repeater_field',
            AlCHEMY_DIR_URL . 'fields/repeater/scripts.min.js',
            array( 'alch_admin_scripts', 'jquery-ui-sortable' ),
            filemtime( AlCHEMY_DIR_PATH . 'fields/repeater/scripts.min.js' ),
            true
        );

        wp_register_style(
            'alch_repeater_field',
            AlCHEMY_DIR_URL . 'fields/repeater/styles.min.css',
            array(),
            filemtime( AlCHEMY_DIR_PATH . 'fields/repeater/styles.min.css' )
        );

        wp_localize_script( 'alch_repeater_field', 'AlchemyRepeatersData', array(
            'add-repeatee' => array(
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'url' => get_rest_url( null, '/alchemy/v1/add-repeatee/' )
            ),
            'clone-repeatee' => array(
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'url' => get_rest_url( null, '/alchemy/v1/clone-repeatee/' )
            ),
			'misc' => array(
				'add-from-type' => __( 'Add from type', 'alchemy' ),
			),
        ) );

        wp_enqueue_script( 'alch_repeater_field' );
        wp_enqueue_style( 'alch_repeater_field' );
    }

    function register_type( array $types ) : array {
        $myType = array(
            'id' => 'repeater',
            'available-for' => array(
                'options' => true,
                'metaboxes' => true,
                'userprofile' => true,
            ),
        );

        return array_merge( $types, [$myType] );
    }

    function get_option_html( array $data, $savedValue, string $type ) : string {
        $registeredRepeaters = Options::get_registered_repeaters();
        $repeater = Options::get_repeater_id_details( $data['type'] );
        $settings = $registeredRepeaters[$repeater['id']];

        if( empty( $settings ) ) {
            return '';
        }

        $canColorCode = apply_filters( "alch_{$data['id']}_repeater_can_colorcode",
            apply_filters( 'alch_repeater_can_colorcode', false )
        );

        $colorCodeColors = apply_filters( 'alch_repeater_colorcode_colors',
            apply_filters( "alch_{$data['id']}_repeater_colorcode_colors", [ 'red', 'green', 'blue' ] )
        );

        $fieldsData = $this->get_fields_data( $settings );

        $html = sprintf( '<div class="alchemy__field field field--repeater repeater clearfix jsAlchemyField jsAlchemyRepeaterField" data-alchemy="%s">',
            esc_attr( json_encode( array(
                'type' => 'repeater',
                'id' => $data['id'],
                'repeater-id' => $repeater['id'],
                'fields-data' => $fieldsData
            ) ) )
        );

        $html .= alch_admin_get_field_sidebar( $data, false );

        $html .= sprintf( '<div class="field__content">%1$s%2$s%3$s</div>',
            $this->get_repeatees_html( array(
                'savedValue' => $savedValue,
                'type' => $type,
                'repeater' => array(
                    'id' => $data['id'],
                    'repeater-id' => $repeater['id'],
                    'data' => $this->get_fields_data( $settings ),
                    'colorcode' => array(
                        'enabled' => $canColorCode,
                        'colors' => $colorCodeColors,
                    ),
                )
            ) ),
            $this->get_add_new_button_html( $settings ),
            $this->get_get_color_choices_html( array(
                'enabled' => $canColorCode,
                'colors' => $colorCodeColors,
            ) )
        );

        $html .= '</div>';

        return $html;
    }

    function validate_value( $id, $value ) : array {
        $error = apply_filters( 'alch_do_validate_repeater_value', '', $value );

        if( empty( $error ) ) {
            $error = apply_filters( "alch_do_validate_{$id}_value", '', $value );
        }

        if( ! empty( $error ) ) {
            return array(
                'is_valid' => false,
                'message' => $error
            );
        }

        return array( 'is_valid' => true );
    }

    function sanitize_value( $value ) : array {
        $sanitisedValues = [];

        foreach ( $value as $repeatee ) {
            $repeateeMeta = json_decode( json_encode( $repeatee->meta ), true );

            if( ! empty( $repeateeMeta['label'] ) ) {
                $repeateeMeta['label'] = sanitize_text_field( $repeateeMeta['label'] );
            }

            $sanitisedValues[] = array(
                'meta' => $repeateeMeta,
                'values' => array_map( function( $item ) {
                    $valueType = $item->type;
                    $repeater = Options::get_repeater_id_details( $valueType );

                    if( $repeater ) {
                        $valueType = 'repeater';
                    }

                    return array(
                        'type' => $item->type,
                        'id' => $item->id,
                        'value' => apply_filters( "alch_sanitize_{$valueType}_value", $item->value )
                    );
                }, $repeatee->values )
            );
        }

        return $sanitisedValues;
    }

    function prepare_value( $value, $id ) : array {
        $modifiedValues = [];

        foreach ( $value as $savedRepeatee ) {
            if( empty( $savedRepeatee['meta']['visible'] ) ) {
                continue;
            }

            $isTyped = ! empty( $savedRepeatee['meta']['id'] );

            $itemValues = $isTyped ? array(
                'type' => $savedRepeatee['meta']['id'],
                'value' => []
            ) : [];

            foreach ( $savedRepeatee['values'] as $savedValueItem ) {
                $valueType = $savedValueItem['type'];
                $repeater = Options::get_repeater_id_details( $valueType );

                if( $repeater ) {
                    $valueType = 'repeater';
                }

                $preparedValue = apply_filters( "alch_prepare_{$valueType}_value", $savedValueItem['value'], $savedValueItem['id'] );

                if( $isTyped ) {
                    $itemValues['value'][$savedValueItem['id']] = $preparedValue;
                } else {
                    $itemValues[$savedValueItem['id']] = $preparedValue;
                }
            }

            $modifiedValues[] = $itemValues;
        }

        $validValue = apply_filters( 'alch_prepared_repeater_value', $modifiedValues );
        $validValue = apply_filters( "alch_prepared_{$id}_value", $validValue );

        return $validValue;
    }

    function add_rest_endpoints() : void {
        register_rest_route( 'alchemy/v1', '/add-repeatee/', array(
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => array( $this, 'handle_add_repeatee' ),
            'permission_callback' => array( $this, 'permission_callback' ),
        ) );

        register_rest_route( 'alchemy/v1', '/clone-repeatee/', array(
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => array( $this, 'handle_clone_repeatee' ),
            'permission_callback' => array( $this, 'permission_callback' ),
        ) );
    }

    function permission_callback() : bool {
        $pageID = $_POST['page-id'] ?? null;

        if( ! empty( $pageID ) ) {
            $pageCap = Options_Page::get_page_capabilities( $pageID );

            return current_user_can( $pageCap );
        }

        return false;
    }

    function handle_add_repeatee( WP_REST_Request $request ) {
        $body_params = $request->get_body_params();

        $this->security_checks( $body_params );

        $repeateeType = $body_params['type-id'] ?? '';
        $repeateeTypeHuman = $body_params['type-title'] ?? '';

        if( isset( $body_params['repeater-id'] ) && isset( $body_params['repeatees-number'] ) ) {
            $response = $this->get_repeatee_html( array(
                'repeater' => array(
                    'id' => $body_params['id'],
                    'repeater-id' => $body_params['repeater-id'],
                ),
                'repeatee' => array(
                    'meta' => array(
                        'visible' => true,
                        'id' => $repeateeType,
                        'title' => $repeateeTypeHuman,
                    )
                ),
                'index' => $body_params['repeatees-number'],
                'type' => 'options',
                'renderOpen' => true
            ) );

            return rest_ensure_response( array(
                'success' => true,
                'data' => $response
            ) );
        }

        return rest_ensure_response( array(
            'success' => false,
            'data' => __( 'Adding a repeatee failed', 'alchemy' )
        ) );
    }

    function handle_clone_repeatee( WP_REST_Request $request ) {
        $body_params = $request->get_body_params();

        $this->security_checks( $body_params );

        $repeateeData = isset( $body_params['values'] ) ? json_decode( $body_params['values'], true ) : [];

        if( isset( $body_params['repeater-id'] ) && isset( $body_params['repeatees-number'] ) ) {
            $response = $this->get_repeatee_html( array(
                'repeater' => array(
                    'id' => $body_params['id'],
                    'repeater-id' => $body_params['repeater-id'],
                ),
                'repeatee' => $repeateeData[0],
                'index' => $body_params['repeatees-number'],
                'type' => 'options',
                'renderOpen' => true
            ) );

            return rest_ensure_response( array(
                'success' => true,
                'data' => $response
            ) );
        }

        return rest_ensure_response( array(
            'success' => false,
            'data' => __( 'Cloning a repeatee failed', 'alchemy' )
        ) );
    }

    private function get_repeatees_html( array $data ) : string {
        $itemsClasses = ['repeater__items', 'jsAlchemyRepeaterItems'];

        if( isset( $data['repeater']['data']['field-types'] ) ) {
            $itemsClasses[] = 'repeater__items--typed';
        }

        $html = sprintf( '<div class="%s">', join( ' ', $itemsClasses ) );

        if( ! empty( $data['savedValue'] ) ) {
            foreach ( $data['savedValue'] as $i => $repeatee ) {
                $html .= $this->get_repeatee_html( array(
                    'repeatee' => $repeatee,
                    'repeater' => $data['repeater'],
                    'type' => $data['type'],
                    'index' => $i,
                    'colorcode' => $data['repeater']['colorcode']
                ) );
            }
        }

        $html .= '</div>';

        return $html;
    }

    private function get_repeatee_html( array $data ) : string {
        $classes = [ 'repeatee', 'jsAlchemyRepeatee' ];
        $repeatee = $data['repeatee'];
        $meta = $repeatee['meta'];
        $repeatee['values'] = $this->modify_values( array(
            'repeater' => $data['repeater'],
            'repeatee' => $repeatee,
            'index' => $data['index']
        ) );
        $fieldsHTML = '';
        $colorCode = $data['colorcode'] ?? array('enabled' => false, 'colors' => []);

        if( ! empty( $data['renderOpen'] ) ) {
            $classes[] = 'repeatee--expanded';
        }

        if( isset( $meta['visible'] ) && empty( $meta['visible'] ) ) {
            $classes[] = 'repeatee--hidden';
        }

        if( isset( $meta['id'] ) && ! empty( $meta['id'] ) ) {
            $classes[] = 'repeatee--typed';
        }

        $html = sprintf( '<div class="%1$s" data-meta="%2$s">',
            join( ' ', $classes ),
            esc_attr( json_encode( (object) $meta ) )
        );

        switch( $data['type'] ) {
            case 'metabox' :
                $fieldsHTML = Options::get_meta_html( get_the_ID(), $repeatee['values'] );
            break;
            case 'options' :
                $fieldsHTML = Options::get_options_html( $repeatee['values'] );
            break;
            case 'network-options' :
                $fieldsHTML = Options::get_network_options_html( $repeatee['values'] );
            break;
        }

        $html .= $this->get_actions_group_html( $meta, $colorCode );
        $html .= sprintf( '<div class="repeatee__fields">%s</div>', $fieldsHTML );

        $html .= alch_get_validation_tooltip();

        $html .= '</div>';

        return $html;
    }

    private function modify_values( array $data ) : array {
        $registeredRepeaters = Options::get_registered_repeaters();
        $settings = $registeredRepeaters[$data['repeater']['repeater-id']];
        $modifiedValues = [];

        $neededFields = $settings['fields'] ?? [];

        if( isset( $settings['field-types'] ) ) {
            $repeateeType = $data['repeatee']['meta']['id'] ?? null;

            $foundFields = $this->get_fields_for_type( $settings, $repeateeType );

            if( ! empty( $foundFields ) ) {
                $neededFields = $foundFields['fields'];
            }
        }

        foreach ( $neededFields as $fieldIndex => $field ) {
            $savedField = isset( $data['repeatee']['values'] )
                ? array_values( array_filter( $data['repeatee']['values'], function( $savedField ) use( $field ) {
                    return $savedField['id'] === $field['id'];
                } ) )
                : null;

            if( ! empty( $savedField ) ) {
                $field['value'] = $savedField[0]['value'];
            }

            $field['id'] = sprintf( '%s-%s-%s',
                $data['repeater']['id'],
                $data['index'],
                $field['id']
            );

            $modifiedValues[] = $field;
        }

        return $modifiedValues;
    }

    private function get_fields_for_type( array $settings, $type ) : array {
        return array_values( array_filter( $settings['field-types'], function( $fieldType ) use( $type ) {
            return $fieldType['id'] === $type;
        } ) )[0];
    }

    private function get_add_new_button_html( array $settings ) : string {
        $html = '<div class="repeater__add-new">';

        if( ! empty( $settings['field-types'] ) ) {
            $html .= '<div class="repeater__add-from-type">';

			if( count( $settings['field-types'] ) > 10 ) {
				$html .= $this->get_field_types_select_html( $settings );
			} else {
				$html .= sprintf( '<button type="button" class="%1$s">%2$s %3$s</button>%4$s',
					'repeater__add-new button button-primary jsAlchemyRepeaterTypeTrigger',
					__( 'Add from type', 'alchemy' ),
					'<span class="dashicons dashicons-arrow-down-alt2"></span>',
					'<span class="alchemy__spinner alchemy__spinner--hidden alchemy__spinner--alch jsAlchemyRepeaterLoader"><svg xmlns="http://www.w3.org/2000/svg" style="fill:#808080" viewBox="0 0 32.88 34.34"><path d="M31.28,23.94A15.82,15.82,0,0,0,19.22,3a1.93,1.93,0,0,0,0-.24,2.76,2.76,0,0,0-5.52,0c0,.08,0,.16,0,.23A15.79,15.79,0,0,0,1.56,23.86a2.76,2.76,0,1,0,2.73,4.78,15.78,15.78,0,0,0,24.23.06,2.73,2.73,0,0,0,1.59.5,2.77,2.77,0,0,0,2.76-2.76A2.79,2.79,0,0,0,31.28,23.94ZM25.21,9.77a12.3,12.3,0,0,1,3.64,8.78,12.56,12.56,0,0,1-.78,4.36L18.48,6.3A12.27,12.27,0,0,1,25.21,9.77Zm-.94,13.3H8.6L16.43,9.5ZM7.65,9.77A12.33,12.33,0,0,1,14.39,6.3L4.8,22.91A12.56,12.56,0,0,1,4,18.55,12.34,12.34,0,0,1,7.65,9.77ZM16.43,31a12.37,12.37,0,0,1-8.78-3.63c-.28-.29-.55-.58-.8-.89H26a11.22,11.22,0,0,1-.81.89A12.34,12.34,0,0,1,16.43,31Z"/></svg></span>'
				);

				$html .= sprintf( '<div class="repeater__field-types jsAlchemyRepeaterFieldTypes">%s</div>',
					$this->get_field_types_list_html( $settings )
				);
			}

            $html .= '</div>';
        } else {
            $html .= sprintf( '<button type="button" class="%1$s">%2$s</button>%3$s',
                'repeater__add-new button button-primary jsAlchemyAddRepeatee',
                __( 'Add new', 'alchemy' ),
                '<span class="alchemy__spinner alchemy__spinner--hidden alchemy__spinner--alch jsAlchemyRepeaterLoader"><svg xmlns="http://www.w3.org/2000/svg" style="fill:#808080" viewBox="0 0 32.88 34.34"><path d="M31.28,23.94A15.82,15.82,0,0,0,19.22,3a1.93,1.93,0,0,0,0-.24,2.76,2.76,0,0,0-5.52,0c0,.08,0,.16,0,.23A15.79,15.79,0,0,0,1.56,23.86a2.76,2.76,0,1,0,2.73,4.78,15.78,15.78,0,0,0,24.23.06,2.73,2.73,0,0,0,1.59.5,2.77,2.77,0,0,0,2.76-2.76A2.79,2.79,0,0,0,31.28,23.94ZM25.21,9.77a12.3,12.3,0,0,1,3.64,8.78,12.56,12.56,0,0,1-.78,4.36L18.48,6.3A12.27,12.27,0,0,1,25.21,9.77Zm-.94,13.3H8.6L16.43,9.5ZM7.65,9.77A12.33,12.33,0,0,1,14.39,6.3L4.8,22.91A12.56,12.56,0,0,1,4,18.55,12.34,12.34,0,0,1,7.65,9.77ZM16.43,31a12.37,12.37,0,0,1-8.78-3.63c-.28-.29-.55-.58-.8-.89H26a11.22,11.22,0,0,1-.81.89A12.34,12.34,0,0,1,16.43,31Z"/></svg></span>'
            );
        }

        $html .= '</div>';

        return $html;
    }

    private function get_field_types_select_html( array $data ) : string {
		$html = '<div class="repeater__select jsAlchRepeaterSelect"><select><option></option>';

		foreach ( $data['field-types'] as $fieldType ) {
			$html .= sprintf( '<option data-repeater="%1$s" value="%2$s">%2$s</option>',
				esc_attr( json_encode(array(
					'id' => $data['id'],
					'type' => array(
						'id' => $fieldType['id'],
						'title' => $fieldType['title'],
					),
				)) ),
				$fieldType['title']
			);
		}

		$html .= '</select><span class="alchemy__spinner alchemy__spinner--hidden alchemy__spinner--alch jsAlchemyRepeaterLoader"><svg xmlns="http://www.w3.org/2000/svg" style="fill:#808080" viewBox="0 0 32.88 34.34"><path d="M31.28,23.94A15.82,15.82,0,0,0,19.22,3a1.93,1.93,0,0,0,0-.24,2.76,2.76,0,0,0-5.52,0c0,.08,0,.16,0,.23A15.79,15.79,0,0,0,1.56,23.86a2.76,2.76,0,1,0,2.73,4.78,15.78,15.78,0,0,0,24.23.06,2.73,2.73,0,0,0,1.59.5,2.77,2.77,0,0,0,2.76-2.76A2.79,2.79,0,0,0,31.28,23.94ZM25.21,9.77a12.3,12.3,0,0,1,3.64,8.78,12.56,12.56,0,0,1-.78,4.36L18.48,6.3A12.27,12.27,0,0,1,25.21,9.77Zm-.94,13.3H8.6L16.43,9.5ZM7.65,9.77A12.33,12.33,0,0,1,14.39,6.3L4.8,22.91A12.56,12.56,0,0,1,4,18.55,12.34,12.34,0,0,1,7.65,9.77ZM16.43,31a12.37,12.37,0,0,1-8.78-3.63c-.28-.29-.55-.58-.8-.89H26a11.22,11.22,0,0,1-.81.89A12.34,12.34,0,0,1,16.43,31Z"/></svg></span></div>';

		return $html;
	}

    private function get_field_types_list_html( array $data ) : string {
		$html = '<ul>';

		foreach ( $data['field-types'] as $fieldType ) {
			$html .= sprintf( '<li><button type="button" class="%1$s" data-repeater="%2$s">%3$s</button></li>',
				'button button-secondary jsAlchemyAddRepeatee',
				esc_attr( json_encode(array(
					'id' => $data['id'],
					'type' => array(
						'id' => $fieldType['id'],
						'title' => $fieldType['title'],
					),
				)) ),
				$fieldType['title']
			);
		}

		$html .= '</ul>';

		return $html;
	}

    private function get_get_color_choices_html( array $data ) : string {
        if( ! $data['enabled'] ) {
            return '';
        }

        return sprintf( '<div class="repeater__color-choices">%s</div>',
            join( '', array_map( function( $color ) {
                return sprintf( '<button type="button" data-color="%1$s" class="repeater__color-choice jsRepeaterColorChoice" style="background-color: %1$s;"></button>', $color );
            }, $data['colors'] ) )
        );
    }

    private function security_checks( array $body_params ) {
        if( empty( $body_params['_wpnonce'] ) || ! wp_verify_nonce( $body_params['_wpnonce'], 'wp_rest' ) ) {
            return rest_ensure_response( new WP_Error(
                'alch-repeater-nonce-failure',
                __( 'Nonce check failed', 'alchemy' ),
                array( 'status' => 401 )
            ) );
        }
    }

    private function get_fields_data( array $settings ) : array {
        $repeaterFieldsData = array();

        if( ! empty( $settings['field-types'] ) ) {
            $repeaterFieldsData['field-types'] = array_map( function( $fieldType ) {
                return array(
                    'type' => $fieldType['id'],
                    'fields' => array_map( function( $fieldTypeField ) {
                        return array(
                            'id' => $fieldTypeField['id'],
                            'type' => $fieldTypeField['type'],
                        );
                    }, $fieldType['fields'] ),
                );
            }, $settings['field-types'] );
        } else if( ! empty( $settings['fields'] ) ) {
            $repeaterFieldsData['fields'] = array_map( function( $fieldTypeField ) {
                return array(
                    'id' => $fieldTypeField['id'],
                    'type' => $fieldTypeField['type'],
                );
            }, $settings['fields'] );
        }

        return $repeaterFieldsData;
    }

    private function get_actions_group_html( array $meta = [], array $colorCode = [] ) : string {
        $toolbarClasses = ['repeatee__toolbar', 'jsAlchemyRepeateeToolbar', 'clearfix'];

        if( $colorCode['enabled'] ) {
            $toolbarClasses[] = 'repeatee__toolbar--can-colorcode';
        }

        $actionGroupHTML = sprintf(
            '%3$s<div class="%1$s" title="%2$s">',
            join( ' ', $toolbarClasses ),
            __( 'Click to edit', 'alchemy' ),
            ( ! empty( $meta ) && ! empty( $meta['title'] ) ) ? sprintf( '<small class="repeatee__type">%s</small>', $meta['title'] ) : ''
        );

        $repeateeVisible = $meta['visible'] ?? true;
		$meta['label'] = $meta['label'] ?? '';

        $visibilityIcon = 'dashicons';
        $visibilityIconClasses = ['repeatee__btn', 'button', 'button-secondary', 'jsAlchemyRepeateeAction'];

        if( ! $repeateeVisible ) {
            $visibilityIconClasses[] = 'repeatee__btn--active';
            $visibilityIcon .= ' dashicons-hidden';
        } else {
            $visibilityIcon .= ' dashicons-visibility';
        }

        if( $colorCode['enabled'] ) {
            $actionGroupHTML .= '<div class="repeatee__color-coder color-coder jsAlchemyRepeateeColor">';

            $actionGroupHTML .= sprintf( '<button tabindex="-1" type="button" class="%1$s" title="%2$s"></button>',
                'color-coder__trigger jsAlchemyRepeateeColorTrigger',
                __( 'Choose a color for this item', 'alchemy' )
            );

            $actionGroupHTML .= '</div>';
        }

        $actionGroupHTML .= sprintf( '<span class="repeatee__dnd-icon jsRepeateeDndHandle" title="%s"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 39 59"><circle cx="7.5" cy="7.5" r="7.5"/><circle cx="31.5" cy="7.5" r="7.5"/><circle cx="7.5" cy="29.5" r="7.5"/><circle cx="31.5" cy="29.5" r="7.5"/><circle cx="7.5" cy="51.5" r="7.5"/><circle cx="31.5" cy="51.5" r="7.5"/></svg></span>',
            __( 'Drag to reorder', 'alchemy' )
        );

        $actionGroupHTML .= sprintf( '<span class="repeatee__title"><span class="jsRepeateeTitleText">%1$s</span><input type="text" class="jsRepeateeTitle" value="%1$s" /></span>',
            esc_attr( $meta['label'] )
        );

        $actionGroupHTML .= '<span class="repeatee__actions button-group">';

        $actionGroupHTML .= sprintf( '<button type="button" class="%1$s" title="%3$s" data-action="hide">%2$s</button>',
            join( ' ', $visibilityIconClasses ),
            sprintf( '<span class="%s"></span>', $visibilityIcon ),
            __( 'Exclude this item from showing', 'alchemy' )
        );

        $actionGroupHTML .= sprintf( '<button type="button" class="%1$s" title="%3$s" data-action="clone">%2$s</button>',
            'repeatee__btn button button-secondary jsAlchemyRepeateeAction',
            '<span class="dashicons dashicons-admin-page"></span>',
            __( 'Clone this item', 'alchemy' )
        );

        $actionGroupHTML .= sprintf( '<button type="button" class="%1$s" title="%3$s" data-action="delete">%2$s</button>',
            'repeatee__btn button button-secondary jsAlchemyRepeateeAction',
            '<span class="dashicons dashicons-trash"></span>',
            __( 'Delete this item', 'alchemy' )
        );

        $actionGroupHTML .= '</span>';
        $actionGroupHTML .= '</div>';

        return $actionGroupHTML;
    }
}
