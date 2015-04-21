<?php

class WPMDB_Base {
	protected $settings;
	protected $plugin_file_path;
	protected $plugin_dir_path;
	protected $plugin_slug;
	protected $plugin_folder_name;
	protected $plugin_basename;
	protected $plugin_base;
	protected $plugin_version;
	protected $template_dir;
	protected $plugin_title;
	protected $dbrains_api_url;
	protected $transient_timeout;
	protected $transient_retry_timeout;
	protected $dbrains_api_base = 'https://deliciousbrains.com';
	protected $dbrains_api_status_url = 'http://s3.amazonaws.com/cdn.deliciousbrains.com/status.json';
	protected $multipart_boundary = 'bWH4JVmYCnf6GfXacrcc';
	protected $attempting_to_connect_to;
	protected $error;
	protected $temp_prefix = '_mig_';
	protected $invalid_content_verification_error;
	protected $addons;
	protected $doing_cli_migration = false;
	protected $is_pro = false;
	protected $is_addon = false;
	protected $core_slug;
	protected $error_log;
	protected $post_data;

	function __construct( $plugin_file_path ) {
		$this->load_settings();
		$this->maybe_schema_update();

		$this->plugin_file_path   = $plugin_file_path;
		$this->plugin_dir_path    = plugin_dir_path( $plugin_file_path );
		$this->plugin_folder_name = basename( $this->plugin_dir_path );
		$this->plugin_basename    = plugin_basename( $plugin_file_path );
		$this->template_dir       = $this->plugin_dir_path . 'template' . DIRECTORY_SEPARATOR;
		$this->plugin_title       = ucwords( str_ireplace( '-', ' ', basename( $plugin_file_path ) ) );
		$this->plugin_title       = str_ireplace( array( 'db', 'wp', '.php' ), array( 'DB', 'WP', '' ), $this->plugin_title );

		// We need to set $this->plugin_slug here because it was set here
		// in Media Files prior to version 1.1.2. If we remove it the customer
		// cannot upgrade, view release notes, etc
		// used almost exclusively as a identifier for plugin version checking (both core and addons)
		$this->plugin_slug = basename( $plugin_file_path, '.php' );

		// used to add admin menus and to identify the core version in the $GLOBALS['wpmdb_meta'] variable for delicious brains api calls, version checking etc
		$this->core_slug = ( $this->is_pro || $this->is_addon ) ? 'wp-migrate-db-pro' : 'wp-migrate-db';

		if ( is_multisite() ) {
			$this->plugin_base = 'settings.php?page=' . $this->core_slug;
		} else {
			$this->plugin_base = 'tools.php?page=' . $this->core_slug;
		}

		if ( $this->is_addon || $this->is_pro ) {
			$this->pro_addon_construct();
		}

		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
	}

	/**
	 * Sets $this->post_data from $_POST, potentially un-slashed.
	 */
	function set_post_data() {
		if ( defined( 'DOING_WPMDB_TESTS' ) || $this->doing_cli_migration ) {
			$this->post_data = $_POST;
		} elseif ( is_null( $this->post_data ) ) {
			$this->post_data = wp_unslash( $_POST );
		}
	}

	function load_plugin_textdomain() {
		load_plugin_textdomain( 'wp-migrate-db', false, dirname( plugin_basename( $this->plugin_file_path ) ) . '/languages/' );
	}

	function pro_addon_construct() {
		$this->addons = array(
			'wp-migrate-db-pro-media-files/wp-migrate-db-pro-media-files.php' => array(
				'name'             => 'Media Files',
				'required_version' => '1.3.1',
			),
			'wp-migrate-db-pro-cli/wp-migrate-db-pro-cli.php'                 => array(
				'name'             => 'CLI',
				'required_version' => '1.1',
			)
		);

		$this->invalid_content_verification_error = __( 'Invalid content verification signature, please verify the connection information on the remote site and try again.', 'wp-migrate-db' );

		$this->transient_timeout       = 60 * 60 * 12;
		$this->transient_retry_timeout = 60 * 60 * 2;

		if ( defined( 'DBRAINS_API_BASE' ) ) {
			$this->dbrains_api_base = DBRAINS_API_BASE;
		}

		if ( $this->open_ssl_enabled() == false ) {
			$this->dbrains_api_base = str_replace( 'https://', 'http://', $this->dbrains_api_base );
		}

		$this->dbrains_api_url = $this->dbrains_api_base . '/?wc-api=delicious-brains';

		// allow developers to change the temporary prefix applied to the tables
		$this->temp_prefix = apply_filters( 'wpmdb_temporary_prefix', $this->temp_prefix );

		// Seen when the user clicks "view details" on the plugin listing page
		add_action( 'install_plugins_pre_plugin-information', array( $this, 'plugin_update_popup' ) );

		// Add an extra row to the plugin screen
		add_action( 'after_plugin_row_' . $this->plugin_basename, array( $this, 'plugin_row' ), 11 );

		// Adds a custom error message to the plugin install page if required (licence expired / invalid)
		add_filter( 'http_response', array( $this, 'verify_download' ), 10, 3 );

		add_action( 'wpmdb_notices', array( $this, 'version_update_notice' ) );
	}

	/**
	 * Loads the settings into the settings class property, sets some defaults if no existing settings are found.
	 */
	function load_settings() {
		if ( ! is_null( $this->settings ) ) {
			return;
		}

		$update_settings = false;
		$this->settings  = get_site_option( 'wpmdb_settings' );

		/*
		 * Settings were previously stored and retrieved using get_option and update_option respectively.
		 * Here we update the subsite option to a network wide option if applicable.
		 */
		if ( false === $this->settings && is_multisite() && is_network_admin() ) {
			$this->settings = get_option( 'wpmdb_settings' );
			if ( false !== $this->settings ) {
				$update_settings = true;
				delete_option( 'wpmdb_settings' );
			}
		}

		$default_settings = array(
			'key'               => $this->generate_key(),
			'allow_pull'        => false,
			'allow_push'        => false,
			'profiles'          => array(),
			'licence'           => '',
			'verify_ssl'        => false,
			'blacklist_plugins' => array(),
			'max_request'       => min( 1024 * 1024, $this->get_bottleneck( 'max' ) ),
		);

		// if we still don't have settings exist this must be a fresh install, set up some default settings
		if ( false === $this->settings ) {
			$this->settings  = $default_settings;
			$update_settings = true;
		} else {
			/*
			 * When new settings are added an existing customer's db won't have the new settings.
			 * They're added here to circumvent array index errors in debug mode.
			 */
			foreach ( $default_settings as $key => $value ) {
				if ( ! isset( $this->settings[ $key ] ) ) {
					$this->settings[ $key ] = $value;
					$update_settings        = true;
				}
			}
		}

		if ( $update_settings ) {
			update_site_option( 'wpmdb_settings', $this->settings );
		}
	}

