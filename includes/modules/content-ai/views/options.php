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
					<a href="' . Admin_Helper::get_activate_url( admin_url( 'admin.php??page=rank-math-options-general#setting-panel-content-ai' ) ) . '" class="button button-primary button-connect button-animated" name="rank_math_activate">'
					. esc_html__( 'Connect Your Rank Math Account', 'rank-math' )
					. '</a>
				</div>
				<div id="rank-math-pro-cta" class="content-ai-settings">
					<div class="rank-math-cta-box width-100 no-shadow no-padding no-border">
						<h3>' . esc_html__( 'Benefits of Connecting Rank Math Account', 'rank-math' ) . '</h3>
						<ul>
							<li>' . esc_html__( 'Gain Access to 40+ Advanced AI Tools.', 'rank-math' ) . '</li>
							<li>' . esc_html__( 'Experience the Revolutionary AI-Powered Content Editor.', 'rank-math' ) . '</li>
							<li>' . esc_html__( 'Engage with RankBot, Our AI Chatbot, For SEO Advice.', 'rank-math' ) . '</li>
							<li>' . esc_html__( 'Escape the Writer\'s Block Using AI to Write Inside WordPress.', 'rank-math' ) . '</li>
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
		'name'    => esc_html__( 'Default Country', 'rank-math' ),
		'desc'    => esc_html__( 'Content AI tailors keyword research to the target country for highly relevant suggestions. You can override this in individual posts/pages/CPTs.', 'rank-math' ),
		'options' => Helper::choices_contentai_countries(),
		'default' => 'all',
	]
);

$cmb->add_field(
	[
		'id'         => 'content_ai_tone',
		'type'       => 'select',
		'name'       => esc_html__( 'Default Tone', 'rank-math' ),
		'desc'       => esc_html__( 'This feature enables the default primary tone or writing style that characterizes your content. You can override this in individual tools.', 'rank-math' ),
		'default'    => 'Formal',
		'attributes' => ( 'data-s2' ),
		'options'    => [
			'Analytical'     => esc_html__( 'Analytical', 'rank-math' ),
			'Argumentative'  => esc_html__( 'Argumentative', 'rank-math' ),
			'Casual'         => esc_html__( 'Casual', 'rank-math' ),
			'Conversational' => esc_html__( 'Conversational', 'rank-math' ),
			'Creative'       => esc_html__( 'Creative', 'rank-math' ),
			'Descriptive'    => esc_html__( 'Descriptive', 'rank-math' ),
			'Emotional'      => esc_html__( 'Emotional', 'rank-math' ),
			'Empathetic'     => esc_html__( 'Empathetic', 'rank-math' ),
			'Expository'     => esc_html__( 'Expository', 'rank-math' ),
			'Factual'        => esc_html__( 'Factual', 'rank-math' ),
			'Formal'         => esc_html__( 'Formal', 'rank-math' ),
			'Friendly'       => esc_html__( 'Friendly', 'rank-math' ),
			'Humorous'       => esc_html__( 'Humorous', 'rank-math' ),
			'Informal'       => esc_html__( 'Informal', 'rank-math' ),
			'Journalese'     => esc_html__( 'Journalese', 'rank-math' ),
			'Narrative'      => esc_html__( 'Narrative', 'rank-math' ),
			'Objective'      => esc_html__( 'Objective', 'rank-math' ),
			'Opinionated'    => esc_html__( 'Opinionated', 'rank-math' ),
			'Persuasive'     => esc_html__( 'Persuasive', 'rank-math' ),
			'Poetic'         => esc_html__( 'Poetic', 'rank-math' ),
			'Satirical'      => esc_html__( 'Satirical', 'rank-math' ),
			'Story-telling'  => esc_html__( 'Story-telling', 'rank-math' ),
			'Subjective'     => esc_html__( 'Subjective', 'rank-math' ),
			'Technical'      => esc_html__( 'Technical', 'rank-math' ),
		],
	],
);

