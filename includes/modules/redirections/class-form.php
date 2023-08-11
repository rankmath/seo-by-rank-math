<?php
/**
 * The Redirection add/edit form.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Redirections
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Redirections;

use RankMath\Helper;
use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\Param;
use RankMath\Monitor\DB as Monitor_DB;

defined( 'ABSPATH' ) || exit;

/**
 * Form class.
 */
class Form {

	use Hooker;

	/**
	 * The hooks.
	 */
	public function hooks() {
		$this->action( 'cmb2_admin_init', 'register_form' );
		$this->filter( 'cmb2_override_option_get_rank-math-redirections', 'set_options' );
		$this->action( 'admin_post_rank_math_save_redirections', 'save' );
	}

	/**
	 * Display form.
	 */
	public function display() {
		?>
		<h2><strong><?php echo ( $this->is_editing() ? esc_html__( 'Update', 'rank-math' ) : esc_html__( 'Add', 'rank-math' ) ) . ' ' . esc_html( get_admin_page_title() ); ?></strong></h2>

		<form class="cmb-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
			<input type="hidden" name="action" value="rank_math_save_redirections">
			<?php
				wp_nonce_field( 'rank-math-save-redirections', 'security' );
				$cmb = cmb2_get_metabox( 'rank-math-redirections', 'rank-math-redirections' );
				$cmb->show_form();
			?>
			<footer class="form-footer rank-math-ui">
				<button type="button" class="button button-secondary button-link-delete alignleft"><?php esc_html_e( 'Cancel', 'rank-math' ); ?></button>
				<button type="submit" class="button button-primary"><?php echo $this->is_editing() ? esc_html__( 'Update Redirection', 'rank-math' ) : esc_html__( 'Add Redirection', 'rank-math' ); ?></button>
			</footer>
		</form>

		<?php
	}

	/**
	 * Create CMB.
	 *
	 * @return CMB2
	 */
	private function create_box() {
		return new_cmb2_box(
			[
				'id'           => 'rank-math-redirections',
				'object_types' => [ 'options-page' ],
				'option_key'   => 'rank-math-redirections',
				'hookup'       => false,
				'save_fields'  => false,
			]
		);
	}

	/**
	 * Register form for Add New Record.
	 */
	public function register_form() {
		$cmb = $this->create_box();

		$cmb->add_field(
			[
				'id'      => 'sources',
				'type'    => 'group',
				'name'    => esc_html__( 'Source URLs', 'rank-math' ),
				'options' => [
					'add_button'    => esc_html__( 'Add another', 'rank-math' ),
					'remove_button' => esc_html__( 'Remove', 'rank-math' ),
				],
				'classes' => 'cmb-group-text-only',
				'fields'  => [
					[
						'id'              => 'pattern',
						'type'            => 'text',
						'escape_cb'       => [ $this, 'escape_sources' ],
						'sanitization_cb' => false,
					],
					[
						'id'      => 'comparison',
						'type'    => 'select',
						'options' => Helper::choices_comparison_types(),
					],
					[
						'id'   => 'ignore',
						'type' => 'checkbox',
						'desc' => esc_html__( 'Ignore Case', 'rank-math' ),
					],
				],
			]
		);

		$cmb->add_field(
			[
				'id'   => 'url_to',
				'type' => 'text_url',
				'name' => esc_html__( 'Destination URL', 'rank-math' ),
			]
		);

		$cmb->add_field(
			[
				'id'      => 'header_code',
				'type'    => 'radio_inline',
				'name'    => esc_html__( 'Redirection Type', 'rank-math' ),
				'options' => Helper::choices_redirection_types(),
				'default' => Helper::get_settings( 'general.redirections_header_code' ),
			]
		);

		$cmb->add_field(
			[
				'id'      => 'status',
				'type'    => 'radio_inline',
				'name'    => esc_html__( 'Status', 'rank-math' ),
				'options' => [
					'active'   => esc_html__( 'Activate', 'rank-math' ),
					'inactive' => esc_html__( 'Deactivate', 'rank-math' ),
				],
				'default' => 'active',
			]
		);

		$cmb->add_field(
			[
				'id'   => 'id',
				'type' => 'hidden',
			]
		);
	}