	/**
	 * Loads the error log into the error log class property.
	 */
	function load_error_log() {
		if ( ! is_null( $this->error_log ) ) {
			return;
		}

		$this->error_log = get_site_option( 'wpmdb_error_log' );

		/*
		 * The error log was previously stored and retrieved using get_option and update_option respectively.
		 * Here we update the subsite option to a network wide option if applicable.
		 */
		if ( false === $this->error_log && is_multisite() && is_network_admin() ) {
			$this->error_log = get_option( 'wpmdb_error_log' );
			if ( false !== $this->error_log ) {
				update_site_option( 'wpmdb_error_log', $this->error_log );
				delete_option( 'wpmdb_error_log' );
			}
		}
	}

	function template( $template, $dir = '', $args = array() ) {
		global $wpdb;
		// TODO: Refactor to remove extract().
		extract( $args, EXTR_OVERWRITE );
		$dir = ( ! empty( $dir ) ) ? trailingslashit( $dir ) : $dir;
		include $this->template_dir . $dir . $template . '.php';
	}

	function open_ssl_enabled() {
		if ( defined( 'OPENSSL_VERSION_TEXT' ) ) {
			return true;
		} else {
			return false;
		}
	}

	function set_time_limit() {
		if ( ! function_exists( 'ini_get' ) || ! ini_get( 'safe_mode' ) ) {
			@set_time_limit( 0 );
		}
	}

	function remote_post( $url, $data, $scope, $args = array(), $expecting_serial = false ) {
		$this->set_time_limit();
		$this->set_post_data();

		if ( function_exists( 'fsockopen' ) && strpos( $url, 'https://' ) === 0 && $scope == 'ajax_verify_connection_to_remote_site' ) {
			$url_parts = $this->parse_url( $url );
			$host      = $url_parts['host'];
			if ( $pf = @fsockopen( $host, 443, $err, $err_string, 1 ) ) {
				// worked
				fclose( $pf );
			} else {
				// failed
				$url = substr_replace( $url, 'http', 0, 5 );
			}
		}

		$sslverify = ( $this->settings['verify_ssl'] == 1 ? true : false );

		$default_remote_post_timeout = apply_filters( 'wpmdb_default_remote_post_timeout', 60 * 20 );

		$args = wp_parse_args( $args,
			array(
				'timeout'   => $default_remote_post_timeout,
				'blocking'  => true,
				'sslverify' => $sslverify,
			) );

		$args['method'] = 'POST';

		if ( ! isset( $args['body'] ) ) {
			$args['body'] = $this->array_to_multipart( $data );
		}

		$args['headers']['Content-Type'] = 'multipart/form-data; boundary=' . $this->multipart_boundary;
		$args['headers']['Referer']      = $this->referer_from_url( $url );

		$this->attempting_to_connect_to = $url;

		$response = wp_remote_post( $url, $args );

		if ( ! is_wp_error( $response ) ) {
			$response['body'] = trim( $response['body'], "\xef\xbb\xbf" );
		}

		if ( is_wp_error( $response ) ) {
			if ( strpos( $url, 'https://' ) === 0 && $scope == 'ajax_verify_connection_to_remote_site' ) {
				return $this->retry_remote_post( $url, $data, $scope, $args, $expecting_serial );
			} elseif ( isset( $response->errors['http_request_failed'][0] ) && strstr( $response->errors['http_request_failed'][0], 'timed out' ) ) {
				$this->error = sprintf( __( 'The connection to the remote server has timed out, no changes have been committed. (#134 - scope: %s)', 'wp-migrate-db' ), $scope );
			} elseif ( isset( $response->errors['http_request_failed'][0] ) && ( strstr( $response->errors['http_request_failed'][0], 'Could not resolve host' ) || strstr( $response->errors['http_request_failed'][0], "couldn't connect to host" ) ) ) {
				$this->error = sprintf( __( 'We could not find: %s. Are you sure this is the correct URL?', 'wp-migrate-db' ), $this->post_data['url'] );
				$url_bits = $this->parse_url( $this->post_data['url'] );
				if ( strstr( $this->post_data['url'], 'dev.' ) || strstr( $this->post_data['url'], '.dev' ) || ! strstr( $url_bits['host'], '.' ) ) {
					$this->error .= '<br />';
					if ( $this->post_data['intent'] == 'pull' ) {
						$this->error .= __( 'It appears that you might be trying to pull from a local environment. This will not work if <u>this</u> website happens to be located on a remote server, it would be impossible for this server to contact your local environment.', 'wp-migrate-db' );
					} else {
						$this->error .= __( 'It appears that you might be trying to push to a local environment. This will not work if <u>this</u> website happens to be located on a remote server, it would be impossible for this server to contact your local environment.', 'wp-migrate-db' );
					}
				}
			} else {
				$this->error = sprintf( __( 'The connection failed, an unexpected error occurred, please contact support. (#121 - scope: %s)', 'wp-migrate-db' ), $scope );
			}
			$this->log_error( $this->error, $response );

			return false;
		} elseif ( (int) $response['response']['code'] < 200 || (int) $response['response']['code'] > 399 ) {
			if ( strpos( $url, 'https://' ) === 0 && $scope == 'ajax_verify_connection_to_remote_site' ) {
				return $this->retry_remote_post( $url, $data, $scope, $args, $expecting_serial );
			} elseif ( $response['response']['code'] == '401' ) {
				$this->error = __( 'The remote site is protected with Basic Authentication. Please enter the username and password above to continue. (401 Unauthorized)', 'wp-migrate-db' );
				$this->log_error( $this->error, $response );

				return false;
			} else {
				$this->error = sprintf( __( 'Unable to connect to the remote server, please check the connection details - %1$s %2$s (#129 - scope: %3$s)', 'wp-migrate-db' ), $response['response']['code'], $response['response']['message'], $scope );
				$this->log_error( $this->error, $response );

				return false;
			}
		} elseif ( $expecting_serial && is_serialized( $response['body'] ) == false ) {
			if ( strpos( $url, 'https://' ) === 0 && $scope == 'ajax_verify_connection_to_remote_site' ) {
				return $this->retry_remote_post( $url, $data, $scope, $args, $expecting_serial );
			}
			$this->error = __( 'There was a problem with the AJAX request, we were expecting a serialized response, instead we received:<br />', 'wp-migrate-db' ) . esc_html( $response['body'] );
			$this->log_error( $this->error, $response );

			return false;
		} elseif ( $response['body'] === '0' ) {
			if ( strpos( $url, 'https://' ) === 0 && $scope == 'ajax_verify_connection_to_remote_site' ) {
				return $this->retry_remote_post( $url, $data, $scope, $args, $expecting_serial );
			}
			$this->error = sprintf( __( 'WP Migrate DB Pro does not seem to be installed or active on the remote site. (#131 - scope: %s)', 'wp-migrate-db' ), $scope );
			$this->log_error( $this->error, $response );

			return false;
		} elseif ( $expecting_serial && is_serialized( $response['body'] ) == true && $scope == 'ajax_verify_connection_to_remote_site' ) {
			$unserialized_response = unserialize( $response['body'] );
			if ( isset( $unserialized_response['error'] ) && '1' == $unserialized_response['error'] && strpos( $url, 'https://' ) === 0 ) {
				return $this->retry_remote_post( $url, $data, $scope, $args, $expecting_serial );
			}
		}

		return $response['body'];
	}

