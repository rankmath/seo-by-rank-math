<?php
/**
 * Show SEO Score on the front end.
 *
 * @since      0.9.0
 * @package    RankMath
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath;

use RankMath\KB;
use RankMath\Post;
use RankMath\Helper;
use RankMath\Traits\Hooker;
use RankMath\Traits\Shortcode;
use RankMath\Admin\Admin_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Frontend_SEO_Score class.
 */
class Frontend_SEO_Score {

	use Hooker, Shortcode;

	/**
	 * SEO Score.
	 *
	 * @var array
	 */
	private $score = 0;

	/**
	 * Flag to only add CSS once.
	 *
	 * @var array
	 */
	private $css_added = false;

	/**
	 * Convenience method to output as string.
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->get_output();
	}

	/**
	 * The Constructor
	 *
	 * @codeCoverageIgnore
	 */
	public function __construct() {
		$this->filter( 'the_content', 'insert_score' );

		$this->add_shortcode( 'rank_math_seo_score', 'shortcode' );
	}

	/**
	 * Insert score before/after content.
	 *
	 * @param string $content Original post content.
	 * @return string $content New content.
	 */
	public function insert_score( $content ) {

		if ( ! $this->score_enabled() ) {
			return $content;
		}

		$score_location = Helper::get_settings( 'general.frontend_seo_score_position' );
		if ( 'custom' === $score_location ) {
			return $content;
		}

		if ( 'top' === $score_location || 'both' === $score_location ) {
			$content = $this->get_output( [ 'class' => 'before-content' ] ) . $content;
		}

		if ( 'bottom' === $score_location || 'both' === $score_location ) {
			$content = $content . $this->get_output( [ 'class' => 'after-content' ] );
		}

		return $content;
	}

	/**
	 * Check if front end SEO score is enabled for this post.
	 *
	 * @return bool
	 */
	public function score_enabled() {
		/*
		 * The loop_start check ensures this only runs after wp_head.
		 */
		if ( is_front_page() || ! is_singular() || ! did_action( 'loop_start' ) ) {
			return false;
		}

		$post_type     = get_post_type();
		$post_id       = get_the_ID();
		$score_enabled = Helper::get_settings( 'general.frontend_seo_score' )
			&& Helper::is_score_enabled()
			&& in_array( $post_type, (array) Helper::get_settings( 'general.frontend_seo_score_post_types' ), true )
			&& get_post_meta( $post_id, 'rank_math_dont_show_seo_score', true ) !== 'on';

		return $score_enabled;
	}

	/**
	 * Get the SEO score HTML.
	 *
	 * @param array $args Arguments.
	 * @return string
	 */
	public function get_output( $args = [] ) {
		$args = $this->do_filter(
			'frontend/seo_score/args',
			wp_parse_args(
				$args,
				[
					'template' => Helper::get_settings( 'general.frontend_seo_score_template' ),
					'backlink' => Helper::get_settings( 'general.support_rank_math' ),
					'post_id'  => '0',
					'class'    => '',
				]
			)
		);

		$score  = (int) $this->get_score( $args['post_id'] );
		$rating = $this->get_rating( $score );

		if ( ! $score ) {
			return $this->do_filter( 'frontend/seo_score/html', '', $args, $score );
		}

		// If template is empty we output $score value directly.
		$html     = $score;
		$backlink = '<a href="' . KB::get( 'seo-suite', 'Frontend SEO score' ) . '" target="_blank" rel="noopener">Rank Math SEO</a>';
		if ( ! empty( $args['template'] ) ) {
			ob_start();

			?>
			<div class="rank-math-seo-score template-<?php echo sanitize_html_class( $args['template'], 'circle' ); ?> <?php echo sanitize_html_class( $rating, 'unknown' ); ?>-seo <?php echo esc_attr( $args['class'] ); ?>">

				<span class="score">
					<?php echo esc_html( absint( $score ) ); ?>
					<span class="outof">
						/ 100
					</span>
				</span>

				<?php if ( $args['backlink'] ) : ?>
					<div class="backlink">
						<span class="poweredby">
							<?php
							printf(
								/* translators: %s is a Rank Math link. */
								__( 'Powered by %s', 'rank-math' ),
								$this->do_filter( 'frontend/seo_score/backlink', $backlink )
							);
							?>
						</span>
					</div>
				<?php endif; ?>

				<span class="label">
					<?php esc_html__( 'SEO Score', 'rank-math' ); ?>
				</span>

			</div>
			<?php
			$this->add_css();

			$html = ob_get_clean();
		}

		return $this->do_filter( 'frontend/seo_score/html', $html, $args, $score );
	}

	/**
	 * Turn numeric score into textual rating.
	 *
	 * @param int $score SEO Score.
	 * @return string
	 */
	public function get_rating( $score ) {
		$hash = [
			'unknown' => 0,
			'bad'     => 50,
			'good'    => 80,
			'great'   => 100,
		];

		foreach ( $hash as $key => $value ) {
			if ( $score <= $value ) {
				return $key;
			}
		}

		return array_keys( $hash )[0];
	}

	/**
	 * Get the SEO score for given post.
	 *
	 * @param int $post_id Post ID.
	 * @return int
	 */
	public function get_score( $post_id = 0 ) {
		global $post;
		if ( empty( $post_id ) ) {
			$post_id = $post->ID;
		}

		return get_post_meta( $post_id, 'rank_math_seo_score', true );
	}

	/**
	 * Show field check callback.
	 *
	 * @param  CMB2_Field $field The current field.
	 * @return boolean
	 */
	public static function show_on( $field = [] ) {
		// Early Bail if is sttic homepage.
		if ( Admin_Helper::is_home_page() ) {
			return false;
		}

		$post_type = get_post_type();
		return Helper::get_settings( 'general.frontend_seo_score' ) &&
			in_array( $post_type, (array) Helper::get_settings( 'general.frontend_seo_score_post_types' ), true );
	}

