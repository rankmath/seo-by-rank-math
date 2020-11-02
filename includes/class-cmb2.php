<?php
/**
 * The CMB2 functionality of the plugin.
 *
 * This class defines all code necessary to have setting pages and manager.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Core
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath;

use RankMath\Admin\Admin_Helper;
use MyThemeShop\Helpers\Str;
use MyThemeShop\Helpers\Param;
use MyThemeShop\Helpers\Conditional;

defined( 'ABSPATH' ) || exit;

/**
 * CMB2 class.
 */
class CMB2 {

	/**
	 * Set field arguments based on type.
	 *
	 * @param CMB2 $cmb CMB2 metabox object.
	 */
	public static function pre_init( $cmb ) {
		foreach ( $cmb->prop( 'fields' ) as $id => $field_args ) {
			$type  = $field_args['type'];
			$field = $cmb->get_field( $id );

			if ( in_array( $type, [ 'meta_tab_container_open', 'tab_container_open', 'tab_container_close', 'tab', 'raw' ], true ) ) {
				$field->args['save_field']    = false;
				$field->args['render_row_cb'] = [ '\RankMath\CMB2', "render_{$type}" ];
			}
			if ( 'notice' === $type ) {
				$field->args['save_field'] = false;
			}

			if ( ! empty( $field_args['dep'] ) ) {
				self::set_dependencies( $field, $field_args );
			}
		}
	}

	/**
	 * Generate the dependency html for JavaScript.
	 *
	 * @param CMB2_Field $field CMB2 field object.
	 * @param array      $args  Dependency array.
	 */
	private static function set_dependencies( $field, $args ) {
		if ( ! isset( $args['dep'] ) || empty( $args['dep'] ) ) {
			return;
		}

		$dependency = '';
		$relation   = 'OR';

		if ( 'relation' === key( $args['dep'] ) ) {
			$relation = current( $args['dep'] );
			unset( $args['dep']['relation'] );
		}

		foreach ( $args['dep'] as $dependence ) {
			$compasrison = isset( $dependence[2] ) ? $dependence[2] : '=';
			$dependency .= '<span class="hidden" data-field="' . $dependence[0] . '" data-comparison="' . $compasrison . '" data-value="' . $dependence[1] . '"></span>';
		}

		$where                 = 'group' === $args['type'] ? 'after_group' : 'after_field';
		$field->args[ $where ] = '<div class="rank-math-cmb-dependency hidden" data-relation="' . strtolower( $relation ) . '">' . $dependency . '</div>';
	}

	/**
	 * Get the object type for the current page, based on the $pagenow global.
	 *
	 * @see CMB2->current_object_type()
	 *
	 * @return string Page object type name.
	 */
	public static function current_object_type() {
		global $pagenow;
		$type = 'post';

		if ( in_array( $pagenow, [ 'user-edit.php', 'profile.php', 'user-new.php' ], true ) ) {
			$type = 'user';
		}

		if ( in_array( $pagenow, [ 'edit-comments.php', 'comment.php' ], true ) ) {
			$type = 'comment';
		}

		if ( in_array( $pagenow, [ 'edit-tags.php', 'term.php' ], true ) ) {
			$type = 'term';
		}

		if ( Conditional::is_ajax() && 'add-tag' === Param::post( 'action' ) ) {
			$type = 'term';
		}

		return $type;
	}

	/**
	 * Render raw field.
	 *
	 * @param array      $field_args Array of field arguments.
	 * @param CMB2_Field $field      The field object.
	 *
	 * @return CMB2_Field
	 */
	public static function render_raw( $field_args, $field ) {
		if ( $field->args( 'file' ) ) {
			include $field->args( 'file' );
		} elseif ( $field->args( 'content' ) ) {
			echo $field->args( 'content' );
		}

		return $field;
	}

