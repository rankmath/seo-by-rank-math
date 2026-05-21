<?php
/**
 * Content AI general settings.
 *
 * @package    RankMath
 * @subpackage RankMath\ContentAI
 */

use RankMath\Helper;
use RankMath\KB;
use RankMath\Admin\Admin_Helper;

defined( 'ABSPATH' ) || exit;

if ( ! Helper::is_site_connected() ) {
	$cmb->add_field(
		[
			'id'      => 'rank_math_content_ai_settings',
			'type'    => 'raw',
			'content' => '<div id="setting-panel-content-ai" class="rank-math-tab rank-math-options-panel-content exclude">
				<div class="wp-core-ui rank-math-ui connect-wrap">
					<a href="' . Admin_Helper::get_activate_url( Helper::get_settings_url( 'general', 'content-ai' ) ) . '" class="button button-primary button-connect button-animated" name="rank_math_activate">'
					. esc_html__( 'Connect Your Rank Math Account', 'seo-by-rank-math' )
					. '</a>
				</div>
				<div id="rank-math-pro-cta" class="content-ai-settings">
					<div class="rank-math-cta-box width-100 no-shadow no-padding no-border">
						<h3>' . esc_html__( 'Benefits of Connecting Rank Math Account', 'seo-by-rank-math' ) . '</h3>
						<ul>
							<li>' . esc_html__( 'Gain Access to 40+ Advanced AI Tools.', 'seo-by-rank-math' ) . '</li>
							<li>' . esc_html__( 'Experience the Revolutionary AI-Powered Content Editor.', 'seo-by-rank-math' ) . '</li>
							<li>' . esc_html__( 'Engage with RankBot, Our AI Chatbot, For SEO Advice.', 'seo-by-rank-math' ) . '</li>
							<li>' . esc_html__( 'Escape the Writer\'s Block Using AI to Write Inside WordPress.', 'seo-by-rank-math' ) . '</li>
						</ul>
					</div>
				</div>
			</div>',
		]
	);
	return;
}

$cmb->add_field(
	[
		'id'      => 'content_ai_country',
		'type'    => 'select',
		'name'    => esc_html__( 'Default Country', 'seo-by-rank-math' ),
		'desc'    => esc_html__( 'Content AI tailors keyword research to the target country for highly relevant suggestions. You can override this in individual posts/pages/CPTs.', 'seo-by-rank-math' ),
		'options' => Helper::choices_contentai_countries(),
		'default' => 'all',
	]
);

$cmb->add_field(
	[
		'id'         => 'content_ai_tone',
		'type'       => 'select',
		'name'       => esc_html__( 'Default Tone', 'seo-by-rank-math' ),
		'desc'       => esc_html__( 'This feature enables the default primary tone or writing style that characterizes your content. You can override this in individual tools.', 'seo-by-rank-math' ),
		'default'    => 'Formal',
		'attributes' => ( 'data-s2' ),
		'options'    => [
			'Analytical'     => esc_html__( 'Analytical', 'seo-by-rank-math' ),
			'Argumentative'  => esc_html__( 'Argumentative', 'seo-by-rank-math' ),
			'Casual'         => esc_html__( 'Casual', 'seo-by-rank-math' ),
			'Conversational' => esc_html__( 'Conversational', 'seo-by-rank-math' ),
			'Creative'       => esc_html__( 'Creative', 'seo-by-rank-math' ),
			'Descriptive'    => esc_html__( 'Descriptive', 'seo-by-rank-math' ),
			'Emotional'      => esc_html__( 'Emotional', 'seo-by-rank-math' ),
			'Empathetic'     => esc_html__( 'Empathetic', 'seo-by-rank-math' ),
			'Expository'     => esc_html__( 'Expository', 'seo-by-rank-math' ),
			'Factual'        => esc_html__( 'Factual', 'seo-by-rank-math' ),
			'Formal'         => esc_html__( 'Formal', 'seo-by-rank-math' ),
			'Friendly'       => esc_html__( 'Friendly', 'seo-by-rank-math' ),
			'Humorous'       => esc_html__( 'Humorous', 'seo-by-rank-math' ),
			'Informal'       => esc_html__( 'Informal', 'seo-by-rank-math' ),
			'Journalese'     => esc_html__( 'Journalese', 'seo-by-rank-math' ),
			'Narrative'      => esc_html__( 'Narrative', 'seo-by-rank-math' ),
			'Objective'      => esc_html__( 'Objective', 'seo-by-rank-math' ),
			'Opinionated'    => esc_html__( 'Opinionated', 'seo-by-rank-math' ),
			'Persuasive'     => esc_html__( 'Persuasive', 'seo-by-rank-math' ),
			'Poetic'         => esc_html__( 'Poetic', 'seo-by-rank-math' ),
			'Satirical'      => esc_html__( 'Satirical', 'seo-by-rank-math' ),
			'Story-telling'  => esc_html__( 'Story-telling', 'seo-by-rank-math' ),
			'Subjective'     => esc_html__( 'Subjective', 'seo-by-rank-math' ),
			'Technical'      => esc_html__( 'Technical', 'seo-by-rank-math' ),
		],
	],
);

