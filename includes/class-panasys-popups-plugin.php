<?php
/**
 * Core plugin class.
 *
 * @package PanasysPopups
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin implementation.
 */
class Panasys_Popups_Plugin {

	/**
	 * Instance.
	 *
	 * @var Panasys_Popups_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get singleton.
	 *
	 * @return Panasys_Popups_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'init', array( $this, 'register_popup_post_type' ) );
		add_action( 'init', array( $this, 'register_shortcodes' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ) );
		add_action( 'save_post_panasys_popup', array( $this, 'save_popup_meta' ) );
		add_action( 'admin_notices', array( $this, 'render_donation_notice' ) );
		add_action( 'admin_init', array( $this, 'handle_donation_notice_dismissal' ) );
		add_action( 'edit_form_after_title', array( $this, 'render_shortcode_reference_near_title' ) );
		add_filter( 'manage_edit-panasys_popup_columns', array( $this, 'add_shortcode_column' ) );
		add_action( 'manage_panasys_popup_posts_custom_column', array( $this, 'render_shortcode_column' ), 10, 2 );
		add_action( 'admin_menu', array( $this, 'register_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_plugin_settings' ) );
	}

	/**
	 * Load plugin translations.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'panasys-popups', false, dirname( plugin_basename( PANASYS_POPUPS_FILE ) ) . '/languages' );
	}

	/**
	 * Register popup custom post type.
	 *
	 * @return void
	 */
	public function register_popup_post_type() {
		$labels = array(
			'name'               => __( 'Popups', 'panasys-popups' ),
			'singular_name'      => __( 'Popup', 'panasys-popups' ),
			'add_new'            => __( 'Add New Popup', 'panasys-popups' ),
			'add_new_item'       => __( 'Add New Popup', 'panasys-popups' ),
			'edit_item'          => __( 'Edit Popup', 'panasys-popups' ),
			'new_item'           => __( 'New Popup', 'panasys-popups' ),
			'view_item'          => __( 'View Popup', 'panasys-popups' ),
			'search_items'       => __( 'Search Popups', 'panasys-popups' ),
			'not_found'          => __( 'No popups found.', 'panasys-popups' ),
			'not_found_in_trash' => __( 'No popups found in trash.', 'panasys-popups' ),
			'menu_name'          => __( 'Panasys Popups', 'panasys-popups' ),
		);

		register_post_type(
			'panasys_popup',
			array(
				'labels'             => $labels,
				'public'             => false,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'show_in_rest'       => true,
				'menu_icon'          => 'dashicons-format-status',
				'supports'           => array( 'title', 'editor', 'thumbnail' ),
				'capability_type'    => 'post',
				'has_archive'        => false,
				'exclude_from_search'=> true,
			)
		);
	}


	/**
	 * Get default plugin settings.
	 *
	 * @return array<string,string>
	 */
	private function get_default_settings() {
		return array(
			'default_width'            => '640px',
			'default_background_color' => '#ffffff',
			'default_text_color'       => '#1e1e1e',
		);
	}

	/**
	 * Get saved plugin settings with defaults.
	 *
	 * @return array<string,string>
	 */
	private function get_plugin_settings() {
		$options = get_option( 'panasys_popups_options', array() );
		$options = is_array( $options ) ? $options : array();

		return wp_parse_args( $options, $this->get_default_settings() );
	}