	/**
	 * Render tab container opening <div> for option panel.
	 *
	 * @param array      $field_args Array of field arguments.
	 * @param CMB2_Field $field      The field object.
	 *
	 * @return CMB2_Field
	 */
	public static function render_tab_container_open( $field_args, $field ) {
		$active = Param::get( 'rank-math-tab', 'general' );
		echo '<div id="' . $field->prop( 'id' ) . '" class="rank-math-tabs">';
		?>
		<div class="rank-math-tabs-navigation <?php echo $field->prop( 'classes' ); ?>">

			<?php
			foreach ( $field->args( 'tabs' ) as $id => $tab ) :
				if ( empty( $tab ) ) {
					continue;
				}

				if ( isset( $tab['type'] ) && 'seprator' === $tab['type'] ) {
					printf( '<span class="separator">%s</span>', $tab['title'] );
					continue;
				}

				$class  = isset( $tab['classes'] ) ? $tab['classes'] : '';
				$class .= $id === $active ? ' active' : '';
				?>
				<a href="#setting-panel-<?php echo $id; ?>" class="<?php echo $class; ?>"><span class="<?php echo esc_attr( $tab['icon'] ); ?>"></span><?php echo $tab['title']; ?></a>
			<?php endforeach; ?>

		</div>

		<div class="rank-math-tabs-content">
		<?php
		return $field;
	}

	/**
	 * Render tab container opening <div> for metabox.
	 *
	 * @param array      $field_args Array of field arguments.
	 * @param CMB2_Field $field      The field object.
	 *
	 * @return CMB2_Field
	 */
	public static function render_meta_tab_container_open( $field_args, $field ) {
		echo '<div id="' . $field->prop( 'id' ) . '" class="rank-math-tabs">';
		if ( Admin_Helper::is_term_profile_page() ) :
			?>
			<h2 class="rank-math-metabox-frame-title"><?php esc_html_e( 'Rank Math', 'rank-math' ); ?></h2>
		<?php endif; ?>
		<div class="rank-math-tabs-navigation rank-math-custom wp-clearfix">

			<?php
			foreach ( $field->args( 'tabs' ) as $id => $tab ) :
				if ( empty( $tab ) || ! Helper::has_cap( $tab['capability'] ) ) {
					continue;
				}
				?>
				<a href="#setting-panel-<?php echo $id; ?>"><span class="<?php echo esc_attr( $tab['icon'] ); ?>"></span><span class="rank-math-tab-text"><?php echo $tab['title']; ?></span></a>
			<?php endforeach; ?>
		</div>

		<div class="rank-math-tabs-content rank-math-custom">
		<?php
		return $field;
	}

	/**
	 * Render tab container closing <div>.
	 *
	 * @param array      $field_args Array of field arguments.
	 * @param CMB2_Field $field      The field object.
	 *
	 * @return CMB2_Field
	 */
	public static function render_tab_container_close( $field_args, $field ) {
		echo '</div><!-- /.rank-math-tabs-content -->';
		echo '</div><!-- /#' . $field->prop( 'id' ) . ' -->';

		return $field;
	}

	/**
	 * Render tab content opening <div> and closing </div>.
	 *
	 * @param array      $field_args Array of field arguments.
	 * @param CMB2_Field $field      The field object.
	 *
	 * @return CMB2_Field
	 */
	public static function render_tab( $field_args, $field ) {
		printf(
			true === $field->prop( 'open' ) ? '<div id="%1$s" class="rank-math-tab rank-math-options-panel-content ' . $field->prop( 'classes' ) . '">' : '</div><!-- /#%1$s -->',
			$field->prop( 'id' )
		);

		return $field;
	}

	/**
	 * Handles sanitization for HTML entities.
	 *
	 * @param mixed $value The unsanitized value from the form.
	 *
	 * @return mixed Sanitized value to be stored.
	 */
	public static function sanitize_htmlentities( $value ) {
		return htmlentities( $value );
	}

	/**
	 * Handles sanitization for Separator Character option.
	 *
	 * @param mixed $value The unsanitized value from the form.
	 *
	 * @return mixed Sanitized value to be stored.
	 */
	public static function sanitize_separator( $value ) {
		return htmlentities( wp_strip_all_tags( $value, true ) );
	}