	function retry_remote_post( $url, $data, $scope, $args = array(), $expecting_serial = false ) {
		$url = substr_replace( $url, 'http', 0, 5 );
		if ( $response = $this->remote_post( $url, $data, $scope, $args, $expecting_serial ) ) {
			return $response;
		}

		return false;
	}

	function array_to_multipart( $data ) {
		if ( ! $data || ! is_array( $data ) ) {
			return $data;
		}

		$result = '';

		foreach ( $data as $key => $value ) {
			$result .= '--' . $this->multipart_boundary . "\r\n" . sprintf( 'Content-Disposition: form-data; name="%s"', $key );

			if ( 'chunk' == $key ) {
				if ( $data['chunk_gzipped'] ) {
					$result .= "; filename=\"chunk.txt.gz\"\r\nContent-Type: application/x-gzip";
				} else {
					$result .= "; filename=\"chunk.txt\"\r\nContent-Type: text/plain;";
				}
			} else {
				$result .= "\r\nContent-Type: text/plain; charset=" . get_option( 'blog_charset' );
			}

			$result .= "\r\n\r\n" . $value . "\r\n";
		}

		$result .= '--' . $this->multipart_boundary . "--\r\n";

		return $result;
	}

	function file_to_multipart( $file ) {
		$result = '';

		if ( false == file_exists( $file ) ) {
			return false;
		}

		$filetype = wp_check_filetype( $file );
		$contents = file_get_contents( $file );

		$result .= '--' . $this->multipart_boundary . "\r\n" . sprintf( 'Content-Disposition: form-data; name="media[]"; filename="%s"', basename( $file ) );
		$result .= sprintf( "\r\nContent-Type: %s", $filetype['type'] );
		$result .= "\r\n\r\n" . $contents . "\r\n";
		$result .= '--' . $this->multipart_boundary . "--\r\n";

		return $result;
	}

	function log_error( $wpmdb_error, $additional_error_var = false ) {
		$error_header = "********************************************\n******  Log date: " . date( 'Y/m/d H:i:s' ) . " ******\n********************************************\n\n";
		$error        = $error_header . 'WPMDB Error: ' . $wpmdb_error . "\n\n";

		if ( ! empty( $this->attempting_to_connect_to ) ) {
			$error .= 'Attempted to connect to: ' . $this->attempting_to_connect_to . "\n\n";
		}

		if ( $additional_error_var !== false ) {
			$error .= print_r( $additional_error_var, true ) . "\n\n";
		}

		$this->load_error_log();

		if ( isset( $this->error_log ) ) {
			$this->error_log .= $error;
		} else {
			$this->error_log = $error;
		}

		update_site_option( 'wpmdb_error_log', $this->error_log );
	}

	function display_errors() {
		if ( ! empty( $this->error ) ) {
			echo $this->error;
			$this->error = '';

			return true;
		}

		return false;
	}

	function filter_post_elements( $post_array, $accepted_elements ) {
		$accepted_elements[] = 'sig';

		return array_intersect_key( $post_array, array_flip( $accepted_elements ) );
	}

	function sanitize_signature_data( $value ) {
		if ( is_bool( $value ) ) {
			$value = $value ? 'true' : 'false';
		}

		return $value;
	}

	function create_signature( $data, $key ) {
		if ( isset( $data['sig'] ) ) {
			unset( $data['sig'] );
		}
		$data      = array_map( array( $this, 'sanitize_signature_data' ), $data );
		$flat_data = implode( '', $data );

		return base64_encode( hash_hmac( 'sha1', $flat_data, $key, true ) );
	}

	function verify_signature( $data, $key ) {
		if ( empty( $data['sig'] ) ) {
			return false;
		}

		if ( isset( $data['nonce'] ) ) {
			unset( $data['nonce'] );
		}

		$temp               = $data;
		$computed_signature = $this->create_signature( $temp, $key );

		return $computed_signature === $data['sig'];
	}

	function get_dbrains_api_url( $request, $args = array() ) {
		$url             = $this->dbrains_api_url;
		$args['request'] = $request;
		$args['version'] = $GLOBALS['wpmdb_meta'][ $this->core_slug ]['version'];
		$url             = add_query_arg( $args, $url );
		if ( false !== get_site_transient( 'wpmdb_temporarily_disable_ssl' ) && 0 === strpos( $this->dbrains_api_url, 'https://' ) ) {
			$url = substr_replace( $url, 'http', 0, 5 );
		}

		$url .= '&locale=' . urlencode( get_locale() );

		return $url;
	}

	/**
	 * Main function for communicating with the Delicious Brains API.
	 *
	 * @param string $request
	 * @param array $args
	 *
	 * @return mixed
	 */
	function dbrains_api_request( $request, $args = array() ) {
		$trans = get_site_transient( 'wpmdb_dbrains_api_down' );

		if ( false !== $trans ) {
			$api_down_message = sprintf( '<div class="updated warning inline-message">%s</div>', $trans );

			return json_encode( array( 'dbrains_api_down' => $api_down_message ) );
		}

		$sslverify = ( $this->settings['verify_ssl'] == 1 ? true : false );

		$url      = $this->get_dbrains_api_url( $request, $args );
		$response = wp_remote_get(
			$url,
			array(
				'timeout'   => 30,
				'blocking'  => true,
				'sslverify' => $sslverify,
			)
		);

		if ( is_wp_error( $response ) || (int) $response['response']['code'] < 200 || (int) $response['response']['code'] > 399 ) {
			$this->log_error( print_r( $response, true ) );

			if ( true === $this->dbrains_api_down() ) {
				$trans = get_site_transient( 'wpmdb_dbrains_api_down' );

				if ( false !== $trans ) {
					$api_down_message = sprintf( '<div class="updated warning inline-message">%s</div>', $trans );

					return json_encode( array( 'dbrains_api_down' => $api_down_message ) );
				}
			}

			$disable_ssl_url           = network_admin_url( $this->plugin_base . '&nonce=' . wp_create_nonce( 'wpmdb-disable-ssl' ) . '&wpmdb-disable-ssl=1' );
			$connection_failed_message = '<div class="updated warning inline-message">';
			$connection_failed_message .= sprintf( __( '<strong>Could not connect to deliciousbrains.com</strong> &mdash; You will not receive update notifications or be able to activate your license until this is fixed. This issue is often caused by an improperly configured SSL server (https). We recommend <a href="%1$s" target="_blank">fixing the SSL configuration on your server</a>, but if you need a quick fix you can:%2$s', 'wp-migrate-db' ), 'https://deliciousbrains.com/wp-migrate-db-pro/doc/could-not-connect-deliciousbrains-com/', sprintf( '<p><a href="%1$s" class="temporarily-disable-ssl button">%2$s</a></p>', $disable_ssl_url, __(  'Temporarily disable SSL for connections to deliciousbrains.com', 'wp-migrate-db' ) ) );
			$connection_failed_message .= '</div>';

			return json_encode( array( 'errors' => array( 'connection_failed' => $connection_failed_message ) ) );
		}

		return $response['body'];
	}

