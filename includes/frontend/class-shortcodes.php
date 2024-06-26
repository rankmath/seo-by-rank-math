<?php
/**
 * The Shortcodes of the plugin.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Frontend
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Frontend;

use RankMath\Helper;
use RankMath\Paper\Paper;
use RankMath\Traits\Hooker;
use RankMath\Traits\Shortcode;
use RankMath\Helpers\Arr;

defined( 'ABSPATH' ) || exit;

/**
 * Shortcodes class.
 */
class Shortcodes {

	use Hooker, Shortcode;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->action( 'init', 'init' );
	}

	/**
	 * Initialize.
	 */
	public function init() {

		// Remove Yoast shortcodes.
		$this->remove_shortcode( 'wpseo_address' );
		$this->remove_shortcode( 'wpseo_map' );
		$this->remove_shortcode( 'wpseo_opening_hours' );
		$this->remove_shortcode( 'wpseo_breadcrumb' );
		$this->remove_shortcode( 'aioseo_breadcrumbs' );

		// Add Yoast compatibility shortcodes.
		$this->add_shortcode( 'wpseo_address', 'yoast_address' );
		$this->add_shortcode( 'wpseo_map', 'yoast_map' );
		$this->add_shortcode( 'wpseo_opening_hours', 'yoast_opening_hours' );
		$this->add_shortcode( 'wpseo_breadcrumb', 'breadcrumb' );
		$this->add_shortcode( 'aioseo_breadcrumbs', 'breadcrumb' );

		// Add the Contact shortcode.
		$this->add_shortcode( 'rank_math_contact_info', 'contact_info' );

		// Add the Breadcrumbs shortcode.
		$this->add_shortcode( 'rank_math_breadcrumb', 'breadcrumb' );
	}

	/**
	 * Get the breadcrumbs.
	 *
	 * @param array $args Arguments.
	 *
	 * @return string
	 */
	public function breadcrumb( $args ) {
		if ( ! Helper::is_breadcrumbs_enabled() ) {
			return;
		}
		return Breadcrumbs::get()->get_breadcrumb( $args );
	}

	/**
	 * Contact info shortcode, displays nicely formatted contact informations.
	 *
	 * @param  array $args Optional. Shortcode arguments - currently only 'show'
	 *                     parameter, which is a comma-separated list of elements to show.
	 * @return string Shortcode output.
	 */
	public function contact_info( $args ) {
		$args = shortcode_atts(
			[
				'show'  => 'all',
				'class' => '',
			],
			$args,
			'contact-info'
		);

		$allowed = $this->get_allowed_info( $args );

		wp_enqueue_style( 'rank-math-contact-info', rank_math()->assets() . 'css/rank-math-contact-info.css', null, rank_math()->version );

		ob_start();
		echo '<div class="' . esc_attr( $this->get_contact_classes( $allowed, $args['class'] ) ) . '">';

		foreach ( $allowed as $element ) {
			$method = 'display_' . $element;
			if ( method_exists( $this, $method ) ) {
				echo '<div class="rank-math-contact-section rank-math-contact-' . esc_attr( $element ) . '">';
				$this->$method();
				echo '</div>';
			}
		}

		echo '</div>';
		echo '<div class="clear"></div>';

		/**
		 * Change the Contact Info HTML output.
		 *
		 * @param string $unsigned HTML output.
		 */
		return $this->do_filter( 'contact_info/html', ob_get_clean() );
	}

	/**
	 * Get allowed info array.
	 *
	 * @param array $args Shortcode arguments - currently only 'show'.
	 *
	 * @return array
	 */
	private function get_allowed_info( $args ) {
		$type = Helper::get_settings( 'titles.knowledgegraph_type' );

		$allowed = 'person' === $type
		? [ 'name', 'email', 'person_phone', 'address' ]
		: [ 'name', 'organization_description', 'email', 'address', 'hours', 'phone', 'additional_info', 'map' ];

		if ( ! empty( $args['show'] ) && 'all' !== $args['show'] ) {
			$allowed = array_intersect( Arr::from_string( $args['show'] ), $allowed );
		}

		return $allowed;
	}

	/**
	 * Get contact info container classes.
	 *
	 * @param  array $allowed     Allowed elements.
	 * @param  array $extra_class Shortcode arguments.
	 * @return string
	 */
	private function get_contact_classes( $allowed, $extra_class ) {
		$classes = [ 'rank-math-contact-info', $extra_class ];
		foreach ( $allowed as $elem ) {
			$classes[] = sanitize_html_class( 'show-' . $elem );
		}
		if ( count( $allowed ) === 1 ) {
			$classes[] = sanitize_html_class( 'show-' . $elem . '-only' );
		}

		return join( ' ', array_filter( $classes ) );
	}

	/**
	 * Output address.
	 */
	private function display_address() {
		$address = Helper::get_settings( 'titles.local_address' );
		if ( false === $address ) {
			return;
		}

		$format = nl2br( Helper::get_settings( 'titles.local_address_format' ) );
		/**
		 * Allow developer to change the address part format.
		 *
		 * @param string $parts_format String format to output the address part.
		 */
		$parts_format = $this->do_filter( 'shortcode/contact/address_parts_format', '<span class="contact-address-%1$s">%2$s</span>' );

		$hash = [
			'streetAddress'   => 'address',
			'addressLocality' => 'locality',
			'addressRegion'   => 'region',
			'postalCode'      => 'postalcode',
			'addressCountry'  => 'country',
		];
		?>
		<label><?php esc_html_e( 'Address:', 'rank-math' ); ?></label>
		<address>
			<?php
			foreach ( $hash as $key => $tag ) {
				$value = '';
				if ( isset( $address[ $key ] ) && ! empty( $address[ $key ] ) ) {
					$value = sprintf( $parts_format, $tag, $address[ $key ] );
				}

				$format = str_replace( "{{$tag}}", $value, $format );
			}

			echo wp_kses_post( $format );
			?>
		</address>
		<?php
	}

	/**
	 * Output opening hours.
	 */
	private function display_hours() {
		$hours = Helper::get_settings( 'titles.opening_hours' );
		if ( ! isset( $hours[0]['time'] ) ) {
			return;
		}

		$combined = $this->get_hours_combined( $hours );
		$format   = Helper::get_settings( 'titles.opening_hours_format' );
		?>
		<label><?php esc_html_e( 'Hours:', 'rank-math' ); ?></label>
		<div class="rank-math-contact-hours-details">
			<?php
			foreach ( $combined as $time => $days ) {
				if ( $format ) {
					$hours = explode( '-', $time );
					$time  = isset( $hours[1] ) ? date_i18n( 'g:i a', strtotime( $hours[0] ) ) . '-' . date_i18n( 'g:i a', strtotime( $hours[1] ) ) : $time;
				}
				$time = str_replace( '-', ' &ndash; ', $time );

				printf(
					'<div class="rank-math-opening-hours"><span class="rank-math-opening-days">%1$s</span><span class="rank-math-opening-time">%2$s</span></div>',
					esc_html( join( ', ', $days ) ),
					esc_html( $time )
				);
			}
			?>
		</div>
		<?php
	}

	/**
	 * Combine hours in an hour
	 *
	 * @param  array $hours Hours to combine.
	 * @return array
	 */
	private function get_hours_combined( $hours ) {
		$combined = [];

		foreach ( $hours as $hour ) {
			if ( empty( $hour['time'] ) ) {
				continue;
			}

			$combined[ trim( $hour['time'] ) ][] = $this->get_localized_day( $hour['day'] );
		}

		return $combined;
	}

	/**
	 * Retrieve the full translated weekday word.
	 *
	 * @param string $day Day to translate.
	 *
	 * @return string
	 */
	private function get_localized_day( $day ) {
		global $wp_locale;

		$hash = [
			'Sunday'    => 0,
			'Monday'    => 1,
			'Tuesday'   => 2,
			'Wednesday' => 3,
			'Thursday'  => 4,
			'Friday'    => 5,
			'Saturday'  => 6,
		];

		return $wp_locale->get_weekday( $hash[ $day ] );
	}

	/**
	 * Output phone numbers.
	 */
	private function display_phone() {
		$phones = Helper::get_settings( 'titles.phone_numbers' );
		if ( empty( $phones ) ) {
			return;
		}

		$choices = Helper::choices_phone_types();
		foreach ( $phones as $phone ) :
			if ( empty( $phone['number'] ) ) {
				continue;
			}

			$number = esc_html( $phone['number'] );
			$label  = isset( $choices[ $phone['type'] ] ) ? $choices[ $phone['type'] ] : ''
			?>
			<div class="rank-math-phone-number type-<?php echo sanitize_html_class( $phone['type'] ); ?>">
				<label><?php echo esc_html( $label ); ?>:</label>
				<span><?php echo isset( $phone['number'] ) ? '<a href="tel://' . esc_attr( $number ) . '">' . esc_html( $number ) . '</a>' : ''; ?></span>
			</div>
			<?php
		endforeach;
	}

	/**
	 * Output Person phone number.
	 */
	private function display_person_phone() {
		$phone = Helper::get_settings( 'titles.phone' );
		if ( empty( $phone ) ) {
			return;
		}
		?>
			<div class="rank-math-phone-numberx">
				<label><?php echo esc_html__( 'Telephone', 'rank-math' ); ?>:</label>
				<span><a href="tel://<?php echo esc_attr( $phone ); ?>"><?php echo esc_html( $phone ); ?></a></span>
			</div>
		<?php
	}

	/**
	 * Output google map.
	 */
	private function display_map() {
		$api_key = Helper::get_settings( 'titles.maps_api_key' );
		if ( ! $api_key ) {
			return;
		}

		$address = Helper::get_settings( 'titles.local_address' );
		if ( false === $address ) {
			return;
		}

		/**
		 * Filter address for Google Map in contact shortcode.
		 *
		 * @param string $address
		 */
		$address = $this->do_filter( 'shortcode/contact/map_address', implode( ' ', $address ) );
		$address = $this->do_filter( 'shortcode/contact/map_iframe_src', '//maps.google.com/maps?q=' . rawurlencode( $address ) . '&z=15&output=embed&key=' . rawurlencode( $api_key ) );
		?>
		<iframe src="<?php echo esc_url( $address ); ?>"></iframe>
		<?php
	}

	/**
	 * Output name.
	 */
	private function display_name() {
		$name = Helper::get_settings( 'titles.knowledgegraph_name' );
		if ( false === $name ) {
			return;
		}

		$url = Helper::get_settings( 'titles.url' );
		?>
		<h4 class="rank-math-name">
			<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $name ); ?></a>
		</h4>
		<?php
	}

	/**
	 * Output email.
	 */
	private function display_email() {
		$email = Helper::get_settings( 'titles.email' );
		if ( false === $email ) {
			return;
		}
		?>
		<div class="rank-math-email">
			<label><?php esc_html_e( 'Email:', 'rank-math' ); ?></label>
			<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
		</div>
		<?php
	}

	/**
	 * Output Organization description.
	 */
	private function display_organization_description() {
		$description = Helper::get_settings( 'titles.organization_description' );
		if ( ! $description ) {
			return;
		}
		?>
		<div class="rank-math-organization-description">
			<label><?php esc_html_e( 'Description:', 'rank-math' ); ?></label>
			<p><?php echo esc_html( $description ); ?></p>
		</div>
		<?php
	}

	/**
	 * Output Additional Organization details.
	 */
	private function display_additional_info() {
		$properties = Helper::get_settings( 'titles.additional_info' );
		if ( empty( $properties ) ) {
			return;
		}

		$choices = Helper::choices_additional_organization_info();

		foreach ( $properties as $property ) {
			if ( empty( $property['value'] ) ) {
				continue;
			}
			?>
			<div class="rank-math-organization-additional-details">
				<label><?php echo esc_html( $choices[ $property['type'] ] ); ?>:</label>
				<span><?php echo esc_html( $property['value'] ); ?></span>
			</div>
			<?php
		}
	}

	/**
	 * Yoast address compatibility functionality.
	 *
	 * @param  array $args Array of arguments.
	 * @return string
	 */
	public function yoast_address( $args ) {
		$atts = shortcode_atts(
			[
				'hide_name'          => '0',
				'hide_address'       => '0',
				'show_state'         => '1',
				'show_country'       => '1',
				'show_phone'         => '1',
				'show_phone_2'       => '1',
				'show_fax'           => '1',
				'show_email'         => '1',
				'show_url'           => '0',
				'show_vat'           => '0',
				'show_tax'           => '0',
				'show_coc'           => '0',
				'show_price_range'   => '0',
				'show_logo'          => '0',
				'show_opening_hours' => '0',
			],
			$args,
			'wpseo_address'
		);
		$show = [ 'address' ];

		if ( 1 === absint( $atts['show_phone'] ) ) {
			$show[] = 'phone';
		}

		if ( 1 === absint( $atts['show_opening_hours'] ) ) {
			$show[] = 'hours';
		}

		return $this->contact_info(
			[
				'show'  => join( ',', $show ),
				'class' => 'wpseo_address_compat',
			]
		);
	}

	/**
	 * Yoast map compatibility functionality.
	 *
	 * @param  array $args Array of arguments.
	 * @return string
	 */
	public function yoast_map( $args ) {
		return $this->contact_info(
			[
				'show'  => 'map',
				'class' => 'wpseo_map_compat',
			]
		);
	}

	/**
	 * Yoast opening hours compatibility functionality.
	 *
	 * @param  array $args Array of arguments.
	 * @return string
	 */
	public function yoast_opening_hours( $args ) {
		return $this->contact_info(
			[
				'show'  => 'hours',
				'class' => 'wpseo_opening_hours_compat',
			]
		);
	}
}