$cmb->add_field(
	[
		'id'         => 'content_ai_audience',
		'type'       => 'select',
		'name'       => esc_html__( 'Default Audience', 'seo-by-rank-math' ),
		'desc'       => esc_html__( 'This option lets you set the default audience that usually reads your content. You can override this in individual tools.', 'seo-by-rank-math' ),
		'default'    => 'General Audience',
		'attributes' => ( 'data-s2' ),
		'options'    => [
			'Activists'                => esc_html__( 'Activists', 'seo-by-rank-math' ),
			'Artists'                  => esc_html__( 'Artists', 'seo-by-rank-math' ),
			'Authors'                  => esc_html__( 'Authors', 'seo-by-rank-math' ),
			'Bargain Hunters'          => esc_html__( 'Bargain Hunters', 'seo-by-rank-math' ),
			'Bloggers'                 => esc_html__( 'Bloggers', 'seo-by-rank-math' ),
			'Business Owners'          => esc_html__( 'Business Owners', 'seo-by-rank-math' ),
			'Collectors'               => esc_html__( 'Collectors', 'seo-by-rank-math' ),
			'Cooks'                    => esc_html__( 'Cooks', 'seo-by-rank-math' ),
			'Crafters'                 => esc_html__( 'Crafters', 'seo-by-rank-math' ),
			'Dancers'                  => esc_html__( 'Dancers', 'seo-by-rank-math' ),
			'DIYers'                   => esc_html__( 'DIYers', 'seo-by-rank-math' ),
			'Designers'                => esc_html__( 'Designers', 'seo-by-rank-math' ),
			'Educators'                => esc_html__( 'Educators', 'seo-by-rank-math' ),
			'Engineers'                => esc_html__( 'Engineers', 'seo-by-rank-math' ),
			'Entrepreneurs'            => esc_html__( 'Entrepreneurs', 'seo-by-rank-math' ),
			'Environmentalists'        => esc_html__( 'Environmentalists', 'seo-by-rank-math' ),
			'Fashionistas'             => esc_html__( 'Fashionistas', 'seo-by-rank-math' ),
			'Fitness Enthusiasts'      => esc_html__( 'Fitness Enthusiasts', 'seo-by-rank-math' ),
			'Foodies'                  => esc_html__( 'Foodies', 'seo-by-rank-math' ),
			'Gaming Enthusiasts'       => esc_html__( 'Gaming Enthusiasts', 'seo-by-rank-math' ),
			'Gardeners'                => esc_html__( 'Gardeners', 'seo-by-rank-math' ),
			'General Audience'         => esc_html__( 'General Audience', 'seo-by-rank-math' ),
			'Health Enthusiasts'       => esc_html__( 'Health Enthusiasts', 'seo-by-rank-math' ),
			'Healthcare Professionals' => esc_html__( 'Healthcare Professionals', 'seo-by-rank-math' ),
			'Indoor Hobbyists'         => esc_html__( 'Indoor Hobbyists', 'seo-by-rank-math' ),
			'Investors'                => esc_html__( 'Investors', 'seo-by-rank-math' ),
			'Job Seekers'              => esc_html__( 'Job Seekers', 'seo-by-rank-math' ),
			'Movie Buffs'              => esc_html__( 'Movie Buffs', 'seo-by-rank-math' ),
			'Musicians'                => esc_html__( 'Musicians', 'seo-by-rank-math' ),
			'Outdoor Enthusiasts'      => esc_html__( 'Outdoor Enthusiasts', 'seo-by-rank-math' ),
			'Parents'                  => esc_html__( 'Parents', 'seo-by-rank-math' ),
			'Pet Owners'               => esc_html__( 'Pet Owners', 'seo-by-rank-math' ),
			'Photographers'            => esc_html__( 'Photographers', 'seo-by-rank-math' ),
			'Podcast Listeners'        => esc_html__( 'Podcast Listeners', 'seo-by-rank-math' ),
			'Professionals'            => esc_html__( 'Professionals', 'seo-by-rank-math' ),
			'Retirees'                 => esc_html__( 'Retirees', 'seo-by-rank-math' ),
			'Russian'                  => esc_html__( 'Russian', 'seo-by-rank-math' ),
			'Seniors'                  => esc_html__( 'Seniors', 'seo-by-rank-math' ),
			'Social Media Users'       => esc_html__( 'Social Media Users', 'seo-by-rank-math' ),
			'Sports Fans'              => esc_html__( 'Sports Fans', 'seo-by-rank-math' ),
			'Students'                 => esc_html__( 'Students', 'seo-by-rank-math' ),
			'Tech Enthusiasts'         => esc_html__( 'Tech Enthusiasts', 'seo-by-rank-math' ),
			'Travelers'                => esc_html__( 'Travelers', 'seo-by-rank-math' ),
			'TV Enthusiasts'           => esc_html__( 'TV Enthusiasts', 'seo-by-rank-math' ),
			'Video Creators'           => esc_html__( 'Video Creators', 'seo-by-rank-math' ),
			'Writers'                  => esc_html__( 'Writers', 'seo-by-rank-math' ),
		],
	],
);

