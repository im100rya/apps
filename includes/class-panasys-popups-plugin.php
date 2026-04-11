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
		$width     = get_post_meta( $post->ID, '_panasys_popup_width', true );
		$auto_open = get_post_meta( $post->ID, '_panasys_popup_auto_open', true );

		wp_nonce_field( 'panasys_popup_settings', 'panasys_popup_settings_nonce' );
		?>
		<p>
			<label for="panasys-popup-width"><strong><?php esc_html_e( 'Popup width (px or %)', 'panasys-popups' ); ?></strong></label>
			<input id="panasys-popup-width" name="panasys_popup_width" type="text" class="widefat" value="<?php echo esc_attr( $width ? $width : '640px' ); ?>" />
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

		$width = isset( $_POST['panasys_popup_width'] ) ? sanitize_text_field( wp_unslash( $_POST['panasys_popup_width'] ) ) : '640px';
		$width = preg_match( '/^[0-9.]+(px|%)$/', $width ) ? $width : '640px';

		update_post_meta( $post_id, '_panasys_popup_width', $width );
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

		$width     = get_post_meta( $post_id, '_panasys_popup_width', true );
		$auto_open = get_post_meta( $post_id, '_panasys_popup_auto_open', true );
		$width     = $width ? $width : '640px';

		ob_start();
		?>
		<div class="panasys-popup" id="panasys-popup-<?php echo esc_attr( (string) $post_id ); ?>" data-auto-open="<?php echo esc_attr( '1' === $auto_open ? '1' : '0' ); ?>" data-hide-days="1" aria-hidden="true">
			<div class="panasys-popup__overlay" data-panasys-close="1"></div>
			<div class="panasys-popup__dialog" role="dialog" aria-modal="true" aria-labelledby="panasys-popup-title-<?php echo esc_attr( (string) $post_id ); ?>" style="--panasys-popup-max-width: <?php echo esc_attr( $width ); ?>;">
				<button class="panasys-popup__close" type="button" aria-label="<?php esc_attr_e( 'Close popup', 'panasys-popups' ); ?>" data-panasys-close="1">&times;</button>
				<h2 class="panasys-popup__title" id="panasys-popup-title-<?php echo esc_attr( (string) $post_id ); ?>"><?php echo esc_html( get_the_title( $popup ) ); ?></h2>
				<div class="panasys-popup__content">
					<?php echo wp_kses_post( do_shortcode( apply_filters( 'the_content', $popup->post_content ) ) ); ?>
				</div>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
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
	}
}
