<?php // phpcs:ignore WordPress.NamingConventions
/**
 * The Web Solver Onboarding Wizard Initialization.
 *
 * @package TheWebSolver\Core\Admin\Onboarding\Class
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

namespace TheWebSolver\Woo\Attribute\Onboarding;

// namespace My_Plugin\My_Feature; // phpcs:ignore -- Namespace Example. Uncomment and use your own.

use TheWebSolver;
use TheWebSolver\Core\Admin\Onboarding\Wizard;
use TheWebSolver\Core\Setting\Options;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Onboarding Wizard configuration.
 *
 * {@see @method Config::create_wizard()}
 *
 * This extends the main Wizard class.
 * Use this as a boilerplate for creating own onboarding wizard.
 */
class Onboarding_Wizard extends Wizard {
	/**
	 * Gets the wizard config instance.
	 *
	 * @return Config
	 */
	private function config() {
		return Config::get( __NAMESPACE__ );
	}

	/**
	 * Sets onboarding prefix.
	 *
	 * @inheritDoc
	 */
	protected function set_prefix() {
		$this->prefix = $this->config()->get_prefix();
	}

	/**
	 * Sets onboarding page slug.
	 *
	 * @inheritDoc
	 */
	protected function set_page() {
		$this->page = $this->config()->get_page();
	}

	/**
	 * Sets onboarding root URL relative to current plugin's directory.
	 *
	 * @inheritDoc
	 */
	protected function set_url() {
		$this->url = trailingslashit( $this->config()->get_url() );
	}

	/**
	 * Sets onboarding root path relative to current plugin's directory.
	 *
	 * @inheritDoc
	 */
	protected function set_path() {
		$this->path = $this->config()->get_path();
	}

	/**
	 * Sets onboarding HTML head title.
	 *
	 * @inheritDoc
	 */
	protected function set_title() {
		$this->title = __( 'MyPlugin Onboarding', 'tws-onboarding' );
	}

	/**
	 * Sets user capability to run onboarding wizard.
	 *
	 * @inheritDoc
	 */
	protected function set_capability() {
		$this->capability = $this->config()->get_capability();
	}

	/**
	 * Sets onboarding logo.
	 *
	 * @inheritDoc
	 */
	protected function set_logo() {
		$this->logo = array(
			'href'   => get_site_url( get_current_blog_id() ),
			'alt'    => 'The Web Solver Onboarding',
			'width'  => '135px',
			'height' => 'auto',
			'src'    => HZFEX_WOO_PAS_URL . 'Assets/Graphics/Options/separate-tabs.svg',
		);
	}

	/**
	 * Onboarding steps.
	 *
	 * @inheritDoc
	 */
	protected function set_steps() {
		$steps = array(
			'general' => array(
				'name' => __( 'General', 'tws-onboarding' ),
				'desc' => __( 'Let\'s set WooCommerce Attribute group names for managing attributes in respective group.', 'tws-onboarding' ),
				'view' => array( $this, 'general_view' ),
				'save' => array( $this, 'general_save' ),
			),
			'product' => array(
				'name' => __( 'Product Page', 'tws-onboarding' ),
				'desc' => __( 'Let\'s set how attribute groups will be displayed on the single product page.', 'tws-onboarding' ),
				'view' => array( $this, 'product_page_view' ),
				'save' => array( $this, 'product_page_save' ),
			),
		);

		return $steps;
	}

