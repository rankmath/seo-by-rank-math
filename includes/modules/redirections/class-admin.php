<?php
/**
 * The Redirections module.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Redirections
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Redirections;

use RankMath\KB;
use RankMath\Helper;
use RankMath\Helpers\Arr;
use RankMath\Helpers\Param;
use RankMath\Module\Base;
use RankMath\Traits\Ajax;
use RankMath\Traits\Hooker;
use RankMath\Admin\Admin_Helper;
use RankMath\Admin\Page;
use RankMath\Monitor\DB as Monitor_DB;

defined( 'ABSPATH' ) || exit;

/**
 * Admin class.
 */
class Admin extends Base {

	use Ajax;
	use Hooker;

	/**
	 * Module ID.
	 *
	 * @var string
	 */
	public $id = '';

	/**
	 * Module directory.
	 *
	 * @var string
	 */
	public $directory = '';

	/**
	 * List table object.
	 *
	 * @var object
	 */
	public $table;

	/**
	 * Screen options.
	 *
	 * @var array
	 */
	public $screen_options = [];

	/**
	 * Module page.
	 *
	 * @var object
	 */
	public $page;

	/**
	 * Admin object.
	 *
	 * @var Admin
	 */
	public $admin;

	/**
	 * Import/Export object.
	 *
	 * @var Import_Export
	 */
	public $import_export;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$directory = __DIR__;
		$this->config(
			[
				'id'             => 'redirect',
				'directory'      => $directory,
				'table'          => 'RankMath\Redirections\Table',
				'screen_options' => [
					'id'      => 'rank_math_redirections_per_page',
					'default' => 100,
				],
			]
		);
		parent::__construct();

		$this->ajax_hooks();
		$this->load_metabox();

		if ( Helper::has_cap( 'redirections' ) ) {
			$this->filter( 'rank_math/settings/general', 'add_settings' );
		}

		if ( $this->page->is_current_page() || 'rank_math_save_redirections' === Param::post( 'action' ) ) {
			$this->import_export = new Import_Export();
			$this->import_export->hooks();
		}

		if ( $this->page->is_current_page() ) {
			new Export();
			$this->action( 'init', 'init', 21 );
		}