	/**
	 * Is the Delicious Brains API down?
	 *
	 * If not available then a 'wpmdb_dbrains_api_down' transient will be set with an appropriate message.
	 *
	 * @return bool
	 */
	function dbrains_api_down() {
		if ( false !== get_site_transient( 'wpmdb_dbrains_api_down' ) ) {
			return true;
		}

		$response = wp_remote_get( $this->dbrains_api_status_url, array( 'timeout' => 30 ) );

		// Can't get to api status url so fall back to normal failure handling.
		if ( is_wp_error( $response ) || 200 != (int) $response['response']['code'] || empty( $response['body'] ) ) {
			return false;
		}

		$json = json_decode( $response['body'], true );

		// Can't decode json so fall back to normal failure handling.
		if ( ! $json ) {
			return false;
		}

		// JSON doesn't seem to have the format we expect or is not down, so fall back to normal failure handling.
		if ( ! isset( $json['api']['status'] ) || 'down' != $json['api']['status'] ) {
			return false;
		}

		$message = __( "<strong>Delicious Brains API is Down — </strong>Unfortunately we're experiencing some problems with our server.", 'wp-migrate-db' );

		if ( ! empty( $json['api']['updated'] ) ) {
			$updated     = $json['api']['updated'];
			$updated_ago = sprintf( _x( '%s ago', 'ex. 2 hours ago', 'wp-migrate-db' ), human_time_diff( strtotime( $updated ) ) );
		}

		if ( ! empty( $json['api']['message'] ) ) {
			$message .= '<br />';
			$message .= __( "Here's the most recent update on its status", 'wp-migrate-db' );
			if ( ! empty( $updated_ago ) ) {
				$message .= ' (' . $updated_ago . ')';
			}
			$message .= ': <em>' . $json['api']['message'] . '</em>';
		}

		set_site_transient( 'wpmdb_dbrains_api_down', $message, $this->transient_retry_timeout );

		return true;
	}

	function plugin_update_popup() {
		if ( $this->plugin_slug != $_GET['plugin'] ) {
			return;
		}

		$filename       = $this->plugin_slug;
		$latest_version = $this->get_latest_version( $this->plugin_slug );

		if ( $this->is_beta_version( $latest_version ) ) {
			$filename .= '-beta';
		}

		$url  = $this->dbrains_api_base . '/content/themes/delicious-brains/update-popup/' . $filename . '.html';
		$data = wp_remote_get( $url, array( 'timeout' => 30 ) );

		if ( is_wp_error( $data ) || 200 != $data['response']['code'] ) {
			echo '<p>' . __( 'Could not retrieve version details. Please try again.', 'wp-migrate-db' ) . '</p>';
		} else {
			echo $data['body'];
		}

		exit;
	}

	/*
	* Shows a message below the plugin on the plugins page when:
	*	1. the license hasn't been activated
	*	2. when there's an update available but the license is expired
	*
	* TODO: Implement "Check my license again" link
	*/
	function plugin_row() {
		$licence          = $this->get_licence_key();
		$licence_response = $this->is_licence_expired();
		$licence_problem  = isset( $licence_response['errors'] );

		if ( ! isset( $GLOBALS['wpmdb_meta'][ $this->plugin_slug ]['version'] ) ) {
			$installed_version = '0';
		} else {
			$installed_version = $GLOBALS['wpmdb_meta'][ $this->plugin_slug ]['version'];
		}

		$latest_version = $this->get_latest_version( $this->plugin_slug );

		$new_version = '';
		if ( version_compare( $installed_version, $latest_version, '<' ) ) {
			$new_version = sprintf( __( 'There is a new version of %s available.', 'wp-migrate-db' ), $this->plugin_title );
			$new_version .= ' <a class="thickbox" title="' . $this->plugin_title . '" href="plugin-install.php?tab=plugin-information&plugin=' . rawurlencode( $this->plugin_slug ) . '&TB_iframe=true&width=640&height=808">';
			$new_version .= sprintf( __( 'View version %s details', 'wp-migrate-db' ), $latest_version ) . '</a>.';
		}

		if ( ! $new_version && ! empty( $licence ) ) {
			return;
		}

		if ( empty( $licence ) ) {
			$settings_link = sprintf( '<a href="%s">%s</a>',
				network_admin_url( $this->plugin_base ) . '#settings',
				_x( 'Settings', 'Plugin configuration and preferences', 'wp-migrate-db' ) );
			if ( $new_version ) {
				$message = sprintf( __( 'To update, go to %1$s and enter your license key. If you don\'t have a license key, you may <a href="%2$s">purchase one</a>.', 'wp-migrate-db' ), $settings_link, 'http://deliciousbrains.com/wp-migrate-db-pro/pricing/' );
			} else {
				$message = sprintf( __( 'To finish activating %1$s, please go to %2$s and enter your license key. If you don\'t have a license key, you may <a href="%3$s">purchase one</a>.', 'wp-migrate-db' ), $this->plugin_title, $settings_link, 'http://deliciousbrains.com/wp-migrate-db-pro/pricing/' );
			}
		} elseif ( $licence_problem ) {
			$message = array_shift( $licence_response['errors'] ) . sprintf( ' <a href="#" class="check-my-licence-again">%s</a>',
					__( 'Check my license again', 'wp-migrate-db' ) );
		} else {
			return;
		} ?>

		<tr class="plugin-update-tr wpmdbpro-custom">
			<td colspan="3" class="plugin-update">
				<div class="update-message"><span class="wpmdb-new-version-notice"><?php echo esc_html( $new_version ); ?></span> <span class="wpmdb-licence-error-notice"><?php echo $message; ?></span></div>
			</td>
		</tr>

		<?php if ( $new_version ) { // removes the built-in plugin update message ?>
			<script type="text/javascript">
				(function ($) {
					var wpmdb_row = jQuery('#<?php echo $this->plugin_slug; ?>'),
						next_row = wpmdb_row.next();

					// If there's a plugin update row - need to keep the original update row available so we can switch it out
					// if the user has a successful response from the 'check my license again' link
					if (next_row.hasClass('plugin-update-tr') && !next_row.hasClass('wpmdbpro-custom')) {
						var original = next_row.clone();
						original.add
						next_row.html(next_row.next().html()).addClass('wpmdbpro-custom-visible');
						next_row.next().remove();
						next_row.after(original);
						original.addClass('wpmdb-original-update-row');
					}
				})(jQuery);
			</script>
		<?php
		}
	}