$cmb->add_field(
	[
		'id'         => 'content_ai_audience',
		'type'       => 'select',
		'name'       => esc_html__( 'Default Audience', 'rank-math' ),
		'desc'       => esc_html__( 'This option lets you set the default audience that usually reads your content. You can override this in individual tools.', 'rank-math' ),
		'default'    => 'General Audience',
		'attributes' => ( 'data-s2' ),
		'options'    => [
			'Activists'                => esc_html__( 'Activists', 'rank-math' ),
			'Artists'                  => esc_html__( 'Artists', 'rank-math' ),
			'Authors'                  => esc_html__( 'Authors', 'rank-math' ),
			'Bargain Hunters'          => esc_html__( 'Bargain Hunters', 'rank-math' ),
			'Bloggers'                 => esc_html__( 'Bloggers', 'rank-math' ),
			'Business Owners'          => esc_html__( 'Business Owners', 'rank-math' ),
			'Collectors'               => esc_html__( 'Collectors', 'rank-math' ),
			'Cooks'                    => esc_html__( 'Cooks', 'rank-math' ),
			'Crafters'                 => esc_html__( 'Crafters', 'rank-math' ),
			'Dancers'                  => esc_html__( 'Dancers', 'rank-math' ),
			'DIYers'                   => esc_html__( 'DIYers', 'rank-math' ),
			'Designers'                => esc_html__( 'Designers', 'rank-math' ),
			'Educators'                => esc_html__( 'Educators', 'rank-math' ),
			'Engineers'                => esc_html__( 'Engineers', 'rank-math' ),
			'Entrepreneurs'            => esc_html__( 'Entrepreneurs', 'rank-math' ),
			'Environmentalists'        => esc_html__( 'Environmentalists', 'rank-math' ),
			'Fashionistas'             => esc_html__( 'Fashionistas', 'rank-math' ),
			'Fitness Enthusiasts'      => esc_html__( 'Fitness Enthusiasts', 'rank-math' ),
			'Foodies'                  => esc_html__( 'Foodies', 'rank-math' ),
			'Gaming Enthusiasts'       => esc_html__( 'Gaming Enthusiasts', 'rank-math' ),
			'Gardeners'                => esc_html__( 'Gardeners', 'rank-math' ),
			'General Audience'         => esc_html__( 'General Audience', 'rank-math' ),
			'Health Enthusiasts'       => esc_html__( 'Health Enthusiasts', 'rank-math' ),
			'Healthcare Professionals' => esc_html__( 'Healthcare Professionals', 'rank-math' ),
			'Indoor Hobbyists'         => esc_html__( 'Indoor Hobbyists', 'rank-math' ),
			'Investors'                => esc_html__( 'Investors', 'rank-math' ),
			'Job Seekers'              => esc_html__( 'Job Seekers', 'rank-math' ),
			'Movie Buffs'              => esc_html__( 'Movie Buffs', 'rank-math' ),
			'Musicians'                => esc_html__( 'Musicians', 'rank-math' ),
			'Outdoor Enthusiasts'      => esc_html__( 'Outdoor Enthusiasts', 'rank-math' ),
			'Parents'                  => esc_html__( 'Parents', 'rank-math' ),
			'Pet Owners'               => esc_html__( 'Pet Owners', 'rank-math' ),
			'Photographers'            => esc_html__( 'Photographers', 'rank-math' ),
			'Podcast Listeners'        => esc_html__( 'Podcast Listeners', 'rank-math' ),
			'Professionals'            => esc_html__( 'Professionals', 'rank-math' ),
			'Retirees'                 => esc_html__( 'Retirees', 'rank-math' ),
			'Russian'                  => esc_html__( 'Russian', 'rank-math' ),
			'Seniors'                  => esc_html__( 'Seniors', 'rank-math' ),
			'Social Media Users'       => esc_html__( 'Social Media Users', 'rank-math' ),
			'Sports Fans'              => esc_html__( 'Sports Fans', 'rank-math' ),
			'Students'                 => esc_html__( 'Students', 'rank-math' ),
			'Tech Enthusiasts'         => esc_html__( 'Tech Enthusiasts', 'rank-math' ),
			'Travelers'                => esc_html__( 'Travelers', 'rank-math' ),
			'TV Enthusiasts'           => esc_html__( 'TV Enthusiasts', 'rank-math' ),
			'Video Creators'           => esc_html__( 'Video Creators', 'rank-math' ),
			'Writers'                  => esc_html__( 'Writers', 'rank-math' ),
		],
	],
);