		add_action( 'rank_math/redirection/clean_trashed', 'RankMath\\Redirections\\DB::periodic_clean_trash' );
	}

	/**
	 * Load metabox.
	 */
	private function load_metabox() {
		if ( Admin_Helper::is_post_edit() || Admin_Helper::is_term_edit() ) {
			new Metabox();
		}
	}

	/**
	 * Hooks for ajax.
	 */
	private function ajax_hooks() {
		if ( ! Helper::is_ajax() ) {
			return;
		}

		$this->ajax( 'redirection_delete', 'handle_ajax' );
		$this->ajax( 'redirection_activate', 'handle_ajax' );
		$this->ajax( 'redirection_deactivate', 'handle_ajax' );
		$this->ajax( 'redirection_trash', 'handle_ajax' );
		$this->ajax( 'redirection_restore', 'handle_ajax' );
	}

	/**
	 * Register admin page.
	 */
	public function register_admin_page() {

		$dir = $this->directory . '/views/';
		$uri = untrailingslashit( plugin_dir_url( __FILE__ ) );

		$this->page = new Page(
			'rank-math-redirections',
			esc_html__( 'Redirections', 'rank-math' ),
			[
				'position'   => 40,
				'parent'     => 'rank-math',
				'capability' => 'rank_math_redirections',
				'render'     => $dir . 'main.php',
				'help'       => [
					'redirections-overview'       => [
						'title' => esc_html__( 'Overview', 'rank-math' ),
						'view'  => $dir . 'help-tab-overview.php',
					],
					'redirections-screen-content' => [
						'title' => esc_html__( 'Screen Content', 'rank-math' ),
						'view'  => $dir . 'help-tab-screen-content.php',
					],
					'redirections-actions'        => [
						'title' => esc_html__( 'Available Actions', 'rank-math' ),
						'view'  => $dir . 'help-tab-actions.php',
					],
					'redirections-bulk'           => [
						'title' => esc_html__( 'Bulk Actions', 'rank-math' ),
						'view'  => $dir . 'help-tab-bulk.php',
					],
				],
				'assets'     => [
					'styles'  => [
						'wp-components'          => '',
						'rank-math-common'       => '',
						'rank-math-redirections' => $uri . '/assets/css/redirections.css',
					],
					'scripts' => [
						'wp-element'             => '',
						'wp-components'          => '',
						'rank-math-common'       => '',
						'rank-math-components'   => '',
						'rank-math-redirections' => $uri . '/assets/js/redirections.js',
					],
					'json'    => [
						'isNew' => Param::get( 'new' ),
					],
				],
			]
		);
	}

	/**
	 * Add module settings in the General Settings panel.
	 *
	 * @param  array $tabs Array of option panel tabs.
	 * @return array
	 */
	public function add_settings( $tabs ) {

		/**
		 * Allow developers to change number of redirections to process at once.
		 *
		 * @param int $number
		 */
		Helper::add_json( 'redirectionPastedContent', $this->do_filter( 'redirections/pastedContent', 100 ) );

		Arr::insert(
			$tabs,
			[
				'redirections' => [
					'icon'  => 'rm-icon rm-icon-redirection',
					'title' => esc_html__( 'Redirections', 'rank-math' ),
					/* translators: Link to kb article */
					'desc'  => sprintf( esc_html__( 'Easily create redirects without fiddling with tedious code. %s.', 'rank-math' ), '<a href="' . KB::get( 'redirections-settings', 'Options Panel Redirections Tab' ) . '" target="_blank">' . esc_html__( 'Learn more', 'rank-math' ) . '</a>' ),
					'file'  => $this->directory . '/views/options.php',
					'json'  => [
						'redirectionTypes' => Helper::choices_redirection_types(),
					],
				],
			],
			8
		);

		return $tabs;
	}

	/**
	 * Initialize module actions.
	 */
	public function init() {
		Helper::add_json( 'data', $this->get_default_redirection() );
		if ( ! empty( $_REQUEST['delete_all'] ) ) {
			check_admin_referer( 'bulk-redirections' );
			DB::clear_trashed();
			return;
		}

		$action = Helper::get_request_action();
		if ( false === $action || empty( $_REQUEST['redirection'] ) || 'edit' === $action ) {
			return;
		}

		check_admin_referer( 'bulk-redirections' );

		$ids = (array) wp_parse_id_list( $_REQUEST['redirection'] );
		if ( empty( $ids ) ) {
			Helper::add_notification( __( 'No valid ID found.', 'rank-math' ) );
			return;
		}

		$notice = $this->perform_action( $action, $ids );
		if ( $notice ) {
			Helper::add_notification( $notice, [ 'type' => 'success' ] );
			return;
		}
	}

	/**
	 * Handle AJAX request.
	 */
	public function handle_ajax() {
		$action = Helper::get_request_action();
		if ( false === $action ) {
			return;
		}

		check_ajax_referer( 'redirection_list_action', 'security' );
		$this->has_cap_ajax( 'redirections' );

		$id     = Param::request( 'redirection', 0, FILTER_VALIDATE_INT );
		$action = str_replace( 'rank_math_redirection_', '', $action );

		if ( ! $id ) {
			$this->error( esc_html__( 'No valid ID found.', 'rank-math' ) );
		}

		$notice = $this->perform_action( $action, $id );
		if ( $notice ) {
			$this->success( $notice );
		}

		$this->error( esc_html__( 'No valid action found.', 'rank-math' ) );
	}

	/**
	 * Output page title actions.
	 *
	 * @param bool $is_editing User is editing a redirection.
	 * @return void
	 */
	public function page_title_actions( $is_editing ) {
		$actions = [
			'add'           => [
				'class' => 'page-title-action rank-math-add-new-redirection' . ( $is_editing ? '-refresh' : '' ),
				'href'  => Helper::get_admin_url( 'redirections', 'new=1' ),
				'label' => __( 'Add New', 'rank-math' ),
			],
			'import_export' => [
				'class' => 'page-title-action',
				'href'  => Helper::get_admin_url( 'redirections', 'importexport=1' ),
				'label' => __( 'Export Options', 'rank-math' ),
			],
			'learn_more'    => [
				'class' => 'page-title-action',
				'href'  => KB::get( 'redirections', 'SW Redirection Step' ),
				'label' => __( 'Learn More', 'rank-math' ),
			],
			'settings'      => [
				'class' => 'page-title-action',
				'href'  => Helper::get_settings_url( 'general', 'redirections' ),
				'label' => __( 'Settings', 'rank-math' ),
			],
		];

		$actions = $this->do_filter( 'redirections/page_title_actions', $actions, $is_editing );

		foreach ( $actions as $action_name => $action ) {
			?>
				<a class="<?php echo esc_attr( $action['class'] ); ?> rank-math-redirections-<?php echo esc_attr( $action_name ); ?>" href="<?php echo esc_attr( $action['href'] ); ?>" target="<?php echo $action_name === 'learn_more' ? '_blank' : ''; ?>"><?php echo esc_attr( $action['label'] ); ?></a>
			<?php
		}
	}

	/**
	 * Get default Redirection to show on a new Redirection form.
	 *
	 * @return array
	 */
	private function get_default_redirection() {
		$redirection = Param::get( 'redirection' );
		if ( $redirection ) {
			return $this->do_filter( 'redirections/table_item', DB::get_redirection_by_id( $redirection ) );
		}

		$url = Param::get( 'url' );
		if ( $url ) {
			return [
				'header_code' => '301',
				'status'      => 'active',
				'sources'     => [
					[
						'pattern'    => esc_attr( $url ),
						'comparison' => 'exact',
					],
				],
			];
		}

		$urls = Param::get( 'urls', false, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		if ( $urls ) {
			$urls    = array_map( 'esc_attr', $urls );
			$sources = [];
			foreach ( $urls as $url ) {
				$sources[] = [
					'pattern'    => $url,
					'comparison' => 'exact',
				];
			}

			return [
				'header_code' => '301',
				'status'      => 'active',
				'sources'     => $sources,
			];
		}

		if ( ! empty( $_REQUEST['log'] ) && is_array( $_REQUEST['log'] ) ) {
			return [
				'header_code' => '301',
				'status'      => 'active',
				'sources'     => $this->get_sources_for_log(),
				'url_to'      => esc_url( home_url( '/' ) ),
			];
		}

		return false;
	}

	/**
	 * Get sources for 404 log items.
	 *
	 * @return array
	 */
	private function get_sources_for_log() {
		$logs = isset( $_REQUEST['log'] ) ? array_map( 'absint', $_REQUEST['log'] ) : [];
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
			$sources[] = [
				'pattern'    => $log['uri'],
				'comparison' => 'exact',
			];
		}

		return $sources;
	}

	/**
	 * Perform action on database.
	 *
	 * @param  string        $action Action to perform.
	 * @param  integer|array $ids    Rows to perform on.
	 * @return string
	 */
	private function perform_action( $action, $ids ) {
		$status  = [
			'activate'   => 'active',
			'deactivate' => 'inactive',
			'trash'      => 'trashed',
			'restore'    => 'active',
		];
		$message = [
			'activate'   => esc_html__( 'Redirection successfully activated.', 'rank-math' ),
			'deactivate' => esc_html__( 'Redirection successfully deactivated.', 'rank-math' ),
			'trash'      => esc_html__( 'Redirection successfully moved to Trash.', 'rank-math' ),
			'restore'    => esc_html__( 'Redirection successfully restored.', 'rank-math' ),
		];

		if ( isset( $status[ $action ] ) ) {
			DB::change_status( $ids, $status[ $action ] );
			return $message[ $action ];
		}

		if ( 'delete' === $action ) {
			$count = DB::delete( $ids );
			if ( $count > 0 ) {
				/* translators: delete counter */
				return sprintf( esc_html__( '%d redirection(s) successfully deleted.', 'rank-math' ), $count );
			}
		}

		return false;
	}
}