	function verify_download( $response, $args, $url ) {
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$download_url = $this->get_plugin_update_download_url( $this->plugin_slug );

		if ( 0 === strpos( $url, $download_url ) || 402 != $response['response']['code'] ) {
			return $response;
		}

		// The $response['body'] is blank but output is actually saved to a file in this case
		$data = @file_get_contents( $response['filename'] );

		if ( ! $data ) {
			return new WP_Error( 'wpmdbpro_download_error_empty', sprintf( __( 'Error retrieving download from deliciousbrain.com. Please try again or download manually from <a href="%1$s">%2$s</a>.', 'wp-migrate-db' ), 'https://deliciousbrains.com/my-account/', _x( 'My Account', 'Delicious Brains account', 'wp-migrate-db' ) ) );
		}

		$decoded_data = json_decode( $data, true );

		// Can't decode the JSON errors, so just barf it all out
		if ( ! isset( $decoded_data['errors'] ) || ! $decoded_data['errors'] ) {
			return new WP_Error( 'wpmdbpro_download_error_raw', $data );
		}

		foreach ( $decoded_data['errors'] as $key => $msg ) {
			return new WP_Error( 'wpmdbpro_' . $key, $msg );
		}
	}

	function is_licence_constant() {
		return defined( 'WPMDB_LICENCE' );
	}

	function get_licence_key() {
		return $this->is_licence_constant() ? WPMDB_LICENCE : $this->settings['licence'];
	}

	/**
	 * Sets the licence index in the $settings array class property and updates the wpmdb_settings option.
	 *
	 * @param string $key
	 */
	function set_licence_key( $key ) {
		$this->settings['licence'] = $key;
		update_site_option( 'wpmdb_settings', $this->settings );
	}

	/**
	 * Checks whether the saved licence has expired or not.
	 *
	 * @param bool $skip_transient_check
	 *
	 * @return bool
	 */
	function is_valid_licence( $skip_transient_check = false ) {
		$response = $this->is_licence_expired( $skip_transient_check );

		if ( isset( $response['dbrains_api_down'] ) ) {
			return true;
		}

		// Don't cripple the plugin's functionality if the user's licence is expired
		if ( isset( $response['errors']['subscription_expired'] ) && 1 === count( $response['errors'] ) ) {
			return true;
		}

		return ( isset( $response['errors'] ) ) ? false : true;
	}

	function is_licence_expired( $skip_transient_check = false ) {
		$licence = $this->get_licence_key();

		if ( empty( $licence ) ) {
			$settings_link = sprintf( '<a href="%s">%s</a>', network_admin_url( $this->plugin_base ) . '#settings', _x( 'Settings', 'Plugin configuration and preferences', 'wp-migrate-db' ) );
			$message = sprintf( __( 'To finish activating WP Migrate DB Pro, please go to %1$s and enter your license key. If you don\'t have a license key, you may <a href="%2$s">purchase one</a>.', 'wp-migrate-db' ), $settings_link, 'http://deliciousbrains.com/wp-migrate-db-pro/pricing/' );
			return array( 'errors' => array( 'no_licence' => $message ) );
		}

		if ( ! $skip_transient_check ) {
			$trans = get_site_transient( 'wpmdb_licence_response' );
			if ( false !== $trans ) {
				return json_decode( $trans, true );
			}
		}

		return json_decode( $this->check_licence( $licence ), true );
	}

	function check_licence( $licence_key ) {
		if ( empty( $licence_key ) ) {
			return false;
		}

		$args = array(
			'licence_key' => $licence_key,
			'site_url'    => home_url( '', 'http' ),
		);

		$response = $this->dbrains_api_request( 'check_support_access', $args );

		set_site_transient( 'wpmdb_licence_response', $response, $this->transient_timeout );

		return $response;
	}

	function is_beta_version( $ver ) {
		if ( preg_match( '@b[0-9]+$@', $ver ) ) {
			return true;
		}

		return false;
	}

	function get_required_version( $slug ) {
		$plugin_file = sprintf( '%1$s/%1$s.php', $slug );

		if ( isset( $this->addons[ $plugin_file ]['required_version'] ) ) {
			return $this->addons[ $plugin_file ]['required_version'];
		} else {
			return 0;
		}
	}

	function get_latest_version( $slug ) {
		$data = $this->get_upgrade_data();

		if ( ! isset( $data[ $slug ] ) ) {
			return false;
		}

		// If pre-1.1.2 version of Media Files addon
		if ( ! isset( $GLOBALS['wpmdb_meta'][ $slug ]['version'] ) ) {
			$installed_version = false;
		} else {
			$installed_version = $GLOBALS['wpmdb_meta'][ $slug ]['version'];
		}

		$required_version = $this->get_required_version( $slug );

		// Return the latest beta version if the installed version is beta
		// and the API returned a beta version and it's newer than the latest stable version
		if ( $installed_version
			&& ( $this->is_beta_version( $installed_version ) || $this->is_beta_version( $required_version ) )
			&& isset( $data[ $slug ]['beta_version'] )
			&& version_compare( $data[ $slug ]['version'], $data[ $slug ]['beta_version'], '<' )
		) {
			return $data[ $slug ]['beta_version'];
		}

		return $data[ $slug ]['version'];
	}

	function get_upgrade_data() {
		$info = get_site_transient( 'wpmdb_upgrade_data' );

		if ( isset( $info['version'] ) ) {
			delete_site_transient( 'wpmdb_licence_response' );
			delete_site_transient( 'wpmdb_upgrade_data' );
			$info = false;
		}

		if ( $info ) {
			return $info;
		}

		$data = $this->dbrains_api_request( 'upgrade_data' );

		$data = json_decode( $data, true );

		/*
		We need to set the transient even when there's an error,
		otherwise we'll end up making API requests over and over again
		and slowing things down big time.
		*/
		$default_upgrade_data = array( 'wp-migrate-db-pro' => array( 'version' => $GLOBALS['wpmdb_meta'][ $this->core_slug ]['version'] ) );

		if ( ! $data ) {
			set_site_transient( 'wpmdb_upgrade_data', $default_upgrade_data, $this->transient_retry_timeout );
			$this->log_error( 'Error trying to decode JSON upgrade data.' );

			return false;
		}

		if ( isset( $data['errors'] ) ) {
			set_site_transient( 'wpmdb_upgrade_data', $default_upgrade_data, $this->transient_retry_timeout );
			$this->log_error( 'Error trying to get upgrade data.', $data['errors'] );

			return false;
		}

		set_site_transient( 'wpmdb_upgrade_data', $data, $this->transient_timeout );

		return $data;
	}

	function get_plugin_update_download_url( $plugin_slug, $is_beta = false ) {
		$licence    = $this->get_licence_key();
		$query_args = array(
			'request'     => 'download',
			'licence_key' => $licence,
			'slug'        => $plugin_slug,
			'site_url'    => home_url( '', 'http' ),
		);

		if ( $is_beta ) {
			$query_args['beta'] = '1';
		}

		return add_query_arg( $query_args, $this->dbrains_api_url );
	}