	/**
	 * Register the settings submenu in the plugin sidebar menu.
	 *
	 * @return void
	 */
	public function register_settings_page() {
		add_submenu_page(
			'edit.php?post_type=panasys_popup',
			__( 'Panasys Popups Settings', 'panasys-popups' ),
			__( 'Settings', 'panasys-popups' ),
			'manage_options',
			'panasys-popups-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register plugin settings.
	 *
	 * @return void
	 */
	public function register_plugin_settings() {
		register_setting(
			'panasys_popups_settings',
			'panasys_popups_options',
			array(
				'sanitize_callback' => array( $this, 'sanitize_plugin_settings' ),
				'default'           => $this->get_default_settings(),
			)
		);
	}

	/**
	 * Sanitize plugin settings.
	 *
	 * @param array<string,mixed> $settings Raw settings.
	 * @return array<string,string>
	 */
	public function sanitize_plugin_settings( $settings ) {
		$defaults = $this->get_default_settings();
		$settings = is_array( $settings ) ? $settings : array();

		$width = isset( $settings['default_width'] ) ? sanitize_text_field( wp_unslash( $settings['default_width'] ) ) : $defaults['default_width'];
		$width = preg_match( '/^[0-9.]+(px|%)$/', $width ) ? $width : $defaults['default_width'];

		$background_color = isset( $settings['default_background_color'] ) ? sanitize_hex_color( wp_unslash( $settings['default_background_color'] ) ) : $defaults['default_background_color'];
		$text_color       = isset( $settings['default_text_color'] ) ? sanitize_hex_color( wp_unslash( $settings['default_text_color'] ) ) : $defaults['default_text_color'];

		return array(
			'default_width'            => $width,
			'default_background_color' => $background_color ? $background_color : $defaults['default_background_color'],
			'default_text_color'       => $text_color ? $text_color : $defaults['default_text_color'],
		);
	}

	/**
	 * Render plugin settings page.
	 *
	 * @return void
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = $this->get_plugin_settings();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Panasys Popups Settings', 'panasys-popups' ); ?></h1>
			<p><?php esc_html_e( 'Set default styling for new and unconfigured popups. Individual popup settings can override these values.', 'panasys-popups' ); ?></p>
			<form action="options.php" method="post">
				<?php settings_fields( 'panasys_popups_settings' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="panasys-popups-default-width"><?php esc_html_e( 'Default modal width', 'panasys-popups' ); ?></label></th>
						<td>
							<input id="panasys-popups-default-width" name="panasys_popups_options[default_width]" type="text" class="regular-text" value="<?php echo esc_attr( $settings['default_width'] ); ?>" />
							<p class="description"><?php esc_html_e( 'Use a pixel or percentage value, for example 640px or 80%.', 'panasys-popups' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="panasys-popups-default-background-color"><?php esc_html_e( 'Default background color', 'panasys-popups' ); ?></label></th>
						<td><input id="panasys-popups-default-background-color" name="panasys_popups_options[default_background_color]" type="color" value="<?php echo esc_attr( $settings['default_background_color'] ); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="panasys-popups-default-text-color"><?php esc_html_e( 'Default text color', 'panasys-popups' ); ?></label></th>
						<td><input id="panasys-popups-default-text-color" name="panasys_popups_options[default_text_color]" type="color" value="<?php echo esc_attr( $settings['default_text_color'] ); ?>" /></td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}


	/**
	 * Add shortcode column next to popup name in list table.
	 *
	 * @param array<string,string> $columns Existing columns.
	 * @return array<string,string>
	 */
	public function add_shortcode_column( $columns ) {
		$new_columns = array();
		foreach ( $columns as $key => $label ) {
			$new_columns[ $key ] = $label;
			if ( 'title' === $key ) {
				$new_columns['panasys_shortcodes'] = __( 'Shortcodes', 'panasys-popups' );
			}
		}

		return $new_columns;
	}

	/**
	 * Render shortcode column value.
	 *
	 * @param string $column  Column slug.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function render_shortcode_column( $column, $post_id ) {
		if ( 'panasys_shortcodes' !== $column ) {
			return;
		}

		echo '<code>[panasys_popup id="' . esc_html( (string) $post_id ) . '"]</code><br />';
		echo '<code>[panasys_popup_trigger id="' . esc_html( (string) $post_id ) . '" label="' . esc_html__( 'Open Popup', 'panasys-popups' ) . '"]</code>';
	}

	/**
	 * Render admin donation notice for plugin users.
	 *
	 * @return void
	 */
	public function render_donation_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && 'plugins' !== $screen->id && 'dashboard' !== $screen->id && 'edit-panasys_popup' !== $screen->id && 'panasys_popup' !== $screen->id ) {
			return;
		}

		if ( get_user_meta( get_current_user_id(), '_panasys_popups_donation_notice_dismissed', true ) ) {
			return;
		}

		$dismiss_url = wp_nonce_url(
			add_query_arg(
				array(
					'panasys_popups_notice' => 'dismiss',
				),
				admin_url()
			),
			'panasys_popups_dismiss_notice'
		);

		echo '<div class="notice notice-info is-dismissible">';
		echo '<p>' . esc_html__( 'Enjoying Panasys Popups? Support future updates by donating.', 'panasys-popups' ) . ' ';
		echo '<a href="' . esc_url( 'https://www.paypal.me/100rya' ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Donate via PayPal', 'panasys-popups' ) . '</a>';
		echo ' · <a href="' . esc_url( $dismiss_url ) . '">' . esc_html__( 'Dismiss', 'panasys-popups' ) . '</a></p>';
		echo '</div>';
	}

	/**
	 * Handle admin donation notice dismissal.
	 *
	 * @return void
	 */
	public function handle_donation_notice_dismissal() {
		if ( ! isset( $_GET['panasys_popups_notice'] ) || 'dismiss' !== $_GET['panasys_popups_notice'] ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_admin_referer( 'panasys_popups_dismiss_notice' );
		update_user_meta( get_current_user_id(), '_panasys_popups_donation_notice_dismissed', 1 );
		wp_safe_redirect( remove_query_arg( array( 'panasys_popups_notice', '_wpnonce' ) ) );
		exit;
	}


	/**
	 * Show popup shortcode references below title field.
	 *
	 * @param WP_Post $post Current post.
	 * @return void
	 */
	public function render_shortcode_reference_near_title( $post ) {
		if ( ! $post instanceof WP_Post || 'panasys_popup' !== $post->post_type ) {
			return;
		}

		$popup_id = $post->ID ? (string) $post->ID : 'ID';

		echo '<div class="notice notice-info inline" style="margin: 10px 0 15px;">';
		echo '<p><strong>' . esc_html__( 'Popup Shortcodes', 'panasys-popups' ) . ':</strong> ';
		echo '<code>[panasys_popup id="' . esc_html( $popup_id ) . '"]</code> ';
		echo '<code>[panasys_popup_trigger id="' . esc_html( $popup_id ) . '" label="' . esc_html__( 'Open Popup', 'panasys-popups' ) . '"]</code>';
		echo '</p>';
		echo '</div>';
	}

	/**
	 * Register metaboxes.
	 *
	 * @return void
	 */
	public function register_meta_boxes() {
		add_meta_box(
			'panasys_popup_settings',
			__( 'Popup Settings', 'panasys-popups' ),
			array( $this, 'render_popup_settings_meta_box' ),
			'panasys_popup',
			'side',
			'default'
		);
	}

	/**
	 * Render popup settings.
	 *
	 * @param WP_Post $post Current post object.
	 * @return void
	 */
	public function render_popup_settings_meta_box( $post ) {
		$settings         = $this->get_plugin_settings();
		$width            = get_post_meta( $post->ID, '_panasys_popup_width', true );
		$auto_open        = get_post_meta( $post->ID, '_panasys_popup_auto_open', true );
		$background_color = get_post_meta( $post->ID, '_panasys_popup_background_color', true );
		$text_color       = get_post_meta( $post->ID, '_panasys_popup_text_color', true );

		wp_nonce_field( 'panasys_popup_settings', 'panasys_popup_settings_nonce' );
		?>
		<p>
			<label for="panasys-popup-width"><strong><?php esc_html_e( 'Modal width (px or %)', 'panasys-popups' ); ?></strong></label>
			<input id="panasys-popup-width" name="panasys_popup_width" type="text" class="widefat" value="<?php echo esc_attr( $width ? $width : $settings['default_width'] ); ?>" />
		</p>
		<p>
			<label for="panasys-popup-background-color"><strong><?php esc_html_e( 'Modal background color', 'panasys-popups' ); ?></strong></label>
			<input id="panasys-popup-background-color" name="panasys_popup_background_color" type="color" value="<?php echo esc_attr( $background_color ? $background_color : $settings['default_background_color'] ); ?>" />
		</p>
		<p>
			<label for="panasys-popup-text-color"><strong><?php esc_html_e( 'Modal text color', 'panasys-popups' ); ?></strong></label>
			<input id="panasys-popup-text-color" name="panasys_popup_text_color" type="color" value="<?php echo esc_attr( $text_color ? $text_color : $settings['default_text_color'] ); ?>" />
		</p>
		<p>
			<label for="panasys-popup-auto-open">
				<input id="panasys-popup-auto-open" name="panasys_popup_auto_open" type="checkbox" value="1" <?php checked( $auto_open, '1' ); ?> />
				<?php esc_html_e( 'Open automatically on page load.', 'panasys-popups' ); ?>
			</label>
		</p>
		<p>
			<?php esc_html_e( 'Use shortcode:', 'panasys-popups' ); ?>
			<code>[panasys_popup id="<?php echo esc_html( (string) $post->ID ); ?>"]</code>
		</p>
		<?php
	}

	/**
	 * Save popup settings.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function save_popup_meta( $post_id ) {
		if ( ! isset( $_POST['panasys_popup_settings_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['panasys_popup_settings_nonce'] ) ), 'panasys_popup_settings' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$width            = isset( $_POST['panasys_popup_width'] ) ? sanitize_text_field( wp_unslash( $_POST['panasys_popup_width'] ) ) : '640px';
		$width            = preg_match( '/^[0-9.]+(px|%)$/', $width ) ? $width : '640px';
		$background_color = isset( $_POST['panasys_popup_background_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['panasys_popup_background_color'] ) ) : '#ffffff';
		$text_color       = isset( $_POST['panasys_popup_text_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['panasys_popup_text_color'] ) ) : '#1e1e1e';

		update_post_meta( $post_id, '_panasys_popup_width', $width );
		update_post_meta( $post_id, '_panasys_popup_background_color', $background_color ? $background_color : '#ffffff' );
		update_post_meta( $post_id, '_panasys_popup_text_color', $text_color ? $text_color : '#1e1e1e' );
		update_post_meta( $post_id, '_panasys_popup_auto_open', isset( $_POST['panasys_popup_auto_open'] ) ? '1' : '0' );
	}

	/**
	 * Register plugin shortcodes.
	 *
	 * @return void
	 */
	public function register_shortcodes() {
		add_shortcode( 'panasys_popup', array( $this, 'render_popup_shortcode' ) );
		add_shortcode( 'panasys_popup_trigger', array( $this, 'render_trigger_shortcode' ) );
	}

	/**
	 * Render popup shortcode.
	 *
	 * @param array<string,mixed> $atts Attributes.
	 * @return string
	 */
	public function render_popup_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'id' => 0,
			),
			$atts,
			'panasys_popup'
		);

