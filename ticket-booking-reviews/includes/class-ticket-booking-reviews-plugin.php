<?php
/**
 * Core plugin class.
 *
 * @package TicketBookingReviews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ticket booking, seat-map, WooCommerce, and review implementation.
 */
class Ticket_Booking_Reviews_Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Ticket_Booking_Reviews_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get singleton.
	 *
	 * @return Ticket_Booking_Reviews_Plugin
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
		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ) );
		add_action( 'save_post_tbr_event', array( $this, 'save_event_meta' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_shortcode( 'ticket_booking_event', array( $this, 'render_event_shortcode' ) );
		add_shortcode( 'ticket_booking_events', array( $this, 'render_events_list_shortcode' ) );
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_cart_item_data' ), 10, 3 );
		add_filter( 'woocommerce_get_item_data', array( $this, 'display_cart_item_data' ), 10, 2 );
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'add_order_line_item_meta' ), 10, 4 );
		add_action( 'comment_form_after_fields', array( $this, 'render_review_rating_field' ) );
		add_action( 'comment_form_logged_in_after', array( $this, 'render_review_rating_field' ) );
		add_action( 'comment_post', array( $this, 'save_review_rating' ) );
	}

	/** Load translations. */
	public function load_textdomain() {
		load_plugin_textdomain( 'ticket-booking-reviews', false, dirname( plugin_basename( TBR_FILE ) ) . '/languages' );
	}

	/** Register event and review post types. */
	public function register_post_types() {
		register_post_type(
			'tbr_event',
			array(
				'labels'       => array(
					'name'          => __( 'Ticket Events', 'ticket-booking-reviews' ),
					'singular_name' => __( 'Ticket Event', 'ticket-booking-reviews' ),
					'add_new_item'  => __( 'Add New Ticket Event', 'ticket-booking-reviews' ),
					'edit_item'     => __( 'Edit Ticket Event', 'ticket-booking-reviews' ),
					'menu_name'     => __( 'Ticket Booking', 'ticket-booking-reviews' ),
				),
				'public'       => true,
				'show_in_rest' => true,
				'menu_icon'    => 'dashicons-tickets-alt',
				'supports'     => array( 'title', 'editor', 'thumbnail', 'comments' ),
				'has_archive'  => true,
			)
		);
	}

	/** Enqueue frontend assets. */
	public function enqueue_assets() {
		wp_enqueue_style( 'ticket-booking-reviews', TBR_URL . 'assets/css/ticket-booking-reviews.css', array(), TBR_VERSION );
		wp_enqueue_script( 'ticket-booking-reviews', TBR_URL . 'assets/js/ticket-booking-reviews.js', array(), TBR_VERSION, true );
	}

	/** Register event metaboxes. */
	public function register_meta_boxes() {
		add_meta_box( 'tbr_event_details', __( 'Ticket Booking Details', 'ticket-booking-reviews' ), array( $this, 'render_event_details_metabox' ), 'tbr_event', 'normal', 'high' );
		add_meta_box( 'tbr_seat_structure', __( 'Seat Structure & Venue Layout', 'ticket-booking-reviews' ), array( $this, 'render_seat_structure_metabox' ), 'tbr_event', 'normal', 'default' );
		add_meta_box( 'tbr_shortcode', __( 'Booking Shortcode', 'ticket-booking-reviews' ), array( $this, 'render_shortcode_metabox' ), 'tbr_event', 'side', 'default' );
	}

	/** Get supported event types. */
	private function get_event_types() {
		return array(
			'movie_theater'  => __( 'Movie Theater', 'ticket-booking-reviews' ),
			'cricket_match'  => __( 'Cricket Match', 'ticket-booking-reviews' ),
			'football_match' => __( 'Football Match', 'ticket-booking-reviews' ),
			'event_pass'     => __( 'Event Pass', 'ticket-booking-reviews' ),
		);
	}

	/** Render details metabox. */
	public function render_event_details_metabox( $post ) {
		wp_nonce_field( 'tbr_save_event', 'tbr_event_nonce' );
		$type       = get_post_meta( $post->ID, '_tbr_event_type', true );
		$product_id = absint( get_post_meta( $post->ID, '_tbr_product_id', true ) );
		$venue      = get_post_meta( $post->ID, '_tbr_venue', true );
		$starts     = get_post_meta( $post->ID, '_tbr_starts_at', true );
		?>
		<p><label for="tbr-event-type"><strong><?php esc_html_e( 'Event type', 'ticket-booking-reviews' ); ?></strong></label></p>
		<select id="tbr-event-type" name="tbr_event_type">
			<?php foreach ( $this->get_event_types() as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $type, $key ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<p><label for="tbr-product-id"><strong><?php esc_html_e( 'WooCommerce product ID', 'ticket-booking-reviews' ); ?></strong></label></p>
		<input id="tbr-product-id" name="tbr_product_id" type="number" min="0" class="small-text" value="<?php echo esc_attr( $product_id ); ?>" />
		<p class="description"><?php esc_html_e( 'Use a simple or virtual WooCommerce product as the ticket checkout item.', 'ticket-booking-reviews' ); ?></p>
		<p><label for="tbr-venue"><strong><?php esc_html_e( 'Venue', 'ticket-booking-reviews' ); ?></strong></label></p>
		<input id="tbr-venue" name="tbr_venue" type="text" class="widefat" value="<?php echo esc_attr( $venue ); ?>" />
		<p><label for="tbr-starts-at"><strong><?php esc_html_e( 'Start date/time', 'ticket-booking-reviews' ); ?></strong></label></p>
		<input id="tbr-starts-at" name="tbr_starts_at" type="datetime-local" value="<?php echo esc_attr( $starts ); ?>" />
		<?php
	}

	/** Render seat structure metabox. */
	public function render_seat_structure_metabox( $post ) {
		$rows     = max( 1, absint( get_post_meta( $post->ID, '_tbr_seat_rows', true ) ) );
		$cols     = max( 1, absint( get_post_meta( $post->ID, '_tbr_seat_columns', true ) ) );
		$sections        = get_post_meta( $post->ID, '_tbr_seat_sections', true );
		$tiers           = get_post_meta( $post->ID, '_tbr_seat_tiers', true );
		$position_notes  = get_post_meta( $post->ID, '_tbr_seat_position_notes', true );
		$screen_position = get_post_meta( $post->ID, '_tbr_screen_position', true );
		$pavilion_ends   = get_post_meta( $post->ID, '_tbr_pavilion_ends', true );
		$blocked         = get_post_meta( $post->ID, '_tbr_blocked_seats', true );
		?>
		<p><?php esc_html_e( 'Define a reusable venue map for reserved seating. Use rows/columns for the physical seat grid, then map rows into tiers and named sections.', 'ticket-booking-reviews' ); ?></p>
		<p><label><?php esc_html_e( 'Rows', 'ticket-booking-reviews' ); ?> <input name="tbr_seat_rows" type="number" min="1" max="100" value="<?php echo esc_attr( $rows ); ?>" /></label> <label><?php esc_html_e( 'Seats per row', 'ticket-booking-reviews' ); ?> <input name="tbr_seat_columns" type="number" min="1" max="200" value="<?php echo esc_attr( $cols ); ?>" /></label></p>
		<p><label for="tbr-seat-sections"><strong><?php esc_html_e( 'Seat sections / stands', 'ticket-booking-reviews' ); ?></strong></label></p>
		<textarea id="tbr-seat-sections" name="tbr_seat_sections" rows="4" class="widefat" placeholder="North Stand: A-C\nPavilion: D-F\nVIP: G-H"><?php echo esc_textarea( $sections ); ?></textarea>
		<p class="description"><?php esc_html_e( 'One section per line, format: Section name: row range. Examples: Balcony: A-C, Pavilion: D-F.', 'ticket-booking-reviews' ); ?></p>
		<p><label for="tbr-seat-tiers"><strong><?php esc_html_e( 'Tier-wise seats and pricing labels', 'ticket-booking-reviews' ); ?></strong></label></p>
		<textarea id="tbr-seat-tiers" name="tbr_seat_tiers" rows="4" class="widefat" placeholder="Platinum: A-B | 2500\nGold: C-E | 1500\nSilver: F-H | 750"><?php echo esc_textarea( $tiers ); ?></textarea>
		<p class="description"><?php esc_html_e( 'One tier per line, format: Tier name: row range | optional price/label. WooCommerce still controls final checkout price.', 'ticket-booking-reviews' ); ?></p>
		<p><label for="tbr-seat-position-notes"><strong><?php esc_html_e( 'Seat position notes', 'ticket-booking-reviews' ); ?></strong></label></p>
		<textarea id="tbr-seat-position-notes" name="tbr_seat_position_notes" rows="3" class="widefat" placeholder="A1-A10: Left aisle\nA11-A20: Center\nA21-A30: Right aisle"><?php echo esc_textarea( $position_notes ); ?></textarea>
		<p><label for="tbr-screen-position"><strong><?php esc_html_e( 'Movie theater screen position', 'ticket-booking-reviews' ); ?></strong></label></p>
		<select id="tbr-screen-position" name="tbr_screen_position"><option value="front" <?php selected( $screen_position, 'front' ); ?>><?php esc_html_e( 'Front / Top of map', 'ticket-booking-reviews' ); ?></option><option value="back" <?php selected( $screen_position, 'back' ); ?>><?php esc_html_e( 'Back / Bottom of map', 'ticket-booking-reviews' ); ?></option></select>
		<p><label for="tbr-pavilion-ends"><strong><?php esc_html_e( 'Cricket pavilion ends', 'ticket-booking-reviews' ); ?></strong></label></p>
		<input id="tbr-pavilion-ends" name="tbr_pavilion_ends" type="text" class="widefat" value="<?php echo esc_attr( $pavilion_ends ); ?>" placeholder="Pavilion End, City End" />
		<p><label for="tbr-blocked-seats"><strong><?php esc_html_e( 'Blocked/unavailable seats', 'ticket-booking-reviews' ); ?></strong></label></p>
		<input id="tbr-blocked-seats" name="tbr_blocked_seats" type="text" class="widefat" value="<?php echo esc_attr( $blocked ); ?>" placeholder="A1,A2,B10" />
		<?php
	}

	/** Render shortcode help. */
	public function render_shortcode_metabox( $post ) {
		echo '<code>[ticket_booking_event id=&quot;' . esc_html( (string) $post->ID ) . '&quot;]</code>';
	}

	/** Save event meta. */
	public function save_event_meta( $post_id ) {
		if ( ! isset( $_POST['tbr_event_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tbr_event_nonce'] ) ), 'tbr_save_event' ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) {
			return;
		}

		$type = isset( $_POST['tbr_event_type'] ) ? sanitize_key( wp_unslash( $_POST['tbr_event_type'] ) ) : 'event_pass';
		if ( ! array_key_exists( $type, $this->get_event_types() ) ) {
			$type = 'event_pass';
		}

		update_post_meta( $post_id, '_tbr_event_type', $type );
		update_post_meta( $post_id, '_tbr_product_id', isset( $_POST['tbr_product_id'] ) ? absint( $_POST['tbr_product_id'] ) : 0 );
		update_post_meta( $post_id, '_tbr_venue', isset( $_POST['tbr_venue'] ) ? sanitize_text_field( wp_unslash( $_POST['tbr_venue'] ) ) : '' );
		update_post_meta( $post_id, '_tbr_starts_at', isset( $_POST['tbr_starts_at'] ) ? sanitize_text_field( wp_unslash( $_POST['tbr_starts_at'] ) ) : '' );
		update_post_meta( $post_id, '_tbr_seat_rows', isset( $_POST['tbr_seat_rows'] ) ? min( 100, max( 1, absint( $_POST['tbr_seat_rows'] ) ) ) : 1 );
		update_post_meta( $post_id, '_tbr_seat_columns', isset( $_POST['tbr_seat_columns'] ) ? min( 200, max( 1, absint( $_POST['tbr_seat_columns'] ) ) ) : 1 );
		update_post_meta( $post_id, '_tbr_seat_sections', isset( $_POST['tbr_seat_sections'] ) ? sanitize_textarea_field( wp_unslash( $_POST['tbr_seat_sections'] ) ) : '' );
		update_post_meta( $post_id, '_tbr_seat_tiers', isset( $_POST['tbr_seat_tiers'] ) ? sanitize_textarea_field( wp_unslash( $_POST['tbr_seat_tiers'] ) ) : '' );
		update_post_meta( $post_id, '_tbr_seat_position_notes', isset( $_POST['tbr_seat_position_notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['tbr_seat_position_notes'] ) ) : '' );
		update_post_meta( $post_id, '_tbr_screen_position', isset( $_POST['tbr_screen_position'] ) && 'back' === sanitize_key( wp_unslash( $_POST['tbr_screen_position'] ) ) ? 'back' : 'front' );
		update_post_meta( $post_id, '_tbr_pavilion_ends', isset( $_POST['tbr_pavilion_ends'] ) ? sanitize_text_field( wp_unslash( $_POST['tbr_pavilion_ends'] ) ) : '' );
		update_post_meta( $post_id, '_tbr_blocked_seats', isset( $_POST['tbr_blocked_seats'] ) ? sanitize_text_field( wp_unslash( $_POST['tbr_blocked_seats'] ) ) : '' );
	}

	/**
	 * Convert a 1-based row number to spreadsheet-style letters.
	 *
	 * @param int $row Row number.
	 * @return string
	 */
	private function get_row_label( $row ) {
		$label = '';
		while ( $row > 0 ) {
			$row--;
			$label = chr( 65 + ( $row % 26 ) ) . $label;
			$row   = (int) floor( $row / 26 );
		}

		return $label;
	}

	/**
	 * Convert spreadsheet-style row letters back to a number for row-range checks.
	 *
	 * @param string $label Row label.
	 * @return int
	 */
	private function get_row_number( $label ) {
		$number = 0;
		foreach ( str_split( strtoupper( trim( $label ) ) ) as $char ) {
			if ( $char < 'A' || $char > 'Z' ) {
				continue;
			}

			$number = ( $number * 26 ) + ( ord( $char ) - 64 );
		}

		return $number;
	}

	/**
	 * Parse newline venue mappings such as "Gold: A-C | 1500".
	 *
	 * @param string $raw Raw mapping text.
	 * @return array<int,array{name:string,range:string,note:string}>
	 */
	private function parse_layout_lines( $raw ) {
		$items = array();
		foreach ( preg_split( '/\r\n|\r|\n/', (string) $raw ) as $line ) {
			$line = trim( $line );
			if ( '' === $line || false === strpos( $line, ':' ) ) {
				continue;
			}

			list( $name, $details ) = array_map( 'trim', explode( ':', $line, 2 ) );
			$parts                  = array_map( 'trim', explode( '|', $details, 2 ) );
			$items[]                = array(
				'name'  => $name,
				'range' => $parts[0],
				'note'  => isset( $parts[1] ) ? $parts[1] : '',
			);
		}

		return $items;
	}

	/**
	 * Find the first section/tier label that contains a row label.
	 *
	 * @param string $row_label Row label.
	 * @param array  $items Parsed layout lines.
	 * @return string
	 */
	private function find_layout_label_for_row( $row_label, $items ) {
		foreach ( $items as $item ) {
			if ( empty( $item['range'] ) ) {
				continue;
			}

			if ( false !== strpos( $item['range'], '-' ) ) {
				list( $start, $end ) = array_map( 'trim', explode( '-', $item['range'], 2 ) );
				$row_number   = $this->get_row_number( $row_label );
				$start_number = $this->get_row_number( $start );
				$end_number   = $this->get_row_number( $end );
				if ( $row_number >= $start_number && $row_number <= $end_number ) {
					return $item['name'];
				}
			} elseif ( $row_label === $item['range'] ) {
				return $item['name'];
			}
		}

		return '';
	}

	/**
	 * Render parsed section/tier legend.
	 *
	 * @param string $title Legend title.
	 * @param array  $items Parsed layout lines.
	 */
	private function render_layout_legend( $title, $items ) {
		if ( empty( $items ) ) {
			return;
		}
		?>
		<div class="tbr-layout-legend">
			<strong><?php echo esc_html( $title ); ?></strong>
			<ul>
				<?php foreach ( $items as $item ) : ?>
					<li><?php echo esc_html( $item['name'] . ': ' . $item['range'] . ( $item['note'] ? ' (' . $item['note'] . ')' : '' ) ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}

	/** Render booking shortcode. */
	public function render_event_shortcode( $atts ) {
		$atts       = shortcode_atts( array( 'id' => 0 ), $atts, 'ticket_booking_event' );
		$event_id   = absint( $atts['id'] );
		$product_id = absint( get_post_meta( $event_id, '_tbr_product_id', true ) );
		if ( ! $event_id || 'tbr_event' !== get_post_type( $event_id ) ) {
			return '';
		}

		$rows           = max( 1, absint( get_post_meta( $event_id, '_tbr_seat_rows', true ) ) );
		$cols           = max( 1, absint( get_post_meta( $event_id, '_tbr_seat_columns', true ) ) );
		$blocked        = array_filter( array_map( 'trim', explode( ',', (string) get_post_meta( $event_id, '_tbr_blocked_seats', true ) ) ) );
		$type           = get_post_meta( $event_id, '_tbr_event_type', true );
		$screen         = get_post_meta( $event_id, '_tbr_screen_position', true );
		$ends           = get_post_meta( $event_id, '_tbr_pavilion_ends', true );
		$sections       = $this->parse_layout_lines( get_post_meta( $event_id, '_tbr_seat_sections', true ) );
		$tiers          = $this->parse_layout_lines( get_post_meta( $event_id, '_tbr_seat_tiers', true ) );
		$position_notes = get_post_meta( $event_id, '_tbr_seat_position_notes', true );
		ob_start();
		?>
		<div class="tbr-booking" data-event-id="<?php echo esc_attr( $event_id ); ?>">
			<h3><?php echo esc_html( get_the_title( $event_id ) ); ?></h3>
			<p class="tbr-meta"><?php echo esc_html( get_post_meta( $event_id, '_tbr_venue', true ) ); ?> <?php echo esc_html( get_post_meta( $event_id, '_tbr_starts_at', true ) ); ?></p>
			<?php $this->render_layout_legend( __( 'Sections / stands', 'ticket-booking-reviews' ), $sections ); ?>
			<?php $this->render_layout_legend( __( 'Seat tiers', 'ticket-booking-reviews' ), $tiers ); ?>
			<?php if ( $position_notes ) : ?>
				<div class="tbr-layout-notes"><strong><?php esc_html_e( 'Seat position notes', 'ticket-booking-reviews' ); ?></strong><pre><?php echo esc_html( $position_notes ); ?></pre></div>
			<?php endif; ?>
			<form method="post" action="<?php echo esc_url( $product_id && function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '' ); ?>">
				<?php if ( 'movie_theater' === $type && 'front' === $screen ) : ?>
					<div class="tbr-screen"><?php esc_html_e( 'Screen', 'ticket-booking-reviews' ); ?></div>
				<?php endif; ?>
				<?php if ( 'cricket_match' === $type && $ends ) : ?>
					<div class="tbr-pavilion-ends"><?php echo esc_html( $ends ); ?></div>
				<?php endif; ?>
				<div class="tbr-seat-map" role="group" aria-label="<?php esc_attr_e( 'Choose seats', 'ticket-booking-reviews' ); ?>">
					<?php for ( $row = 1; $row <= $rows; $row++ ) : $row_label = $this->get_row_label( $row ); ?>
						<div class="tbr-seat-row" data-section="<?php echo esc_attr( $this->find_layout_label_for_row( $row_label, $sections ) ); ?>" data-tier="<?php echo esc_attr( $this->find_layout_label_for_row( $row_label, $tiers ) ); ?>"><span class="tbr-row-label"><?php echo esc_html( $row_label ); ?></span>
						<?php for ( $col = 1; $col <= $cols; $col++ ) : $seat = $row_label . $col; $is_blocked = in_array( $seat, $blocked, true ); ?>
							<label class="tbr-seat <?php echo $is_blocked ? 'is-blocked' : ''; ?>"><input type="checkbox" name="tbr_seats[]" value="<?php echo esc_attr( $seat ); ?>" <?php disabled( $is_blocked ); ?> /><?php echo esc_html( $seat ); ?></label>
						<?php endfor; ?></div>
					<?php endfor; ?>
				</div>
				<?php if ( 'movie_theater' === $type && 'back' === $screen ) : ?>
					<div class="tbr-screen"><?php esc_html_e( 'Screen', 'ticket-booking-reviews' ); ?></div>
				<?php endif; ?>
				<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product_id ); ?>" />
				<input type="hidden" name="tbr_event_id" value="<?php echo esc_attr( $event_id ); ?>" />
				<button type="submit" class="button tbr-book-button" <?php disabled( ! $product_id ); ?>><?php esc_html_e( 'Book selected tickets', 'ticket-booking-reviews' ); ?></button>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render a frontend list of upcoming ticket events.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_events_list_shortcode( $atts ) {
		$atts  = shortcode_atts( array( 'limit' => 12 ), $atts, 'ticket_booking_events' );
		$query = new WP_Query(
			array(
				'post_type'      => 'tbr_event',
				'posts_per_page' => max( 1, absint( $atts['limit'] ) ),
				'post_status'    => 'publish',
				'meta_key'       => '_tbr_starts_at',
				'orderby'        => 'meta_value',
				'order'          => 'ASC',
			)
		);

		ob_start();
		?>
		<div class="tbr-events-list">
			<?php while ( $query->have_posts() ) : $query->the_post(); ?>
				<article class="tbr-event-card">
					<?php if ( has_post_thumbnail() ) : ?>
						<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'medium' ); ?></a>
					<?php endif; ?>
					<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
					<p><?php echo esc_html( get_post_meta( get_the_ID(), '_tbr_venue', true ) ); ?></p>
					<p><?php echo esc_html( get_post_meta( get_the_ID(), '_tbr_starts_at', true ) ); ?></p>
					<a class="button" href="<?php the_permalink(); ?>"><?php esc_html_e( 'View tickets', 'ticket-booking-reviews' ); ?></a>
				</article>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/** Add selected seats to WooCommerce cart item. */
	public function add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
		if ( isset( $_POST['tbr_event_id'] ) ) {
			$event_id                      = absint( $_POST['tbr_event_id'] );
			$blocked                       = array_filter( array_map( 'trim', explode( ',', (string) get_post_meta( $event_id, '_tbr_blocked_seats', true ) ) ) );
			$selected_seats                = isset( $_POST['tbr_seats'] ) ? array_map( 'sanitize_text_field', wp_unslash( (array) $_POST['tbr_seats'] ) ) : array();
			$cart_item_data['tbr_event_id'] = $event_id;
			$cart_item_data['tbr_seats']    = array_values( array_diff( $selected_seats, $blocked ) );
			$cart_item_data['unique_key']    = md5( wp_json_encode( $cart_item_data ) . microtime() );
		}
		return $cart_item_data;
	}

	/** Display booking data in cart and checkout. */
	public function display_cart_item_data( $item_data, $cart_item ) {
		if ( ! empty( $cart_item['tbr_event_id'] ) ) {
			$item_data[] = array( 'name' => __( 'Event', 'ticket-booking-reviews' ), 'value' => get_the_title( absint( $cart_item['tbr_event_id'] ) ) );
		}
		if ( ! empty( $cart_item['tbr_seats'] ) ) {
			$item_data[] = array( 'name' => __( 'Seats', 'ticket-booking-reviews' ), 'value' => implode( ', ', array_map( 'sanitize_text_field', (array) $cart_item['tbr_seats'] ) ) );
		}
		return $item_data;
	}

	/** Persist booking data to order items. */
	public function add_order_line_item_meta( $item, $cart_item_key, $values, $order ) {
		if ( ! empty( $values['tbr_event_id'] ) ) {
			$item->add_meta_data( __( 'Ticket Event', 'ticket-booking-reviews' ), get_the_title( absint( $values['tbr_event_id'] ) ), true );
		}
		if ( ! empty( $values['tbr_seats'] ) ) {
			$item->add_meta_data( __( 'Seats', 'ticket-booking-reviews' ), implode( ', ', array_map( 'sanitize_text_field', (array) $values['tbr_seats'] ) ), true );
		}
	}

	/** Render star rating field for event reviews. */
	public function render_review_rating_field() {
		if ( ! is_singular( 'tbr_event' ) ) {
			return;
		}
		echo '<p class="comment-form-tbr-rating"><label for="tbr-rating">' . esc_html__( 'Event rating', 'ticket-booking-reviews' ) . '</label><select id="tbr-rating" name="tbr_rating"><option value="5">5</option><option value="4">4</option><option value="3">3</option><option value="2">2</option><option value="1">1</option></select></p>';
	}

	/** Save star rating with comments. */
	public function save_review_rating( $comment_id ) {
		if ( isset( $_POST['tbr_rating'] ) ) {
			update_comment_meta( $comment_id, 'tbr_rating', min( 5, max( 1, absint( $_POST['tbr_rating'] ) ) ) );
		}
	}
}