	/**
	 * Handles sanitization for text fields.
	 *
	 * @param string $value The unsanitized value from the form.
	 *
	 * @return string Sanitized value to be stored.
	 */
	public static function sanitize_textfield( $value ) {
		if ( is_object( $value ) || is_array( $value ) ) {
			return '';
		}

		$value    = (string) $value;
		$filtered = wp_check_invalid_utf8( $value );

		if ( strpos( $filtered, '<' ) !== false ) {
			$filtered = wp_pre_kses_less_than( $filtered );
			// This will strip extra whitespace for us.
			$filtered = wp_strip_all_tags( $filtered, false );

			// Use html entities in a special case to make sure no later
			// newline stripping stage could lead to a functional tag!
			$filtered = str_replace( "<\n", "&lt;\n", $filtered );
		}
		$filtered = preg_replace( '/[\r\n\t ]+/', ' ', $filtered );
		$filtered = trim( $filtered );

		$found = false;
		while ( preg_match( '/%[0-9]{2}/i', $filtered, $match ) ) {
			$filtered = str_replace( $match[0], '', $filtered );
			$found    = true;
		}

		if ( $found ) {
			// Strip out the whitespace that may now exist after removing the octets.
			$filtered = trim( preg_replace( '/ +/', ' ', $filtered ) );
		}

		return apply_filters( 'sanitize_text_field', $filtered, $value );
	}

	/**
	 * Handles sanitization of rank_math_permalink.
	 *
	 * @param string $value The unsanitized value from the form.
	 *
	 * @return string Sanitized value to be stored.
	 */
	public static function sanitize_permalink( $value ) {
		if ( empty( $value ) ) {
			return '';
		}

		return sanitize_title( $value );
	}

	/**
	 * Handles escaping of rank_math_permalink.
	 *
	 * @param string $value The value from the DB.
	 *
	 * @return string Escaped value.
	 */
	public static function escape_permalink( $value ) {
		if ( empty( $value ) ) {
			return '';
		}

		return esc_attr( urldecode( $value ) );
	}

	/**
	 * Handles sanitization of floating point values.
	 *
	 * @param string $value The unsanitized value from the form.
	 *
	 * @return string Sanitized value to be stored.
	 */
	public static function sanitize_float( $value ) {
		if ( empty( $value ) ) {
			return 0;
		}

		return filter_var( $value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
	}

	/**
	 * Handles sanitization for webmaster tag and remove <meta> tag.
	 *
	 * @param mixed $value The unsanitized value from the form.
	 *
	 * @return mixed Sanitized value to be stored.
	 */
	public static function sanitize_webmaster_tags( $value ) {
		$value = trim( $value );

		if ( ! empty( $value ) && Str::starts_with( '<meta', trim( $value ) ) ) {
			preg_match( '/content="([^"]+)"/i', stripslashes( $value ), $matches );
			$value = $matches[1];
		}

		return htmlentities( wp_strip_all_tags( $value ) );
	}

	/**
	 * Handles sanitization of advanced robots data.
	 *
	 * @param array $robots The unsanitized value from the form.
	 *
	 * @return array Sanitized value to be stored.
	 */
	public static function sanitize_advanced_robots( $robots ) {
		if ( empty( $robots ) ) {
			return [];
		}

		$advanced_robots = [];
		foreach ( $robots as $key => $robot ) {
			$advanced_robots[ $key ] = ! empty( $robot['enable'] ) ? $robot['length'] : false;
		}

		return $advanced_robots;
	}

	/**
	 * Handles sanitization of Focus Keywords.
	 *
	 * @param mixed $value The unsanitized focus keywords.
	 *
	 * @return string Sanitized focus keywords to be stored.
	 */
	public static function sanitize_focus_keywords( $value ) {
		$values = json_decode( stripslashes( $value ), true );
		if ( empty( $values ) ) {
			return '';
		}

		return implode(
			',',
			array_map(
				function ( $entry ) {
					return sanitize_text_field( $entry['value'] );
				},
				$values
			)
		);
	}

	/**
	 * Handles sanitization of Robots text.
	 *
	 * @since 1.0.45
	 *
	 * @param mixed $value The unsanitized Robots text.
	 *
	 * @return string Sanitized Robots text to be stored.
	 */
	public static function sanitize_robots_text( $value ) {
		if ( empty( $value ) ) {
			return '';
		}

		return wp_strip_all_tags( $value );
	}
}
