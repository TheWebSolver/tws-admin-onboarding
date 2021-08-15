<?php // phpcs:ignore WordPress.NamingConventions
/**
 * The Web Solver WordPress Admin Onboarding Wizard Form and it's fields generator class.
 *
 * @package TheWebSolver\Core\Admin\Onboarding
 *
 * -----------------------------------
 * DEVELOPED-MAINTAINED-SUPPPORTED BY
 * -----------------------------------
 * ███║     ███╗   ████████████████
 * ███║     ███║   ═════════██████╗
 * ███║     ███║        ╔══█████═╝
 *  ████████████║      ╚═█████
 * ███║═════███║      █████╗
 * ███║     ███║    █████═╝
 * ███║     ███║   ████████████████╗
 * ╚═╝      ╚═╝    ═══════════════╝
 */

namespace TheWebSolver\Core\Admin\Onboarding;

use TheWebSolver;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( __NAMESPACE__ . '\\Form' ) ) {
	/**
	 * Onboarding Step Form/Fields class.
	 *
	 * @class TheWebSolver\Core\Admin\Onboarding\Form
	 */
	class Form {
		/**
		 * The prefixer.
		 *
		 * @var string
		 *
		 * @since 1.1
		 */
		protected $prefix;

		/**
		 * Current step ID.
		 *
		 * @var string
		 *
		 * @since 1.1
		 */
		private $step;

		/**
		 * Template file path.
		 *
		 * @var string
		 *
		 * @since 1.1
		 */
		protected $template_path;

		/**
		 * Constructor.
		 *
		 * @param string $prefix        The prefixer.
		 * @param string $template_path The template files path.
		 *
		 * @since 1.1
		 */
		public function __construct( string $prefix, string $template_path = '' ) {
			$this->prefix        = $prefix;
			$this->template_path = $template_path;
		}

		/**
		 * Sets current step for which form needs to be displayed.
		 *
		 * @param string $step The current onboarding step.
		 *
		 * @since 1.1
		 */
		public function set_step( $step ) {
			$this->step = $step;
		}

		/**
		 * Generates Form HTML tags.
		 *
		 * @since 1.1
		 */
		public function start() {
			$this->show( 'start', array( 'step' => $this->step ) );
		}

		/**
		 * Generates Form Closure HTML tags.
		 *
		 * @since 1.1
		 */
		public function end() {
			$this->show( 'end' );
		}

		/**
		 * Generates Form field.
		 *
		 * @param string $type The field type. Supported field types are:
		 * `text` | `textarea` | `checkbox` | `radio` | `select`.
		 * @param array  $args The field args.
		 * * `string` `id`          - (required) The field unique ID.
		 * * `string` `label`       - (required) The field label.
		 * * `string` `desc`        - (optional) The field description.
		 * * `string` `placeholder` - (optional) The field placeholder.
		 * * `string` `default`     - (optional) The field default value.
		 * * `array`  `options`     - (required) The radio/select field id as key and info as value.
		 * * `string` `class`       - (optional) The class to be applied to field's top level tag.
		 *
		 * @since 1.1
		 */
		public function add_field( string $type = 'text', array $args ) {
			if ( 'textarea' === $type ) {
				$args['placeholder'] = isset( $args['placeholder'] ) ? $args['placeholder'] : 'Example placeholder&#13;Another feature in new line&#13;Last feature in new line&#13;and so on....';
			} elseif ( 'radio' === $type || 'select' === $type ) {
				$args['options'] = isset( $args['options'] ) && is_array( $args['options'] ) ? $args['options'] : array();
			}

			$this->show( $type, $this->set_args( $args ) );
		}

		/**
		 * Saves current step Form Fields' values.
		 *
		 * Field types with sanitization callback & default value before saving to database are:
		 *
		 * | Type      | Callback                | Value Type | Default Value |
		 * |-----------|-------------------------|------------|---------------|
		 * | text      | sanitize_text_field     | string     | empty string  |
		 * | textarea  | sanitize_textarea_field | string     | empty string  |
		 * | checkbox  | sanitize_key            | string     | off           |
		 * | radio     | sanitize_key            | string     | empty string  |
		 * | select    | sanitize_key            | string     | empty string  |.
		 *
		 * @param string[] $field_ids The unique field IDs as key, respective field type as value.
		 *                            Field IDs must be same used in the respective
		 *                            onboarding step's view method.
		 *
		 * @since 1.1
		 * @example Usage
		 * ```
		 * // In the current step's save method, call Form::save() method like so:
		 * $this->config->form->save(
		 *  array(
		 *   'text_field_id'     => 'text',
		 *   'textarea_field_id' => 'textarea',
		 *  )
		 * );
		 * ```
		 */
		public function save( array $field_ids ) {
			$p = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

			// Bail if no Form Fields exist.
			if ( ! isset( $p[ $this->get_key() ] ) || ! is_array( $p[ $this->get_key() ] ) ) {
				return;
			}

			$post = $p[ $this->get_key() ];
			$save = array();

			foreach ( $field_ids as $id => $type ) {
				// Sanitization callback & default value by Field Type.
				switch ( $type ) {
					case 'text':
						$callback = 'sanitize_text_field';
						$default  = '';
						break;
					case 'textarea':
						$callback = 'sanitize_textarea_field';
						$default  = '';
						break;
					case 'checkbox':
						$callback = 'sanitize_key';
						$default  = 'off';
						break;
					case 'radio':
					case 'select':
						$callback = 'sanitize_key';
						$default  = '';
						break;
					default:
						$callback = 'sanitize_text_field';
						$default  = '';
				}

				$save[ $id ] = isset( $post[ $id ] ) ? call_user_func( $callback, $post[ $id ] ) : $default;
			}

			// Save current step's field values in an array, if exist.
			if ( ! empty( $save ) ) {
				update_option( $this->get_key(), $save, false );
			}
		}

		/**
		 * Gets saved form field values of the given step.
		 *
		 * @param string $step    (required) The current step ID (key/index).
		 * @param string $id      (optional) The field unique ID.
		 *                        If passed, the given field ID's value will be returned.
		 * @param mixed  $default (optional) The default value.
		 *                        Only works if `$id` passed. Defaults to an empty string.
		 *
		 * @since 1.1
		 */
		public function get_value_by( string $step, string $id = '', $default = '' ) {
			$option = get_option( $this->get_key( $step ) );

			if ( ! $id ) {
				return $option;
			}

			return isset( $option[ $id ] ) ? $option[ $id ] : $default;
		}

		/**
		 * Gets current step option key.
		 *
		 * @param string $step The current step ID. Used when getting form field value.
		 *
		 * @return string
		 *
		 * @since 1.1
		 */
		protected function get_key( string $step = '' ) : string {
			$_step = $step ? $step : $this->step;

			return $this->prefix . '_' . $_step;
		}

		/**
		 * Prepares template args for form field.
		 *
		 * @param array $args The field args.
		 *
		 * @return array
		 *
		 * @since 1.1
		 */
		protected function set_args( array $args ) : array {
			$default_value = isset( $args['default'] ) ? $args['default'] : '';

			$template_args = array(
				'id'    => $this->get_key() . '[' . $args['id'] . ']',
				'label' => $args['label'],
				'desc'  => isset( $args['desc'] ) ? $args['desc'] : '',
				'value' => $this->get_value_by( $this->step, $args['id'], $default_value ),
				'class' => isset( $args['class'] ) ? ' ' . $args['class'] : '',
			);

			if ( isset( $args['placeholder'] ) ) {
				$template_args['placeholder'] = $args['placeholder'];
			}

			if ( isset( $args['options'] ) ) {
				$template_args['options'] = $args['options'];
			}

			return $template_args;
		}

		/**
		 * Gets the template file.
		 *
		 * @param string $filename The template file name.
		 * @param array  $args     Args passed to the template file.
		 *
		 * @since 1.1
		 */
		protected function show( string $filename, array $args = array() ) {
			TheWebSolver::get_template( "onboarding-form/{$filename}.php", $args, '', $this->template_path );
		}
	}
}