	/**
	 * Shortcode output.
	 *
	 * @param array $atts Shortcode attributes.
	 */
	public function shortcode( $atts ) {
		if ( ! $this->score_enabled() ) {
			return '';
		}

		$atts = shortcode_atts(
			[
				'class' => 'as-shortcode',
			],
			$atts,
			'rank-math-seo-score'
		);

		return $this->get_output( $atts );
	}

	/**
	 * Add CSS inline, once.
	 */
	public function add_css() {
		if ( $this->css_added ) {
			return;
		}
		?>
		<style type="text/css">
		.rank-math-seo-score{font-family:sans-serif;position:relative;display:inline-block;height:96px;width:96px;margin:20px 20px 30px;text-align:center;color:#fff;border:none;border-radius:50%;background:#eee;-webkit-box-shadow:1px 1px 1px #bbb;box-shadow:1px 1px 1px #bbb}.rank-math-seo-score.before-content{margin:0 0 30px 20px;float:right}.rank-math-seo-score.after-content{margin:20px 0 30px 20px}.rank-math-seo-score.as-shortcode{display:inline-block}.rank-math-seo-score .label{font-size:12px;position:absolute;top:100px;left:0;display:block;width:100%;color:#979ea5}.rank-math-seo-score .score{font-size:42px;font-weight:bold;line-height:42px;display:block}.rank-math-seo-score .outof{font-size:12px;font-weight:normal;line-height:12px;display:block;color:rgba(255,255,255,0.7)}.rank-math-seo-score .backlink{font-size:12px;position:absolute;top:-94px;left:-12px;display:block;visibility:hidden;width:120px;padding:8px 10px;-webkit-transition:.25s all ease;transition:.25s all ease;-webkit-transition-delay:.25s;transition-delay:.25s;opacity:0;color:#a8a8a8;border:none;border-radius:8px;background:#fff;-webkit-box-shadow:0 4px 14px rgba(60,60,90,0.2);box-shadow:0 4px 12px rgba(60,60,90,0.15)}.rank-math-seo-score .backlink:after{position:absolute;bottom:-8px;left:calc(50% - 7px);width:0;height:0;content:'';border-width:8px 7.5px 0 7.5px;border-style:solid;border-color:#fff transparent transparent transparent}.rank-math-seo-score:hover .backlink{top:-74px;visibility:visible;opacity:1}.rank-math-seo-score .poweredby{font-size:13px;color:#a8a8a8}.rank-math-seo-score .poweredby a{display:block;font-weight:normal;text-decoration:none;color:#6372b6;border:none}.rank-math-seo-score.unknown-seo{background:#eee;background:linear-gradient(135deg, #b9b9b9 0%, #989898 100%);-webkit-box-shadow:1px 1px 1px #bbb;box-shadow:1px 1px 1px #bbb}.rank-math-seo-score.bad-seo{background:#f8b0a2;background:linear-gradient(135deg, #f8b0a2 0%, #f1938c 100%);-webkit-box-shadow:1px 1px 1px #e48982;box-shadow:1px 1px 1px #e48982;filter:progid:DXImageTransform.Microsoft.gradient( startColorstr='#f8b0a2', endColorstr='#f1938c',GradientType=1 )}.rank-math-seo-score.good-seo{background:#fdd07a;background:linear-gradient(135deg, #fdd07a 0%, #fcbe6c 100%);-webkit-box-shadow:1px 1px 1px #efb463;box-shadow:1px 1px 1px #efb463;filter:progid:DXImageTransform.Microsoft.gradient( startColorstr='#fdd07a', endColorstr='#fcbe6c',GradientType=1 )}.rank-math-seo-score.great-seo{background:#99d484;background:linear-gradient(135deg, #99d484 0%, #83c97f 100%);-webkit-box-shadow:1px 1px 1px #5ba857;box-shadow:1px 1px 1px #5ba857;filter:progid:DXImageTransform.Microsoft.gradient( startColorstr='#99d484', endColorstr='#83c97f',GradientType=1 )}.rank-math-seo-score.template-circle .score{margin-top:22px !important}.rank-math-seo-score.template-square{height:80px;width:110px;border-radius:12px}.rank-math-seo-score.template-square .score{margin:10px 12px;text-align:left}.rank-math-seo-score.template-square .outof{display:inline-block;margin-left:-8px}.rank-math-seo-score.template-square .label{font-size:13px;top:52px;left:14px;text-align:left;color:rgba(255,255,255,0.8)}.rank-math-seo-score.template-square .backlink{left:-5px}.rank-math-seo-score.template-square.before-content{margin-bottom:20px}.rank-math-seo-score.template-square.after-content{margin-bottom:0}.theme-twentytwenty .rank-math-seo-score{width:96px !important}.theme-twentytwenty .rank-math-seo-score.template-square{width:110px !important}.theme-twentytwenty .rank-math-seo-score.before-content{margin:0 auto 30px auto;display:inherit;float:none}.theme-twentytwenty .rank-math-seo-score.template-circle .score,.theme-twentytwenty .rank-math-seo-score.template-square .score{transform:translateY(22px)}
		</style>
		<?php
		$this->css_added = true;
	}

	/**
	 * Settings field default callback.
	 */
	public static function post_types_field_default() {
		$seo_score  = Helper::get_settings( 'general.frontend_seo_score' );
		$post_types = Helper::get_settings( 'general.frontend_seo_score_post_types' );

		if ( 'on' === $seo_score && '' === $post_types ) {
			return [];
		}

		return [ 'post' ];
	}
}