		$post_id = absint( $atts['id'] );
		$popup   = get_post( $post_id );

		if ( ! $popup || 'panasys_popup' !== $popup->post_type || 'publish' !== $popup->post_status ) {
			return '';
		}

		$settings         = $this->get_plugin_settings();
		$width            = get_post_meta( $post_id, '_panasys_popup_width', true );
		$auto_open        = get_post_meta( $post_id, '_panasys_popup_auto_open', true );
		$background_color = get_post_meta( $post_id, '_panasys_popup_background_color', true );
		$text_color       = get_post_meta( $post_id, '_panasys_popup_text_color', true );
		$width            = $width ? $width : $settings['default_width'];
		$background_color = $background_color ? $background_color : $settings['default_background_color'];
		$text_color       = $text_color ? $text_color : $settings['default_text_color'];

		ob_start();
		?>
		<div class="panasys-popup" id="panasys-popup-<?php echo esc_attr( (string) $post_id ); ?>" data-auto-open="<?php echo esc_attr( '1' === $auto_open ? '1' : '0' ); ?>" data-hide-days="1" aria-hidden="true">
			<div class="panasys-popup__overlay" data-panasys-close="1"></div>
			<div class="panasys-popup__dialog" role="dialog" aria-modal="true" aria-labelledby="panasys-popup-title-<?php echo esc_attr( (string) $post_id ); ?>" style="--panasys-popup-max-width: <?php echo esc_attr( $width ); ?>; --panasys-popup-background-color: <?php echo esc_attr( $background_color ); ?>; --panasys-popup-text-color: <?php echo esc_attr( $text_color ); ?>;">
				<button class="panasys-popup__close" type="button" aria-label="<?php esc_attr_e( 'Close popup', 'panasys-popups' ); ?>" data-panasys-close="1">&times;</button>
				<h2 class="panasys-popup__title" id="panasys-popup-title-<?php echo esc_attr( (string) $post_id ); ?>"><?php echo esc_html( get_the_title( $popup ) ); ?></h2>
				<div class="panasys-popup__content">
					<?php echo $this->render_popup_content( $popup->post_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}