	function diverse_array( $vector ) {
		$result = array();

		foreach ( $vector as $key1 => $value1 ) {
			foreach ( $value1 as $key2 => $value2 ) {
				$result[ $key2 ][ $key1 ] = $value2;
			}
		}

		return $result;
	}

	function set_time_limit_available() {
		if ( ! function_exists( 'set_time_limit' ) || ! function_exists( 'ini_get' ) ) {
			return false;
		}

		$current_max_execution_time  = ini_get( 'max_execution_time' );
		$proposed_max_execution_time = ( $current_max_execution_time == 30 ) ? 31 : 30;
		@set_time_limit( $proposed_max_execution_time );
		$current_max_execution_time = ini_get( 'max_execution_time' );

		return $proposed_max_execution_time == $current_max_execution_time;
	}

	function get_plugin_name( $plugin = false ) {
		if ( ! is_admin() ) {
			return false;
		}

		$plugin_basename = ( false !== $plugin ? $plugin : $this->plugin_basename );

		$plugins = get_plugins();

		if ( ! isset( $plugins[ $plugin_basename ]['Name'] ) ) {
			return false;
		}

		return $plugins[ $plugin_basename ]['Name'];
	}

	function get_class_props() {
		return get_object_vars( $this );
	}

	// Get only the table beginning with our DB prefix or temporary prefix, also skip views
	function get_tables( $scope = 'regular' ) {
		global $wpdb;
		$prefix       = ( $scope == 'temp' ? $this->temp_prefix : $wpdb->prefix );
		$tables       = $wpdb->get_results( 'SHOW FULL TABLES', ARRAY_N );
		$clean_tables = array();

		foreach ( $tables as $table ) {
			if ( ( ( $scope == 'temp' || $scope == 'prefix' ) && 0 !== strpos( $table[0], $prefix ) ) || $table[1] == 'VIEW' ) {
				continue;
			}
			$clean_tables[] = $table[0];
		}

		return apply_filters( 'wpmdb_tables', $clean_tables, $scope );
	}

	function version_update_notice() {
		// We don't want to show both the "Update Required" and "Update Available" messages at the same time
		if ( isset( $this->addons[ $this->plugin_basename ] ) && true == $this->is_addon_outdated( $this->plugin_basename ) ) {
			return;
		}

		// To reduce UI clutter we hide addon update notices if the core plugin has updates available
		if ( isset( $this->addons[ $this->plugin_basename ] ) ) {
			$core_installed_version = $GLOBALS['wpmdb_meta'][ $this->core_slug ]['version'];
			$core_latest_version    = $this->get_latest_version( $this->core_slug );
			// Core update is available, don't show update notices for addons until core is updated
			if ( version_compare( $core_installed_version, $core_latest_version, '<' ) ) {
				return;
			}
		}

		$update_url = wp_nonce_url( network_admin_url( 'update.php?action=upgrade-plugin&plugin=' . urlencode( $this->plugin_basename ) ), 'upgrade-plugin_' . $this->plugin_basename );

		// If pre-1.1.2 version of Media Files addon, don't bother getting the versions
		if ( ! isset( $GLOBALS['wpmdb_meta'][ $this->plugin_slug ]['version'] ) ) {
			?>
			<div style="display: block;" class="updated warning inline-message">
				<strong><?php _ex( 'Update Available', 'A new version of the plugin is available', 'wp-migrate-db' ); ?></strong> &mdash;
				<?php printf( __( 'A new version of %1$s is now available. %2$s', 'wp-migrate-db' ), $this->plugin_title, sprintf( '<a href="%s">%s</a>', $update_url, _x( 'Update Now', 'Download and install a new version of the plugin', 'wp-migrate-db' ) ) ); ?>
			</div>
		<?php
		} else {
			$installed_version = $GLOBALS['wpmdb_meta'][ $this->plugin_slug ]['version'];
			$latest_version    = $this->get_latest_version( $this->plugin_slug );

			if ( version_compare( $installed_version, $latest_version, '<' ) ) { ?>
				<div style="display: block;" class="updated warning inline-message">
					<strong><?php _ex( 'Update Available', 'A new version of the plugin is available', 'wp-migrate-db' ); ?></strong> &mdash;
						<?php printf( __( '%1$s %2$s is now available. You currently have %3$s installed. <a href="%4$s">%5$s</a>', 'wp-migrate-db' ), $this->plugin_title, $latest_version, $installed_version, $update_url, _x( 'Update Now', 'Download and install a new version of the plugin', 'wp-migrate-db' ) ); ?>
				</div>
			<?php
			}
		}
	}

	function plugins_dir() {
		$path = untrailingslashit( $this->plugin_dir_path );

		return substr( $path, 0, strrpos( $path, DIRECTORY_SEPARATOR ) ) . DIRECTORY_SEPARATOR;
	}

	function is_addon_outdated( $addon_basename ) {
		$addon_slug = current( explode( '/', $addon_basename ) );

		// If pre-1.1.2 version of Media Files addon, then it is outdated
		if ( ! isset( $GLOBALS['wpmdb_meta'][ $addon_slug ]['version'] ) ) {
			return true;
		}

		$installed_version = $GLOBALS['wpmdb_meta'][ $addon_slug ]['version'];
		$required_version  = $this->addons[ $addon_basename ]['required_version'];

		return version_compare( $installed_version, $required_version, '<' );
	}

	function get_plugin_file_path() {
		return $this->plugin_file_path;
	}