	/**
	 * Set the recommended plugins.
	 *
	 * @inheritDoc
	 */
	protected function set_recommended_plugins() {
		// phpcs:disable -- Example recommended plugins OK.
		$plugins = array(
			array(
				'slug'  => 'show-hooks',
				'title' => __( 'Show Hooks', 'tws-onboarding' ),
				'desc'  => __( 'A sequential and visual representation of WordPess action and filter hooks.', 'tws-onboarding' ),
				'logo'  => 'https://ps.w.org/show-hooks/assets/icon-256x256.png?rev=2327503',
				'alt'   => __( 'Show Hooks Plugin', 'tws-onboarding' ),
			),
			array(
				'slug'  => 'advanced-custom-fields',
				'file'  => 'acf',
				'title' => __( 'Advanced Custom Fields', 'tws-onboarding' ),
				'desc'  => __( 'Use the Advanced Custom Fields plugin to take full control of your WordPress edit screens & custom field data.', 'tws-onboarding' ),
				'logo'  => 'https://ps.w.org/advanced-custom-fields/assets/icon-256x256.png?rev=1082746',
				'alt'   => __( 'ACF logo', 'tws-onboarding' ),
			),
			array(
				'slug'  => 'query-monitor',
				'file'  => 'query-monitor',
				'title' => __( 'Query Monitor', 'tws-onboarding' ),
				'desc'  => __( 'The Developer Tools Panel for WordPress.', 'tws-onboarding' ),
				'logo'  => 'https://ps.w.org/query-monitor/assets/icon-256x256.png?rev=2301273',
				'alt'   => __( 'Query monitor logo', 'tws-onboarding' ),
			),
			array(
				'slug'  => 'ultimate-member',
				'title' => __( 'Ultimate Member', 'tws-onboarding' ),
				'desc'  => __( 'Ultimate Member is a free user profile WordPress plugin that makes it easy to create powerful online communities and membership sites with WordPress.', 'tws-onboarding' ),
				'logo'  => 'https://ps.w.org/ultimate-member/assets/icon-256x256.png?rev=2143339',
				'alt'   => __( 'Ultimate Member logo', 'tws-onboarding' ),
			),
			array(
				'slug'  => 'wp-reset',
				'title' => __( 'WP Reset', 'tws-onboarding' ),
				'desc'  => __( 'WP Reset quickly resets the site’s database to the default installation values without modifying any files.', 'tws-onboarding' ),
				'logo'  => 'https://ps.w.org/wp-reset/assets/icon-256x256.png?rev=1906468',
				'alt'   => __( 'WP Reset logo', 'tws-onboarding' ),
			),
		);

		$this->recommended = $plugins;
		// phpcs:enable -- Example recommended plugins OK.
	}

	/**
	 * Displays General Settings options.
	 */
	public function general_view() {
		$group_names       = Options::get_option( 'attribute_group_names', 'hzfex_woopas_basic_config', '' );
		$custom_group_name = Options::get_option( 'custom_attribute_group_name', 'hzfex_woopas_basic_config', 'Additional Features' );
		$exclude_custom    = Options::get_option( 'exclude_custom_attribute_group', 'hzfex_woopas_basic_config', 'off' );

		// Display general step contents from template file.
		TheWebSolver::get_template(
			'onboarding/general.php',
			array(
				'group_names'       => $group_names,
				'custom_group_name' => $custom_group_name,
				'maybe_checked'     => $exclude_custom,
				'onboarding'        => $this,
			),
		);
	}

	/**
	 * Saves General Settings options.
	 */
	public function general_save() {
		$this->validate_save();

		$options = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification
		$general = get_option( 'hzfex_woopas_basic_config', array() );

		// Prepare options value to save.
		$general['attribute_group_names']          = ! empty( $options['hzfex_woopas_basic_config']['attribute_group_names'] ) ? sanitize_textarea_field( $options['hzfex_woopas_basic_config']['attribute_group_names'] ) : '';
		$general['custom_attribute_group_name']    = ! empty( $options['hzfex_woopas_basic_config']['custom_attribute_group_name'] ) ? sanitize_text_field( $options['hzfex_woopas_basic_config']['custom_attribute_group_name'] ) : 'Additional Features';
		$general['exclude_custom_attribute_group'] = isset( $options['hzfex_woopas_basic_config']['exclude_custom_attribute_group'] ) ? sanitize_text_field( $options['hzfex_woopas_basic_config']['exclude_custom_attribute_group'] ) : 'off';

		update_option( 'hzfex_woopas_basic_config', $general );

		wp_safe_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}

	/**
	 * Displays Product Page Setting Options.
	 */
	public function product_page_view() {
		$placement = Options::get_option( 'attribute_display', 'hzfex_woopas_product_page_config', 'additional_information' );
		$separator = Options::get_option( 'attribute_option_separator', 'hzfex_woopas_product_page_config', 'comma' );

		TheWebSolver::get_template(
			'onboarding/product-page.php',
			array(
				'placement'  => $placement,
				'separator'  => $separator,
				'onboarding' => $this,
			)
		);
	}

	/**
	 * Saves Product Page Setting Options.
	 */
	public function product_page_save() {
		$this->validate_save();

		$options = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification
		$product = get_option( 'hzfex_woopas_product_page_config', array() );

		// Prepare options value to save.
		$product['attribute_display']          = isset( $options['hzfex_woopas_product_page_config']['attribute_display'] ) ? $options['hzfex_woopas_product_page_config']['attribute_display'] : 'additional_information';
		$product['attribute_option_separator'] = isset( $options['hzfex_woopas_product_page_config']['attribute_option_separator'] ) ? $options['hzfex_woopas_product_page_config']['attribute_option_separator'] : 'comma';

		update_option( 'hzfex_woopas_product_page_config', $product );

		wp_safe_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}
}
