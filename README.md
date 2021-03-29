<!--
<!-- ***
https://www.markdownguide.org/basic-syntax/#reference-style-links
 -->
<p align="center">

<!-- [![Contributors][contributors-shield]][contributors-url]
[![Forks][forks-shield]][forks-url]
[![Stargazers][stars-shield]][stars-url]
[![Issues][issues-shield]][issues-url] -->
[![GPL License][license-shield]][license-url]

</p>
<!-- ***
<!-- -->

Donate Link: https://paypal.me/gg1008

<h1 align="center">WordPress Admin Onboarding Wizard</h1>
<div align="center">
	<p>
		CREATE WORDPRESS ADMIN ONBOARDING WIZARD -|- INSTALL DEPENDENCY/RECOMMENDED PLUGINS
	</p>
	<p>
		This plugin is a framework meant for creating onboarding wizard for any plugins.
		<br>
		(For testing purpose, this can be installed as a plugin itself)
	</p>
</div>

## Installation (via Composer)
To install this plugin, edit your `composer.json` file:
```json
"require": {
	"thewebsolver/tws-admin-onboarding": "dev-master"
}
```
Then from terminal, run:
```sh
$ composer install
```

## Example Code
>File [tws-admin-onboarding.php](https://github.com/TheWebSolver/tws-admin-onboarding/blob/master/tws-admin-onboarding.php) contains all the codes to start onboarding on plugin activation. Mainly check `My_Plugin\My_Feature\Onboarding::activate` method on how to redirect to onboarding after plugin activation.

Below is the extract of this file. You must use below codes in your own plugin file and delete the [tws-admin-onboarding.php](https://github.com/TheWebSolver/tws-admin-onboarding/blob/master/tws-admin-onboarding.php) file.

### In brief, you must:
- set your own plugin's prefix,
- declare your own plugin's namespace in files [Config.php](https://github.com/TheWebSolver/tws-admin-onboarding/blob/master/Config.php) and [Wizard.php](https://github.com/TheWebSolver/tws-admin-onboarding/blob/master/Includes/Wizard.php),
- set your own step contents in [Wizard.php](https://github.com/TheWebSolver/tws-admin-onboarding/blob/master/Includes/Wizard.php), and
- check `@todo` tags across all files and make appropriate changes.

___
### NOTE: Before plugin activation
Must make changes to *namespace* and *prefix*. If no changes are made to *namespace* and *prefix*, WordPress dies with appropriate message.
___

```php
<?php // file tws-admin-onboarding.php

/**
 * Onboarding namespace.
 *
 * @todo MUST REPLACE AND USE OWN NAMESPACE.
 */
namespace My_Plugin\My_Feature;

/**
 * Boilerplate plugin for The Web Solver WordPress Admin Onboarding Wizard.
 */
final class Onboarding {
	/**
	 * Onboarding wizard prefix.
	 *
	 * @var string
	 * @todo Prefix for onboarding wizard. DO NOT CHANGE IT ONCE SET.\
	 *       It will be used for WordPress Hooks, Options, Transients, etc.\
	 *       MUST BE A UNIQUE PREFIX FOR YOUR PLUGIN.
	 */
	public $prefix = 'thewebsolver';

	/**
	 * Onboarding Wizard Config.
	 *
	 * @var Config
	 */
	public $config;

	/**
	 * Starts Onboarding.
	 *
	 * @return Onboarding
	 */
	public static function start() {
		static $onboarding;
		if ( ! is_a( $onboarding, get_class() ) ) {
			$onboarding = new self();
		}
		return $onboarding;
	}

	/**
	 * Private constructor to prevent direct instantiation.
	 */
	private function __construct() {
		$this->config();
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		/**
		 * If all onboarding steps are not completed, show admin notice.
		 *
		 * At last step of onboarding, $status => 'complete'.
		 *
		 * @var string
		 *
		 * @todo Need to perform additional checks before showing notice
		 *       Such as show notice on plugins, themes and dashboard pages only.
		 */
		$status = get_option( $this->prefix . '_onboarding_steps_status' );
		if ( 'pending' === $status ) {
			add_action( 'admin_notices', array( $this, 'onboarding_notice' ) );
		}
	}

	/**
	 * Instantiates onboarding config.
	 */
	private function config() {
		// Onboarding config file path.
		include_once __DIR__ . '/Config.php';
		$config = array( '\\' . __NAMESPACE__ . '\\Config', 'get' );

		// Only call config if it is on the same namespace.
		if ( is_callable( $config ) ) {
			$this->config = call_user_func( $config, $this->prefix );
		}
	}

	/**
	 * Sets onboarding notice if not completed.
	 */
	public function onboarding_notice() {
		$msg = sprintf(
			'<p><b>%1$s</b> - %2$s.</p><p><a href="%3$s" class="button-primary">%4$s</a></p>',
			__( 'Namaste! from The Web Solver Onboarding Wizard', 'tws-onboarding' ),
			__( 'Let us help you quickly setup the plugin with our onboarding wizard', 'tws-onboarding' ),
			admin_url( 'admin.php?page=' . $this->config->get_page() ),
			__( 'Run the Wizard Now', 'tws-onboarding' )
		);

		echo '<div class="notice notice-info">' . wp_kses_post( $msg ) . '</div>';
	}

	/**
	 * Performs task on plugin activation.
	 *
	 * @todo Configured with example codes. Make changes as needed.
	 */
	public function activate() {
		// Check if plugin is already installed.
		$old_install = get_option( $this->prefix . '_install_version', false );

		if ( ! $old_install ) {
			// if new install => enable onboarding.
			$check[] = 'true';

			// Set the plugin install version to "1.0".
			update_option( $this->prefix . '_install_version', '1.0' );
		} else {
			// There is now installed version "1.0" => disable onboarding.
			$check[] = 'false';
		}

		// If PHP version less than or equal to "7.0" => disable onboarding.
		if ( version_compare( phpversion(), '7.0', '<=' ) ) {
			$check[] = 'false';
		}

		// Now onboarding will run on the basis of check parameter passed.
		// If this is first activation or PHP > 7.0 => redirect to onboarding page.
		// Lets also verify if config has been instantiated.
		if ( is_object( $this->config ) ) {
			$this->config->enable_onboarding( $check );
		}
	}

	/**
	 * Performs task on plugin deactivation.
	 *
	 * @todo Configured to delete onboarding options on plugin deactivation.\
	 *       Cane be safely deleted for production.
	 */
	public function deactivate() {
		// Onboarding options.
		delete_option( $this->prefix . '_onboarding_steps_status' );
		delete_option( $this->prefix . '_onboarding_dependency_status' );
		delete_option( $this->prefix . '_onboarding_dependency_name' );
		delete_option( $this->prefix . '_install_version' );

		// Onboarding transitents.
		delete_transient( $this->prefix . '_onboarding_redirect' );
	}
}

Onboarding::start();
```

<!-- SCREENSHOTS -->
## Screenshots
***Introduction***, ***Recommended*** and ***Ready*** steps are default steps and are created from [onboarding] abstract class file.

Other steps added from the [wizard] file.
### Introduction
![Intro Step][intro]
### Introduction (after install)
![Intro post install][intro_post_install]
### Text/Checkbox Fields
![Text/Checkbox Step][text]
### Radio/Select Fields
![Radio/Select Step][select]
### Recommended
![Recommended Step][recommended]
### Ready
![Ready Step][ready]

<!-- CONTACT -->
## Contact

```sh
----------------------------------
DEVELOPED-MAINTAINED-SUPPPORTED BY
----------------------------------
███║     ███╗   ████████████████
███║     ███║   ═════════██████╗
███║     ███║        ╔══█████═╝
 ████████████║      ╚═█████
███║═════███║      █████╗
███║     ███║    █████═╝
███║     ███║   ████████████████╗
╚═╝      ╚═╝    ═══════════════╝
 ```
 Shesh Ghimire - [@hsehszroc](https://twitter.com/hsehszroc)

[license-shield]: https://www.gnu.org/graphics/gplv3-or-later-sm.png
[license-url]: https://github.com/TheWebSolver/tws-admin-onboarding/blob/master/LICENSE
[intro]: Assets/Screenshots/intro.png
[intro_post_install]: Assets/Screenshots/intro-installed.png
[text]: Assets/Screenshots/text.png
[select]: Assets/Screenshots/select.png
[recommended]: Assets/Screenshots/recommended.png
[ready]: Assets/Screenshots/ready.png
[onboarding]: https://github.com/TheWebSolver/tws-admin-onboarding/blob/master/Includes/Source/Onboarding.php
[wizard]: https://github.com/TheWebSolver/tws-admin-onboarding/blob/master/Includes/Wizard.php
