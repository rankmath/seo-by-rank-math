<?php
/**
 * Analytics Report email template.
 *
 * @package    RankMath
 * @subpackage RankMath\Admin
 */

defined( 'ABSPATH' ) || exit;

$this->template_part( 'header' );

?>

<?php $this->template_part( 'header-after' ); ?>

<?php $this->template_part( 'sections/summary' ); ?>

<?php $this->template_part( 'sections/positions' ); ?>

<?php $this->template_part( 'cta' ); ?>

<?php $this->template_part( 'footer' ); ?>