	/**
	 * Render popup content with shortcode and oEmbed support.
	 *
	 * @param string $content Popup post content.
	 * @return string
	 */
	private function render_popup_content( $content ) {
		$content = $this->convert_standalone_image_links( $content );
		$content = apply_filters( 'the_content', $content );
		$content = do_shortcode( $content );

		return wp_kses( $content, $this->get_allowed_popup_html() );
	}


	/**
	 * Convert standalone image URLs into image markup before content filters run.
	 *
	 * @param string $content Popup post content.
	 * @return string
	 */
	private function convert_standalone_image_links( $content ) {
		$lines = preg_split( '/\r\n|\r|\n/', $content );
		if ( ! is_array( $lines ) ) {
			return $content;
		}

		foreach ( $lines as $index => $line ) {
			$trimmed = trim( $line );
			if ( preg_match( '#^https?://[^\s]+\.(?:jpg|jpeg|png|gif|webp|svg)(?:\?[^\s]*)?$#i', $trimmed ) ) {
				$lines[ $index ] = '<img src="' . esc_url( $trimmed ) . '" alt="" loading="lazy" />';
			}
		}

		return implode( "\n", $lines );
	}

	/**
	 * Get HTML allowlist for popup content, including safe oEmbed iframe output.
	 *
	 * @return array<string,array<string,bool|array<string,bool>>>
	 */
	private function get_allowed_popup_html() {
		$allowed = wp_kses_allowed_html( 'post' );

		$allowed['iframe'] = array(
			'src'             => true,
			'title'           => true,
			'width'           => true,
			'height'          => true,
			'frameborder'     => true,
			'allow'           => true,
			'allowfullscreen' => true,
			'loading'         => true,
			'referrerpolicy'  => true,
			'class'           => true,
			'id'              => true,
			'style'           => true,
		);

		$allowed['blockquote'] = array(
			'class'     => true,
			'cite'      => true,
			'data-*'    => true,
			'lang'      => true,
			'dir'       => true,
			'style'     => true,
		);

		$allowed['div']['data-*'] = true;
		$allowed['span']['data-*'] = true;

		return $allowed;
	}

