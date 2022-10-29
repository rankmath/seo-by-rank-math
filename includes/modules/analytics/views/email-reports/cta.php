<?php
/**
 * Analytics Report header template.
 *
 * @package    RankMath
 * @subpackage RankMath\Admin
 */

use RankMath\KB;

defined( 'ABSPATH' ) || exit;

?>
<table role="presentation" border="0" cellpadding="0" cellspacing="0" class="cta">
	<tbody>
		<tr class="top">
			<td align="left">
				<a href="<?php KB::the( 'seo-email-reporting', 'Email Report CTA' ); ?>"><?php $this->image( 'rank-math-pro.jpg', 540, 422, __( 'Rank Math PRO', 'rank-math' ) ); ?></a>
			</td>
		</tr>
	</tbody>
</table>