$cmb->add_field(
	[
		'id'         => 'content_ai_language',
		'type'       => 'select',
		'name'       => esc_html__( 'Default Language', 'rank-math' ),
		'desc'       => esc_html__( 'This option lets you set the default language for content generated using Content AI. You can override this in individual tools.', 'rank-math' ),
		'default'    => Helper::content_ai_default_language(),
		'attributes' => ( 'data-s2' ),
		'options'    => [
			'US English' => esc_html__( 'US English', 'rank-math' ),
			'UK English' => esc_html__( 'UK English', 'rank-math' ),
			'Bulgarian'  => esc_html__( 'Bulgarian', 'rank-math' ),
			'Chinese'    => esc_html__( 'Chinese', 'rank-math' ),
			'Czech'      => esc_html__( 'Czech', 'rank-math' ),
			'Danish'     => esc_html__( 'Danish', 'rank-math' ),
			'Dutch'      => esc_html__( 'Dutch', 'rank-math' ),
			'Estonian'   => esc_html__( 'Estonian', 'rank-math' ),
			'Finnish'    => esc_html__( 'Finnish', 'rank-math' ),
			'French'     => esc_html__( 'French', 'rank-math' ),
			'German'     => esc_html__( 'German', 'rank-math' ),
			'Greek'      => esc_html__( 'Greek', 'rank-math' ),
			'Hebrew'     => esc_html__( 'Hebrew', 'rank-math' ),
			'Hungarian'  => esc_html__( 'Hungarian', 'rank-math' ),
			'Indonesian' => esc_html__( 'Indonesian', 'rank-math' ),
			'Italian'    => esc_html__( 'Italian', 'rank-math' ),
			'Japanese'   => esc_html__( 'Japanese', 'rank-math' ),
			'Korean'     => esc_html__( 'Korean', 'rank-math' ),
			'Latvian'    => esc_html__( 'Latvian', 'rank-math' ),
			'Lithuanian' => esc_html__( 'Lithuanian', 'rank-math' ),
			'Norwegian'  => esc_html__( 'Norwegian', 'rank-math' ),
			'Polish'     => esc_html__( 'Polish', 'rank-math' ),
			'Portuguese' => esc_html__( 'Portuguese', 'rank-math' ),
			'Romanian'   => esc_html__( 'Romanian', 'rank-math' ),
			'Russian'    => esc_html__( 'Russian', 'rank-math' ),
			'Slovak'     => esc_html__( 'Slovak', 'rank-math' ),
			'Slovenian'  => esc_html__( 'Slovenian', 'rank-math' ),
			'Spanish'    => esc_html__( 'Spanish', 'rank-math' ),
			'Swedish'    => esc_html__( 'Swedish', 'rank-math' ),
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
		'name'    => esc_html__( 'Select Post Type', 'rank-math' ),
		'desc'    => esc_html__( 'Choose the type of posts/pages/CPTs where you want to use Content AI.', 'rank-math' ),
		'options' => $post_types,
		'default' => array_keys( $post_types ),
	]
);

$credits = Helper::get_credits();
if ( Helper::is_site_connected() && false !== $credits ) {
	$update_credits = '<a href="#" class="rank-math-tooltip update-credit">
		<i class="dashicons dashicons-image-rotate"></i>
		<span>' . esc_html__( 'Click to refresh the available credits.', 'rank-math' ) . '</span>
	</a>';

	$refresh_date = Helper::get_content_ai_refresh_date();
	$cmb->add_field(
		[
			'id'      => 'content_ai_credits',
			'type'    => 'raw',
			/* translators: 1. Credits left 2. Buy more credits link */
			'content' => '<div class="cmb-row buy-more-credits rank-math-exclude-from-search">' . $update_credits . sprintf( esc_html__( '%1$s credits left this month. Credits will renew on %2$s or you can upgrade to get more credits %3$s.', 'rank-math' ), '<strong>' . $credits . '</strong>', wp_date( 'Y-m-d g:i a', $refresh_date ), '<a href="' . KB::get( 'content-ai-pricing-tables', 'Buy CAI Credits Options Panel' ) . '" target="_blank">' . esc_html__( 'here', 'rank-math' ) . '</a>' ) . '</div>',
		]
	);
}
