<?php // phpcs:ignore WordPress.NamingConventions
/**
 * The Web Solver abstraction class API.
 *
 * @package TheWebSolver\API
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

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'TheWebSolver' ) ) {
	/**
	 * The Web Solver abstraction class API.
	 *
	 * @api
	 */
	final class TheWebSolver {
		/**
		 * Prefixer for all commands.
		 *
		 * @var string
		 *
		 * @since 1.0
		 */
		const PREFIX = 'thewebsolver_';

		/**
		 * Gets the template part.
		 *
		 * @param string $slug The first part of the template file name.
		 * @param string $name The second part of the template file name after ***-***.
		 * @param array  $args The args passed to the template file.
		 *
		 * @return void
		 *
		 * @since 1.0
		 * @static
		 */
		public static function get_template_part( $slug, $name = '', $args = array() ) {
			if ( $args && is_array( $args ) ) {
				extract( $args ); // phpcs:ignore -- Extraction OK.
			}

			// Prepare template part.
			$template = '';

			/**
			 * Look in yourtheme/{dirname}/slug-name.php and yourtheme/{dirname}/slug.php.
			 * Here the {dirname} defaults to "thewebsolver" unless used filter to change it.
			 */
			$template = locate_template(
				array(
					self::get_template_path() . "{$slug}-{$name}.php",
					self::get_template_path() . "{$slug}.php",
				)
			);

			/**
			 * WPHOOK: Filter -> change the template directory path.
			 *
			 * @var string
			 *
			 * @since 1.0
			 */
			$template_path = apply_filters( 'hzfex_set_template_path', self::get_plugin_path() . '/templates', $template, $args );

			// Get default slug-name.php.
			if ( ! $template && $name && file_exists( $template_path . "/{$slug}-{$name}.php" ) ) {
					$template = $template_path . "/{$slug}-{$name}.php";
			}

			if ( ! $template && ! $name && file_exists( $template_path . "/{$slug}.php" ) ) {
					$template = $template_path . "/{$slug}.php";
			}

			/**
			 * WPHOOK: Filter -> change template part files from 3rd-party plugins.
			 *
			 * @var string
			 *
			 * @since 1.0
			 */
			$template = apply_filters( 'hzfex_get_template_part', $template, $slug, $name );

			if ( $template ) {
				include $template;
			}
		}

		/**
		 * Gets template file that can be overridden from themes.
		 *
		 * @param mixed  $template_name The template file name.
		 * @param array  $args          The agruments to be passed to template file.
		 * @param string $template_path The template path.
		 * @param string $default_path  The default path.
		 *
		 * @return void
		 *
		 * @since 1.0
		 * @static
		 */
		public static function get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
			if ( $args && is_array( $args ) ) {
				extract( $args ); // phpcs:ignore -- Extraction OK.
			}

			$located = self::locate_template( $template_name, $template_path, $default_path );

			/**
			 * WPHOOK: Action -> Fires before getting template file.
			 *
			 * @param mixed  $template_name
			 * @param string $template_path
			 * @param string $located
			 * @param array  $args
			 *
			 * @since 1.0
			 */
			do_action( 'hzfex_before_get_template', $template_name, $template_path, $located, $args );

			// Bail with wrong path info.
			if ( ! file_exists( $located ) ) {
				_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', esc_html( $located ) ), '1.0' );

					return;
			}

			// Include the located template file.
			include $located;

			/**
			 * WPHOOK: Action -> Fires after getting template file.
			 *
			 * @param mixed  $template_name
			 * @param string $template_path
			 * @param string $located
			 * @param array  $args
			 *
			 * @since 1.0
			 */
			do_action( 'hzfex_after_get_template', $template_name, $template_path, $located, $args );
		}

		/**
		 * Locates a template file and return the path for inclusion.
		 *
		 * This is the load order:
		 * - yourtheme/$template_path/$template_name
		 * - yourtheme/$template_name
		 * - $default_path/$template_name
		 *
		 * @param mixed  $template_name The template file name.
		 * @param string $template_path The template path.
		 * @param string $default_path  The default path.
		 *
		 * @return string
		 *
		 * @since 1.0
		 * @static
		 */
		public static function locate_template( $template_name, $template_path = '', $default_path = '' ) {
			// Set the template path.
			if ( ! $template_path ) {
				$template_path = self::get_template_path();
			}

			if ( ! $default_path ) {
				$default_path = untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/templates/';
			}

			// Theme priority when looking for the template file.
			$template = locate_template( array( trailingslashit( $template_path ) . $template_name ) );

			// Default to plugins directory if not found in theme.
			if ( ! $template ) {
				$template = $default_path . $template_name;
			}

			/**
			 * WPHOOK: Filter -> Send back the located template file.
			 *
			 * @since 1.0
			 */
			return apply_filters( 'hzfex_locate_template_file', $template, $template_name, $template_path );
		}

		/**
		 * Install a plugin from .org in the background via a cron job (used by
		 * installer - opt in).
		 *
		 * @param string $id       Plugin ID.
		 * @param array  $args     Plugin information.
		 * @param bool   $activate Whether to activate plugin after installation or not.
		 *
		 * @return bool True if plugin activated successfully, false otherwise.
		 *
		 * @throws Exception If unable to proceed with plugin installation.
		 *
		 * @since  2.6.0
		 * @static
		 */
		public static function silent_plugin_installer( $id, $args, $activate = true ) {
			// Explicitly clear the event.
			$func_args = func_get_args();
			$active    = false;

			if ( ! empty( $args['slug'] ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
				require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
				require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

				WP_Filesystem();

				$upgrader          = new WP_Upgrader( new Automatic_Upgrader_Skin() );
				$installed_plugins = array_reduce( array_keys( get_plugins() ), array( __CLASS__, 'get_plugin_basenames' ) );
				if ( empty( $installed_plugins ) ) {
					$installed_plugins = array();
				}
				$plugin_slug    = $args['slug'];
				$plugin_file    = isset( $args['file'] ) ? $args['file'] : $plugin_slug . '.php';
				$installed      = false;
				$maybe_activate = false;

				// See if the plugin is installed already.
				if ( isset( $installed_plugins[ $plugin_file ] ) ) {
					$installed      = true;
					$maybe_activate = ! self::maybe_plugin_is_active( $installed_plugins[ $plugin_file ] );
				}

				// Install the plugin.
				if ( ! $installed ) {
					// Suppress feedback.
					ob_start();

					try {
						$zip_link = self::get_plugin_info_from_wp_api( $plugin_slug, 'download_link' );

						if ( is_wp_error( $zip_link ) ) {
							throw new Exception( $zip_link->get_error_message() );
						}

						$download = $upgrader->download_package( $zip_link );

						if ( is_wp_error( $download ) ) {
							throw new Exception( $download->get_error_message() );
						}

						$working_dir = $upgrader->unpack_package( $download, true );

						if ( is_wp_error( $working_dir ) ) {
							throw new Exception( $working_dir->get_error_message() );
						}

						$result = $upgrader->install_package(
							array(
								'source'            => $working_dir,
								'destination'       => WP_PLUGIN_DIR,
								'clear_destination' => false,
								'abort_if_destination_exists' => false,
								'clear_working'     => true,
								'hook_extra'        => array(
									'type'   => 'plugin',
									'action' => 'install',
								),
							)
						);

						if ( is_wp_error( $result ) ) {
							throw new Exception( $result->get_error_message() );
						}

						$maybe_activate = true;

					} catch ( Exception $e ) {
						update_option(
							self::PREFIX . $id . '_install_error',
							sprintf(
								/* translators: 1: plugin name, 2: error message, 3: URL to install plugin manually. */
								__( '%1$s could not be installed (%2$s). <a href="%3$s">Please install it manually by clicking here.</a>', 'thewebsolver' ),
								$args['name'],
								$e->getMessage(),
								esc_url( admin_url( 'plugin-install.php' ) )
							)
						);
					}

					// Discard feedback.
					ob_end_clean();
				}

				wp_clean_plugins_cache();

				// Activate the plugin.
				if ( $maybe_activate && $activate ) {
					try {
						$with_file = $installed ? $installed_plugins[ $plugin_file ] : $plugin_slug . '/' . $plugin_file;

						/**
						 * WPHOOK: Action -> Fires before activating the plugin.
						 *
						 * @param string $with_file The plugin file that is being activate.
						 *
						 * @since 1.0
						 */
						do_action( 'hzfex_before_plugin_activation', $with_file );

						$result = self::maybe_activate_plugin( $with_file );

						if ( is_wp_error( $result ) ) {
							throw new Exception( $result->get_error_message() );
						} else {
							$active = true;
						}
					} catch ( Exception $e ) {
						update_option(
							self::PREFIX . $id . '_install_error',
							sprintf(
								/* translators: 1: plugin name, 2: URL to WP plugin page. */
								__( '%1$s was installed but could not be activated. <a href="%2$s">Please activate it manually by clicking here.</a>', 'thewebsolver' ),
								$args['name'],
								admin_url( 'plugins.php' )
							)
						);
					}
				}
			}

			return $active;
		}

		/**
		 * Get slug from path and associate it with the path.
		 *
		 * @param array  $plugins  Associative array of plugin files to paths.
		 * @param string $basename Plugin relative path. Example: thewebsolver/thewebsolver.php.
		 *
		 * @since 1.0
		 * @static
		 */
		public static function get_plugin_basenames( $plugins, $basename ) {
			$path                 = explode( '/', $basename );
			$filename             = end( $path );
			$plugins[ $filename ] = $basename;
			return $plugins;
		}

		/**
		 * Gets and installs plugin from wordpress.org repository.
		 *
		 * @param string $dirname  The plugin directory name (the plguin's slug in WordPress repository).
		 * @param string $filename The plugin main file name if different than `$dirname`.
		 * @param string $version  The plugin version number to install. Defaults to latest version.
		 * @param bool   $activate Whether to activate plugin after installation or not.
		 *
		 * @return bool|string|WP_Error True on successful installation, WP_Error if not.
		 *
		 * @since 1.0
		 * @static
		 */
		public static function maybe_install_plugin( $dirname, $filename = null, $version = 'latest', $activate = true ) {
			$basename = $dirname . '/' . ( $filename ? $filename : $dirname . '.php' );

			if ( false === self::maybe_plugin_is_installed( $basename ) ) {
				// Include necessary WordPress files for plugin installation.
				include_once ABSPATH . 'wp-admin/includes/file.php';
				include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

				$info     = self::get_plugin_info_from_wp_api( $dirname, 'all' );
				$zip_link = 'latest' === $version ? $info->download_link : $info->versions[ $version ];

				// WP Error if can't get the plugin.
				if ( is_wp_error( $zip_link ) ) {
					return $zip_link;
				}

				$upgrader  = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
				$installed = $upgrader->install( $zip_link );

				if ( is_wp_error( $installed ) ) {
					return $installed;
				} elseif ( ! $installed ) {
					return new WP_Error(
						'hzfex_wp_org_plugin_installation_error',
						/* translators: %s: The plugin directory (slug) name */
						sprintf( __( 'Unable to install %s from wordpress.org.', 'tws-core' ), $dirname )
					);
				}
			}

			// If no activation needed, return after installation.
			if ( false === $activate ) {
				return property_exists( $info, 'name' ) ? $info->name : true;
			}

			$plugin = self::maybe_activate_plugin( $basename );

			if ( is_wp_error( $plugin ) ) {
				return $plugin;
			}

			return property_exists( $info, 'name' ) ? $info->name : true;
		}

		/**
		 * Attempts to activate plugin if not yet active.
		 *
		 * @param string $basename The plugin name. (dirname/main-plugin-name.php).
		 *
		 * @return WP_Error|null WP_Error on invalid file, null on success.
		 *
		 * @since 1.0
		 * @static
		 */
		public static function maybe_activate_plugin( $basename ) {
			// Include necessary plugin file, if required.
			if ( ! function_exists( 'activate_plugins' ) ) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			// Bail if already active.
			if ( self::maybe_plugin_is_active( $basename ) ) {
				return;
			}

			return activate_plugin( $basename );
		}

		/**
		 * Deactivates plugin if is active.
		 *
		 * @param string $basename The plugin name. (dirname/main-plugin-name.php).
		 *
		 * @return bool True if plugin is deactivated, false if not.
		 *
		 * @since 1.0
		 * @static
		 */
		public static function maybe_deactivate_plugin( $basename ) {
			// Include necessary plugin file.
			if ( ! function_exists( 'deactivate_plugins' ) ) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			// Bail if plugin is not active.
			if ( false === self::maybe_plugin_is_active( $basename ) ) {
				return false;
			}

			deactivate_plugins( $basename );

			return true;
		}

		/**
		 * Gets the plugin data.
		 *
		 * @param string $basename The plugin name. (dirname/main-plugin-name.php).
		 *
		 * @return array Plugin data, empty array if plugin not found.
		 *
		 * @since 1.0
		 * @static
		 */
		public static function get_plugin_data( $basename ) {
			// Include necessary plugin file.
			if ( ! function_exists( 'is_plugin_active' ) ) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			return self::maybe_plugin_is_installed( $basename ) ? get_plugin_data( trailingslashit( WP_PLUGIN_DIR ) . $basename ) : array();
		}

		/**
		 * Checks if plugin is active.
		 *
		 * @param string $basename The plugin name. (dirname/main-plugin-name.php).
		 *
		 * @return bool True if is active, false if not.
		 *
		 * @since 1.0
		 * @static
		 */
		public static function maybe_plugin_is_active( $basename ) {
			// Include necessary plugin file.
			if ( ! function_exists( 'is_plugin_active' ) ) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			return is_plugin_active( $basename ) || in_array( $basename, get_option( 'active_plugins' ), true );
		}

		/**
		 * Checks if plugin is installed.
		 *
		 * @param string $basename The plugin name. (dirname/main-plugin-name.php).
		 *
		 * @return bool True if installed, false otherwise.
		 *
		 * @since 1.0
		 * @static
		 */
		public static function maybe_plugin_is_installed( $basename ) {
			return file_exists( trailingslashit( WP_PLUGIN_DIR ) . $basename );
		}

		/**
		 * Gets plugin api object from api.wordpress.org
		 *
		 * @param string $slug  The plugin slug in WordPress plugins repository.
		 * @param mixed  $get   What to retrieve from the API response. Defaults to the response object.
		 *                      Set it to `version` to see list of versions available to download.
		 *                      To know all possible values, debug by setting param value to `all`.
		 * @param string $which The plugin version to download. It will only work if
		 *                      { @param get } is set to `version`. Possible options are:
		 * * `latest` - Gets the plugin's latest version download link. `WP_Error` if WP & PHP requirement not met.
		 * * `all`    - Gets all the plugin version download links in an array.
		 * * `*.*.*`  - Gets the given plugin version number. `WP_Error` if given version number doesn't exist.
		 *
		 * @return string|int|float|object|array|false|WP_Error The returned type, WP_Error if anything fails.
		 *
		 * @see  https://developer.wordpress.org/reference/functions/plugins_api/
		 * @link https://codex.wordpress.org/WordPress.org_API
		 *
		 * @since 1.0
		 * @static
		 */
		public static function get_plugin_info_from_wp_api( $slug, $get = 'all', $which = 'latest' ) {
			// Include file that contains the Plugins API.
			include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

			// Get remote response from the Plugins API.
			$response = plugins_api( 'plugin_information', array( 'slug' => $slug ) );

			// Bail with error message if can't get the plugin.
			if ( is_wp_error( $response ) ) {
				return new WP_Error(
					'hzfex_wp_org_plugin_api_error',
					/* translators: %s: The plugin directory (slug) name. */
					sprintf( __( 'Unable to fetch plugin information from wordpress.org for %s.', 'tws-core' ), $slug )
				);
			}

			// Object casting for response, if is array.
			$response      = (object) $response;
			$requires_wp   = $response->requires;
			$requires_php  = $response->requires_php;
			$check         = new stdClass();
			$installed_wp  = __( 'The installed version of WordPress is', 'thewebsolver' );
			$installed_php = __( 'The installed version of PHP is', 'thewebsolver' );
			$min_required  = __( 'The minimum version required is', 'thewebsolver' );
			$wp_msg        = sprintf( '%1$s <b>%2$s</b>. %3$s <b>%4$s</b>.', $installed_wp, get_bloginfo( 'version' ), $min_required, $requires_wp );
			$php_msg       = sprintf( '%1$s <b>%2$s</b>. %3$s <b>%4$s</b>.', $installed_php, PHP_VERSION, $min_required, $requires_php );
			$name          = $response->name;

			/**
			 * Compatibility check can only happen for:
			 * - $get => `download_link`, or
			 * - $get => `version` & $which => `latest`
			 *
			 * This is because `$requires_wp` & `$requires_php` is
			 * extracted from plugin's latest zip file only.
			 */
			if (
				version_compare( PHP_VERSION, $requires_php, '<' ) &&
				version_compare( get_bloginfo( 'version' ), $requires_wp, '<' )
			) {
				$check = new WP_Error(
					'hzfex_wp_org_plugin_wordpress_php_not_compatible',
					/* translators: %s: The plugin directory (slug) name. */
					sprintf(
						'%1$s %2$s %3$s %4$s',
						__( 'Currently installed WordPress and PHP version does not meet the minimum requirement for', 'thewebsolver' ),
						$slug,
						$wp_msg,
						$php_msg
					)
				);
			} elseif ( version_compare( get_bloginfo( 'version' ), $requires_wp, '<' ) ) {
				$check = new WP_Error(
					'hzfex_wp_org_plugin_wordpress_not_compatible',
					/* translators: 1: Requirement not met msg 2: The plugin directory (slug) name 3: WP versions message */
					sprintf(
						'%1$s <b>%2$s</b>. %3$s',
						__( 'Currently installed WordPress version does not meet the minimum requirement for', 'thewebsolver' ),
						$name,
						$wp_msg
					)
				);
			} elseif ( version_compare( PHP_VERSION, $requires_php, '<' ) ) {
				$check = new WP_Error(
					'hzfex_wp_org_plugin_php_not_compatible',
					/* translators: 1: Requirement not met msg 2: The plugin directory (slug) name 3: PHP version message */
					sprintf(
						'%1$s %2$s %3$s',
						__( 'Currently installed PHP version does not meet the minimum requirement for', 'thewebsolver' ),
						$name,
						$php_msg
					)
				);
			}

			if ( 'all' === $get ) {
				// Send the response object.
				return $response;
			} elseif ( 'download_link' === $get ) {
				// Send WP_Error if download link and lastest version isn't compatible with WordPress or PHP.
				if ( is_wp_error( $check ) ) {
					return new WP_Error( 'hzfex_wp_org_plugin_latest_version_not_compatible', $check->get_error_message() );
				} else {
					return $response->download_link;
				}
			} elseif ( 'version' !== $get ) {
				// Send property value if exits, else false.
				return property_exists( $response, $get ) ? $response->{$get} : false;
			}

			// Get download links for all versions of the plugin.
			$all_versions = $response->versions;

			if ( 'latest' === $which ) {
				// Send WP_Error if version check failed while getting the latest version.
				if ( is_wp_error( $check ) ) {
					return $check;
				} else {
					// Send lastest plugin download link if not.
					return $response->download_link;
				}
			} elseif ( 'all' === $which ) {
				// Send an array of all plugin versions.
				return $all_versions;
			}

			// Send WP_Error if given version number not found.
			if ( ! is_array( $all_versions ) || ! isset( $all_versions[ $which ] ) ) {
				return new WP_Error(
					'hzfex_wp_org_plugin_version_not_found',
					/* translators: %s: The plugin directory (slug) name. */
					sprintf( __( 'The version number provided could not be found for %s.', 'thewebsolver' ), $name )
				);
			}

			// Finally send the download link for the given version number (cant do compatibility check here).
			return $all_versions[ $which ];
		}

		/**
		 * Gets the current plugin directory path.
		 *
		 * @return string The current plugin directory path without ending slashes.
		 *
		 * @since 1.0
		 * @static
		 */
		public static function get_plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}

		/**
		 * Gets the default template path.
		 *
		 * @return string
		 *
		 * @since 1.0
		 * @static
		 */
		public static function get_template_path() {
			return apply_filters( 'hzfex_default_tempate_path', 'thewebsolver/' );
		}

		/**
		 * Gets the directory URL of a given file.
		 *
		 * @param string $file   Typically passed as `__FILE__`.
		 * @param bool   $levels The number of parent directories to go up.
		 *                       If `0`, it will get URL to `$file` directory.
		 *                       If `greater than 0`, it will get URL to `$file` directory's parent directory with given level up.
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		public static function get_plugin_dir_url( $file, $levels = 0 ) {
			$path = 0 === $levels ? $file : dirname( $file, absint( $levels ) );
			return trailingslashit( plugins_url( '', $path ) );
		}
	} // Class end.
}