	/**
	 * Returns a formatted message dependant on the status of the licence.
	 *
	 * @param bool $trans
	 *
	 * @return string|void
	 */
	function get_licence_status_message( $trans = false ) {
		$licence               = $this->get_licence_key();
		$api_response_provided = true;

		if ( empty( $licence ) && ! $trans ) {
			$message = sprintf( __( '<strong>Activate Your License</strong> &mdash; Please <a href="#" class="%s">enter your license key</a> to enable push and pull.', 'wp-migrate-db' ), 'js-action-link enter-licence' );
			return $message;
		}

		if ( ! $trans ) {
			$trans = get_site_transient( 'wpmdb_licence_response' );

			if ( false === $trans ) {
				$trans = $this->check_licence( $licence );
			}

			$trans = json_decode( $trans, true );
			$api_response_provided = false;
		}

		if ( isset( $trans['dbrains_api_down'] ) ) {
			return __( "<strong>We've temporarily activated your license and will complete the activation once the Delicious Brains API is available again.</strong>", 'wp-migrate-db' );
		}

		$errors = $trans['errors'];

		$check_licence_again_url = network_admin_url( $this->plugin_base . '&nonce=' . wp_create_nonce( 'wpmdb-check-licence' ) . '&wpmdb-check-licence=1' );

		if ( isset( $errors['connection_failed'] ) ) {
			$disable_ssl_url = network_admin_url( $this->plugin_base . '&nonce=' . wp_create_nonce( 'wpmdb-disable-ssl' ) . '&wpmdb-disable-ssl=1' );
			$message = sprintf( __( '<strong>Could not connect to deliciousbrains.com</strong> &mdash; You will not receive update notifications or be able to activate your license until this is fixed. This issue is often caused by an improperly configured SSL server (https). We recommend <a href="%1$s" target="_blank">fixing the SSL configuration on your server</a>, but if you need a quick fix you can:%2$s', 'wp-migrate-db' ), 'https://deliciousbrains.com/wp-migrate-db-pro/doc/could-not-connect-deliciousbrains-com/', sprintf( '<p><a href="%1$s" class="temporarily-disable-ssl button">%2$s</a></p>', $disable_ssl_url, __(  'Temporarily disable SSL for connections to deliciousbrains.com', 'wp-migrate-db' ) ) );
		} elseif ( isset( $errors['subscription_cancelled'] ) ) {
			$message = sprintf( __( '<strong>Your License Was Cancelled</strong> &mdash; Please visit <a href="%s" target="_blank">My Account</a> to renew or upgrade your license and enable push and pull.', 'wp-migrate-db' ), 'https://deliciousbrains.com/my-account/' );
			$message .= sprintf( '<br /><a href="%s">%s</a>', $check_licence_again_url, __( 'Check my license again', 'wp-migrate-db' ) );
		} elseif ( isset( $errors['subscription_expired'] ) ) {
			$message = sprintf( __( '<strong>Your License Has Expired</strong> &mdash; Your expired license has been added to this install, so you can now use the push and pull features. Please visit <a href="%s" target="_blank">My Account</a> to renew your license and continue receiving plugin updates and access to email support.', 'wp-migrate-db' ), 'https://deliciousbrains.com/my-account/' );
			$message .= sprintf( '<br /><a href="%s">%s</a>', $check_licence_again_url, __( 'Check my license again', 'wp-migrate-db' ) );
		} elseif ( isset( $errors['no_activations_left'] ) ) {
			$message = sprintf( __( '<strong>No Activations Left</strong> &mdash; Please visit <a href="%s" target="_blank">My Account</a> to upgrade your license or deactivate a previous activation and enable push and pull.', 'wp-migrate-db' ), 'https://deliciousbrains.com/my-account/' );
			$message .= sprintf( ' <a href="%s">%s</a>', $check_licence_again_url, __( 'Check my license again', 'wp-migrate-db' ) );
		} elseif ( isset( $errors['licence_not_found'] ) ) {
			if ( ! $api_response_provided ) {
				$message = sprintf( __( '<strong>Your License Was Not Found</strong> &mdash; Perhaps you made a typo when defining your WPMDB_LICENCE constant in your wp-config.php? Please visit <a href="%s" target="_blank">My Account</a> to double check your license key.', 'wp-migrate-db' ), 'https://deliciousbrains.com/my-account/' );
				$message .= sprintf( ' <a href="%s">%s</a>', $check_licence_again_url, __( 'Check my license again', 'wp-migrate-db' ) );
			} else {
				$error   = reset( $errors );
				$message = __( '<strong>Your License Was Not Found</strong> &mdash; ', 'wp-migrate-db' );
				$message .= $error;
			}
		} elseif ( isset( $errors['activation_deactivated'] ) ) {
			$message = sprintf( '<strong>%s</strong> &mdash; ', __( 'Your License Is Inactive', 'wp-migrate-db' ) );
			$message .= sprintf( '%s <a href="#" class="js-action-link reactivate-licence">%s</a>', __( 'Your license has been deactivated for this install.', 'wp-migrate-db' ), __( 'Reactivate License', 'wp-migrate-db' ) );
		} else {
			$error = reset( $errors );
			$message = sprintf( __( '<strong>An Unexpected Error Occurred</strong> &mdash; Please contact us at <a href="%1$s">%2$s</a> and quote the following:', 'wp-migrate-db' ), 'mailto:nom@deliciousbrains.com', 'nom@deliciousbrains.com' );
			$message .= sprintf( '<p>%s</p>', $error );
		}

		return $message;
	}

	function set_cli_migration() {
		$this->doing_cli_migration = true;
	}

	function end_ajax( $return = false ) {
		if ( defined( 'DOING_WPMDB_TESTS' ) || $this->doing_cli_migration ) {
			return ( false === $return ) ? null : $return;
		}

		echo ( false === $return ) ? '' : $return;
		exit;
	}

	function check_ajax_referer( $action ) {
		if ( defined( 'DOING_WPMDB_TESTS' ) || $this->doing_cli_migration ) {
			return;
		}

		$result = check_ajax_referer( $action, 'nonce', false );

		if ( false === $result ) {
			$return = array( 'wpmdb_error' => 1, 'body' => sprintf( __( 'Invalid nonce for: %s', 'wp-migrate-db' ), $action ) );
			$this->end_ajax( json_encode( $return ) );
		}

		$cap = ( is_multisite() ) ? 'manage_network_options' : 'export';
		$cap = apply_filters( 'wpmdb_ajax_cap', $cap );

		if ( ! current_user_can( $cap ) ) {
			$return = array( 'wpmdb_error' => 1, 'body' => sprintf( __( 'Access denied for: %s', 'wp-migrate-db' ), $action ) );
			$this->end_ajax( json_encode( $return ) );
		}
	}

	// Generates our secret key
	function generate_key( $length = 40 ) {
		$keyset = 'abcdefghijklmnopqrstuvqxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789+/';
		$key    = '';

		for ( $i = 0; $i < $length; $i ++ ) {
			$key .= substr( $keyset, wp_rand( 0, strlen( $keyset ) - 1 ), 1 );
		}

		return $key;
	}

	function get_bottleneck( $type = 'regular' ) {
		$suhosin_limit         = false;
		$suhosin_request_limit = false;
		$suhosin_post_limit    = false;

		if ( function_exists( 'ini_get' ) ) {
			$suhosin_request_limit = $this->return_bytes( ini_get( 'suhosin.request.max_value_length' ) );
			$suhosin_post_limit    = $this->return_bytes( ini_get( 'suhosin.post.max_value_length' ) );
		}

		if ( $suhosin_request_limit && $suhosin_post_limit ) {
			$suhosin_limit = min( $suhosin_request_limit, $suhosin_post_limit );
		}

		// we have to account for HTTP headers and other bloating, here we minus 1kb for bloat
		$post_max_upper_size   = apply_filters( 'wpmdb_post_max_upper_size', 26214400 );
		$calculated_bottleneck = min( ( $this->get_post_max_size() - 1024 ), $post_max_upper_size );

		if ( $suhosin_limit ) {
			$calculated_bottleneck = min( $calculated_bottleneck, $suhosin_limit - 1024 );
		}

		if ( $type != 'max' ) {
			$calculated_bottleneck = min( $calculated_bottleneck, $this->settings['max_request'] );
		}

		return apply_filters( 'wpmdb_bottleneck', $calculated_bottleneck );
	}