$cmb->add_field(
	[
		'id'         => 'content_ai_language',
		'type'       => 'select',
		'name'       => esc_html__( 'Default Language', 'seo-by-rank-math' ),
		'desc'       => esc_html__( 'This option lets you set the default language for content generated using Content AI. You can override this in individual tools.', 'seo-by-rank-math' ),
		'default'    => Helper::content_ai_default_language(),
		'attributes' => ( 'data-s2' ),
		'options'    => [
			'US English' => esc_html__( 'US English', 'seo-by-rank-math' ),
			'UK English' => esc_html__( 'UK English', 'seo-by-rank-math' ),
			'Arabic'     => esc_html__( 'Arabic', 'seo-by-rank-math' ),
			'Bulgarian'  => esc_html__( 'Bulgarian', 'seo-by-rank-math' ),
			'Chinese'    => esc_html__( 'Chinese', 'seo-by-rank-math' ),
			'Czech'      => esc_html__( 'Czech', 'seo-by-rank-math' ),
			'Danish'     => esc_html__( 'Danish', 'seo-by-rank-math' ),
			'Dutch'      => esc_html__( 'Dutch', 'seo-by-rank-math' ),
			'Estonian'   => esc_html__( 'Estonian', 'seo-by-rank-math' ),
			'Finnish'    => esc_html__( 'Finnish', 'seo-by-rank-math' ),
			'French'     => esc_html__( 'French', 'seo-by-rank-math' ),
			'German'     => esc_html__( 'German', 'seo-by-rank-math' ),
			'Greek'      => esc_html__( 'Greek', 'seo-by-rank-math' ),
			'Hebrew'     => esc_html__( 'Hebrew', 'seo-by-rank-math' ),
			'Hungarian'  => esc_html__( 'Hungarian', 'seo-by-rank-math' ),
			'Indonesian' => esc_html__( 'Indonesian', 'seo-by-rank-math' ),
			'Italian'    => esc_html__( 'Italian', 'seo-by-rank-math' ),
			'Japanese'   => esc_html__( 'Japanese', 'seo-by-rank-math' ),
			'Korean'     => esc_html__( 'Korean', 'seo-by-rank-math' ),
			'Latvian'    => esc_html__( 'Latvian', 'seo-by-rank-math' ),
			'Lithuanian' => esc_html__( 'Lithuanian', 'seo-by-rank-math' ),
			'Norwegian'  => esc_html__( 'Norwegian', 'seo-by-rank-math' ),
			'Polish'     => esc_html__( 'Polish', 'seo-by-rank-math' ),
			'Portuguese' => esc_html__( 'Portuguese', 'seo-by-rank-math' ),
			'Romanian'   => esc_html__( 'Romanian', 'seo-by-rank-math' ),
			'Russian'    => esc_html__( 'Russian', 'seo-by-rank-math' ),
			'Slovak'     => esc_html__( 'Slovak', 'seo-by-rank-math' ),
			'Slovenian'  => esc_html__( 'Slovenian', 'seo-by-rank-math' ),
			'Spanish'    => esc_html__( 'Spanish', 'seo-by-rank-math' ),
			'Swedish'    => esc_html__( 'Swedish', 'seo-by-rank-math' ),
			'Turkish'    => esc_html__( 'Turkish', 'seo-by-rank-math' ),
		],
	],
);

$post_types = Helper::choices_post_types();
if ( isset( $post_types['attachment'] ) ) {
	unset( $post_types['attachment'] );
}

$cmb->add_field(
	[
		'id'      => 'content_ai_post_types',
		'type'    => 'multicheck_inline',
		'name'    => esc_html__( 'Select Post Type', 'seo-by-rank-math' ),
		'desc'    => esc_html__( 'Choose the type of posts/pages/CPTs where you want to use Content AI.', 'seo-by-rank-math' ),
		'options' => $post_types,
		'default' => array_keys( $post_types ),
	]
);

$credits = Helper::get_credits();
if ( Helper::is_site_connected() && false !== $credits ) {
	$update_credits = '<a href="#" class="rank-math-tooltip update-credit">
		<i class="dashicons dashicons-image-rotate"></i>
		<span>' . esc_html__( 'Click to refresh the available credits.', 'seo-by-rank-math' ) . '</span>
	</a>';

	$refresh_date = Helper::get_content_ai_refresh_date();
	$cmb->add_field(
		[
			'id'      => 'content_ai_credits',
			'type'    => 'raw',
			/* translators: 1. Credits left 2. Buy more credits link */
			'content' => '<div class="cmb-row buy-more-credits rank-math-exclude-from-search">' . $update_credits . sprintf( esc_html__( '%1$s credits left this month. Credits will renew on %2$s or you can upgrade to get more credits %3$s.', 'seo-by-rank-math' ), '<strong>' . $credits . '</strong>', wp_date( 'Y-m-d g:i a', $refresh_date ), '<a href="' . KB::get( 'content-ai-pricing-tables', 'Buy CAI Credits Options Panel' ) . '" target="_blank">' . esc_html__( 'here', 'seo-by-rank-math' ) . '</a>' ) . '</div>',
		]
	);
}