	/**
	 * Set option handler for form.
	 *
	 * @param array $opts Array of options.
	 */
	public function set_options( $opts ) {
		// If editing previous record.
		$redirection_id = $this->is_editing();
		if ( $redirection_id ) {
			return DB::get_redirection_by_id( $redirection_id );
		}

		$url = Param::get( 'url' );
		if ( $url ) {
			$url = esc_attr( $url );
			return [ 'sources' => [ [ 'pattern' => $url ] ] ];
		}

		$urls = Param::get( 'urls', false, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		if ( $urls ) {
			$urls   = array_map( 'esc_attr', $urls );
			$return = [ 'sources' => [] ];
			foreach ( $urls as $url ) {
				$return['sources'][] = [ 'pattern' => $url ];
			}
			return $return;
		}

		if ( ! empty( $_REQUEST['log'] ) && is_array( $_REQUEST['log'] ) ) {
			return [
				'sources' => $this->get_sources_for_log(),
				'url_to'  => esc_url( home_url( '/' ) ),
			];
		}

		return $opts;
	}

	/**
	 * Get sources for 404 log items.
	 *
	 * @return array
	 */
	private function get_sources_for_log() {
		$logs = array_map( 'absint', $_REQUEST['log'] );
		$logs = Monitor_DB::get_logs(
			[
				'ids'     => $logs,
				'orderby' => '',
				'limit'   => 1000,
			]
		);

		$sources = [];
		foreach ( $logs['logs'] as $log ) {
			if ( empty( $log['uri'] ) ) {
				continue;
			}
			$sources[] = [ 'pattern' => $log['uri'] ];
		}

		return $sources;
	}

	/**
	 * Save new record form submit handler.
	 */
	public function save() {
		// If no form submission, bail!
		if ( empty( $_POST ) ) {
			return false;
		}

		check_admin_referer( 'rank-math-save-redirections', 'security' );
		if ( ! Helper::has_cap( 'redirections' ) ) {
			return false;
		}

		$cmb    = cmb2_get_metabox( 'rank-math-redirections' );
		$values = $cmb->get_sanitized_values( $_POST );

		$redirection = Redirection::from( $values );

		if ( $redirection->is_infinite_loop() ) {
			if ( ! $redirection->get_id() ) {
				Helper::add_notification( __( 'The redirection you are trying to create may cause an infinite loop. Please check the source and destination URLs. The redirection has been deactivated.', 'rank-math' ), [ 'type' => 'error' ] );
				$redirection->set_status( 'inactive' );
			} else {
				Helper::add_notification( __( 'The redirection you are trying to update may cause an infinite loop. Please check the source and destination URLs.', 'rank-math' ), [ 'type' => 'error' ] );
			}
		}

		if ( false === $redirection->save() ) {
			Helper::add_notification( __( 'Please add at least one valid source URL.', 'rank-math' ), [ 'type' => 'error' ] );
			Helper::redirect( Param::post( '_wp_http_referer', false ) );
			exit;
		}

		$this->do_action( 'redirection/saved', $redirection );
		Helper::redirect( Helper::get_admin_url( 'redirections' ) );
		exit;
	}

	/**
	 * Is editing a record.
	 *
	 * @return int|boolean
	 */
	public function is_editing() {
		if ( 'edit' !== Param::get( 'action' ) ) {
			return false;
		}

		return Param::get( 'redirection', false, FILTER_VALIDATE_INT );
	}

	/**
	 * Stripslashes wrapper.
	 *
	 * @param  mixed      $value      The unescaped value from the database.
	 * @param  array      $field_args Array of field arguments.
	 * @param  CMB2_Field $field      The field object.
	 *
	 * @return mixed                  Escaped value to be displayed.
	 */
	public function escape_sources( $value, $field_args, $field ) {
		return esc_attr( \stripslashes( $value ) );
	}
}
