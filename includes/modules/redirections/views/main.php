<?php
/**
 * Redirection main view.
 *
 * @package    RankMath
 * @subpackage RankMath\Redirections
 */

use RankMath\Helper;
use RankMath\Helpers\Param;

defined( 'ABSPATH' ) || exit;

$redirections = Helper::get_module( 'redirections' )->admin;
$redirections->table->prepare_items();

$is_new     = (bool) Param::get( 'new' );
$is_editing = ! empty( Param::get( 'url' ) ) || ! empty( $_GET['urls'] ) || ! empty( $_REQUEST['log'] ) || ! empty( Param::request( 'redirect_uri' ) ) || $redirections->form->is_editing();

$is_importexport = ! empty( Param::get( 'importexport' ) );

$entries_status = Param::get( 'status' ) ?? 'any';

?>
<div class="wrap rank-math-redirections-wrap">

	<h1 class="wp-heading-inline">
		<?php echo esc_html( get_admin_page_title() ); ?>
		<?php $redirections->page_title_actions( $is_editing ); ?>
	</h1>

	<div class="clear"></div>

	<div class="rank-math-redirections-form rank-math-editcreate-form<?php echo $is_editing || $is_new ? ' is-open' : ''; ?> rank-math-page rank-math-box">

		<?php $redirections->form->display(); ?>

	</div>

	<div class="rank-math-redirections-form rank-math-importexport-form<?php echo $is_importexport ? ' is-open' : ''; ?>">

		<?php $redirections->import_export->display_form(); ?>

	</div>

	<form method="get">
		<input type="hidden" name="page" value="rank-math-redirections">
		<input type="hidden" name="status" value="<?php echo esc_attr( $entries_status ); ?>">
		<?php $redirections->table->search_box( esc_html__( 'Search', 'rank-math' ), 's' ); ?>
	</form>

	<form method="post">
	<?php
		$redirections->table->views();
		$redirections->table->display();
	?>
	</form>

</div>
