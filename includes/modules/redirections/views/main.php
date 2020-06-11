<?php
/**
 * Redirection main view.
 *
 * @package    RankMath
 * @subpackage RankMath\Redirections
 */

use RankMath\KB;
use RankMath\Helper;

$redirections = Helper::get_module( 'redirections' )->admin;
$redirections->table->prepare_items();

$is_new     = isset( $_GET['new'] );
$is_editing = ! empty( $_GET['url'] ) || ! empty( $_GET['urls'] ) || ! empty( $_REQUEST['log'] ) || ! empty( $_REQUEST['redirect_uri'] ) || $redirections->form->is_editing();
?>
<div class="wrap rank-math-redirections-wrap">

	<h1 class="wp-heading-inline">
		<?php echo esc_html( get_admin_page_title() ); ?>
		<a class="rank-math-add-new-redirection<?php echo $is_editing ? '-refresh' : ''; ?> page-title-action" href="<?php echo Helper::get_admin_url( 'redirections', 'new=1' ); ?>"><?php esc_html_e( 'Add New', 'rank-math' ); ?></a>
		<a class="page-title-action" href="<?php echo wp_nonce_url( Helper::get_admin_url( 'redirections', 'export=apache' ), 'rank-math-export-redirections' ); ?>"><?php esc_html_e( 'Export to .htaccess', 'rank-math' ); ?></a>
		<a class="page-title-action" href="<?php echo wp_nonce_url( Helper::get_admin_url( 'redirections', 'export=nginx' ), 'rank-math-export-redirections' ); ?>"><?php esc_html_e( 'Export to Nginx config file', 'rank-math' ); ?></a>
		<a class="page-title-action" href="<?php KB::the( 'redirections' ); ?>" target="_blank"><?php esc_html_e( 'Learn More', 'rank-math' ); ?></a>
		<a class="page-title-action" href="<?php echo Helper::get_admin_url( 'options-general#setting-panel-redirections' ); ?>"><?php esc_html_e( 'Settings', 'rank-math' ); ?></a>
	</h1>

	<div class="clear"></div>

	<div class="rank-math-redirections-form<?php echo $is_editing || $is_new ? ' is-editing' : ''; ?> rank-math-page rank-math-box">

		<?php $redirections->form->display(); ?>

	</div>

	<form method="get">
		<input type="hidden" name="page" value="rank-math-redirections">
		<?php $redirections->table->search_box( esc_html__( 'Search', 'rank-math' ), 's' ); ?>
	</form>

	<form method="post">
	<?php
		$redirections->table->views();
		$redirections->table->display();
	?>
	</form>

</div>