	/**
	 * Render trigger button shortcode.
	 *
	 * @param array<string,mixed> $atts Attributes.
	 * @return string
	 */
	public function render_trigger_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'id'    => 0,
				'label' => __( 'Open Popup', 'panasys-popups' ),
				'class' => '',
			),
			$atts,
			'panasys_popup_trigger'
		);

		$post_id = absint( $atts['id'] );

		if ( ! $post_id ) {
			return '';
		}

		$classes = array( 'panasys-popup-trigger' );
		if ( ! empty( $atts['class'] ) ) {
			$user_classes = preg_split( '/\s+/', (string) $atts['class'] );
			if ( is_array( $user_classes ) ) {
				foreach ( $user_classes as $user_class ) {
					$sanitized = sanitize_html_class( $user_class );
					if ( $sanitized ) {
						$classes[] = $sanitized;
					}
				}
			}
		}

		return sprintf(
			'<button type="button" class="%1$s" data-panasys-open="panasys-popup-%2$d">%3$s</button>',
			esc_attr( implode( ' ', $classes ) ),
			$post_id,
			esc_html( (string) $atts['label'] )
		);
	}

	/**
	 * Enqueue frontend styles/scripts.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		wp_register_style(
			'panasys-popups',
			PANASYS_POPUPS_URL . 'assets/css/panasys-popups.css',
			array(),
			PANASYS_POPUPS_VERSION
		);
		wp_register_script(
			'panasys-popups',
			PANASYS_POPUPS_URL . 'assets/js/panasys-popups.js',
			array(),
			PANASYS_POPUPS_VERSION,
			true
		);

		wp_enqueue_style( 'panasys-popups' );
		wp_enqueue_script( 'panasys-popups' );
		wp_enqueue_script( 'wp-embed' );
	}
}
