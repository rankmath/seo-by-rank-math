<?php
/**
 * Finding value object for the rank-math/audit-site-seo ability.
 *
 * @since      1.0.271
 * @package    RankMath
 * @subpackage RankMath\Abilities\SEO_Analysis
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\SEO_Analysis;

defined( 'ABSPATH' ) || exit;

/**
 * One SEO-audit test result, agent-consumable shape.
 */
class Finding {

	/**
	 * Test identifier.
	 *
	 * @var string
	 */
	public $test_id;

	/**
	 * Test category: priority|basic|advanced|performance|security.
	 *
	 * @var string
	 */
	public $category;

	/**
	 * Test outcome: ok|fail|warning|info.
	 *
	 * @var string
	 */
	public $status;

	/**
	 * Score contribution for this test.
	 *
	 * @var int
	 */
	public $score;

	/**
	 * Human-readable test title.
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Plain-text description of the current result (actual counts/state, not static guidance).
	 *
	 * @var string
	 */
	public $description;

	/**
	 * Plain-text how-to-fix — HTML tags and entities stripped, ready for AI consumption.
	 *
	 * @var string
	 */
	public $fix_text;

	/**
	 * Original how-to-fix HTML, preserved for admin-UI consumers.
	 *
	 * @var string
	 */
	public $fix_html;

	/**
	 * Structured fix hint an agent can act on, or null if none mapped.
	 *
	 * @var array|null
	 */
	public $fix_hint;

	/**
	 * Knowledge-base article URL.
	 *
	 * @var string
	 */
	public $kb_link;

	/**
	 * Free-form per-test supplemental data.
	 *
	 * @var mixed
	 */
	public $data;

	/**
	 * Constructor.
	 *
	 * @param array $args See properties.
	 */
	public function __construct( array $args ) {
		foreach ( $args as $key => $value ) {
			if ( property_exists( $this, $key ) ) {
				$this->{$key} = $value;
			}
		}
	}

	/**
	 * Serialize to array.
	 *
	 * @return array
	 */
	public function to_array() {
		return [
			'test_id'     => $this->test_id,
			'category'    => $this->category,
			'status'      => $this->status,
			'score'       => (int) $this->score,
			'title'       => $this->title,
			'description' => $this->description,
			'fix_text'    => $this->fix_text,
			'fix_html'    => $this->fix_html,
			'fix_hint'    => $this->fix_hint,
			'kb_link'     => $this->kb_link,
			'data'        => $this->data,
		];
	}
}