	function return_bytes( $val ) {
		if ( is_numeric( $val ) ) {
			return $val;
		}

		if ( empty( $val ) ) {
			return false;
		}

		$val  = trim( $val );
		$last = strtolower( $val[ strlen( $val ) - 1 ] );

		switch ( $last ) {
			// The 'G' modifier is available since PHP 5.1.0
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
				break;
			default :
				$val = false;
				break;
		}

		return $val;
	}

	function get_post_max_size() {
		$val  = trim( ini_get( 'post_max_size' ) );
		$last = strtolower( $val[ strlen( $val ) - 1 ] );

		switch ( $last ) {
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}

		return $val;
	}

	/**
	 * Returns a url string given an associative array as per the output of parse_url.
	 *
	 * @param $parsed_url
	 *
	 * @return string
	 */
	function unparse_url( $parsed_url ) {
		$scheme   = isset( $parsed_url['scheme'] ) ? $parsed_url['scheme'] . '://' : '';
		$host     = isset( $parsed_url['host'] ) ? $parsed_url['host'] : '';
		$port     = isset( $parsed_url['port'] ) ? ':' . $parsed_url['port'] : '';
		$user     = isset( $parsed_url['user'] ) ? $parsed_url['user'] : '';
		$pass     = isset( $parsed_url['pass'] ) ? ':' . $parsed_url['pass'] : '';
		$pass     = ( $user || $pass ) ? "$pass@" : '';
		$path     = isset( $parsed_url['path'] ) ? $parsed_url['path'] : '';
		$query    = isset( $parsed_url['query'] ) ? '?' . $parsed_url['query'] : '';
		$fragment = isset( $parsed_url['fragment'] ) ? '#' . $parsed_url['fragment'] : '';

		return "$scheme$user$pass$host$port$path$query$fragment";
	}

	/**
	 * Get a simplified url for use as the referrer.
	 *
	 * @param $referer_url
	 *
	 * @return string
	 *
	 * NOTE: mis-spelling intentional to match usage.
	 */
	function referer_from_url( $referer_url ) {
		$url_parts = $this->parse_url( $referer_url );

		if ( false !== $url_parts ) {
			$reduced_url_parts = array_intersect_key( $url_parts, array_flip( array( 'scheme', 'host', 'port', 'path' ) ) );
			if ( ! empty( $reduced_url_parts ) ) {
				$referer_url = $this->unparse_url( $reduced_url_parts );
			}
		}

		return $referer_url;
	}

	/**
	 * Parses a url into it's components. Compatible with PHP < 5.4.7.
	 *
	 * @param $url string The url to parse.
	 *
	 * @return array|false The parsed components or false on error.
	 */
	function parse_url( $url ) {
		if ( 0 === strpos( $url, '//' ) ) {
			$url       = 'http:' . $url;
			$no_scheme = true;
		} else {
			$no_scheme = false;
		}

		$parts = parse_url( $url );
		if ( $no_scheme ) {
			unset( $parts['scheme'] );
		}

		return $parts;
	}

	/**
	 * Standard notice display check
	 * Returns dismiss and reminder links html for templates where necessary
	 *
	 * @param string   $notice   The name of the notice e.g. license-key-warning
	 * @param bool     $dismiss  If the notice has a dismiss link
	 * @param bool|int $reminder If the notice has a reminder link, this will be the number of seconds
	 *
	 * @return array|bool
	 */
	function check_notice( $notice, $dismiss = false, $reminder = false ) {
		if ( true === apply_filters( 'wpmdb_hide_' . $notice, false ) ) {
			return false;
		}
		global $current_user;
		$notice_links = array();

		if ( $dismiss ) {
			if ( get_user_meta( $current_user->ID, 'wpmdb_dismiss_' . $notice ) ) {
				return false;
			}
			$notice_links['dismiss'] = '<a href="#" class="notice-link" data-notice="' . $notice . '" data-type="dismiss">' . _x( 'Dismiss', 'dismiss notice permanently', 'wp-migrate-db' ) . '</a>';
		}

		if ( $reminder ) {
			if ( ( $reminder_set = get_user_meta( $current_user->ID, 'wpmdb_reminder_' . $notice, true ) ) ) {
				if ( strtotime( 'now' ) < $reminder_set ) {
					return false;
				}
			}
			$notice_links['reminder'] = '<a href="#"  class="notice-link" data-notice="' . $notice . '" data-type="reminder" data-reminder="' . $reminder . '">' . __( 'Remind Me Later', 'wp-migrate-db' ) . '</a>';
		}

		return ( count( $notice_links ) > 0 ) ? $notice_links : true;
	}

	/**
	 * Performs a schema update if required.
	 */
	function maybe_schema_update() {
		$schema_version = get_site_option( 'wpmdb_schema_version' );
		$update_schema = false;

		/*
		 * Upgrade this option to a network wide option if the site has been upgraded
		 * from a regular WordPress installation to a multisite installation.
		 */
		if ( false === $schema_version && is_multisite() && is_network_admin() ) {
			$schema_version = get_option( 'wpmdb_schema_version' );
			if ( false !== $schema_version ) {
				update_site_option( 'wpmdb_schema_version', $schema_version );
				delete_option( 'wpmdb_schema_version' );
			}
		}

		if ( false === $schema_version ) {
			$schema_version = 0;
		}

		if ( $schema_version < 1 ) {
			$error_log = get_option( 'wpmdb_error_log' );
			// skip multisite installations as we can't use add_site_option because it doesn't include an 'autoload' argument
			if ( false !== $error_log && false === is_multisite() ) {
				delete_option( 'wpmdb_error_log' );
				add_option( 'wpmdb_error_log', $error_log, '', 'no' );
			}

			$update_schema = true;
			$schema_version = 1;
		}

		if ( true === $update_schema ) {
			update_site_option( 'wpmdb_schema_version', $schema_version );
		}
	}

	/**
	 * Converts file paths that include mixed slashes to use the correct type of slash for the current operating system.
	 *
	 * @param $path string
	 *
	 * @return string
	 */
	function slash_one_direction( $path ) {
		return str_replace( array( '/', '\\' ), DIRECTORY_SEPARATOR, $path );
	}

	/**
	 * Returns the absolute path to the root of the website.
	 *
	 * @return string
	 */
	function get_absolute_root_file_path() {
		static $absolute_path;

		if ( ! empty( $absolute_path ) ) {
			return $absolute_path;
		}

		$absolute_path = rtrim( ABSPATH, '\\/' );
		$site_url      = rtrim( site_url( '', 'http' ), '\\/' );
		$home_url      = rtrim( home_url( '', 'http' ), '\\/' );

		if ( $site_url != $home_url ) {
			$difference = str_replace( $home_url, '', $site_url );
			if ( strpos( $absolute_path, $difference ) !== false ) {
				$absolute_path = rtrim( substr( $absolute_path, 0, - strlen( $difference ) ), '\\/' );
			}
		}

		return $absolute_path;
	}
}
