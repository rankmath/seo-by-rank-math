<?php
/**
 * Help Sitemaps tab.
 *
 * @package    RankMath
 * @subpackage RankMath\Sitemap
 */

use RankMath\KB;
?>
<h3><?php esc_html_e( 'Sitemaps', 'rank-math' ); ?></h3>

<p><?php esc_html_e( 'XML sitemaps are files that serve a single purpose - to help search engines find and index all of your content.', 'rank-math' ); ?></p>

<p><?php esc_html_e( 'Once upon a time, search engine bots would have a hard time finding all the content on a site. Many sites had a weak internal link structure, and that meant that some pages or posts would go overlooked.', 'rank-math' ); ?></p>

<p><?php esc_html_e( 'The early search engines gave us forms where we could enter our URLs one at a time. They\'d feed this information into their queue of pages to crawl, and the pages would get visited and indexed at some point.', 'rank-math' ); ?></p>

<p><?php esc_html_e( 'Typing in dozens or even hundreds of URLs was a tedious job - in fact, many companies popped up offering to submit your site to the search engines, because who had the time to do that?', 'rank-math' ); ?></p>

<p><?php esc_html_e( 'When Google appeared on the scene, people soon noticed that submitting pages didn\'t lead to fast crawls. Linking to a page from another page (even on the same site) would get the bot to visit quickly.', 'rank-math' ); ?></p>

<p><?php esc_html_e( 'To solve the problem of getting pages crawled and indexed, many webmasters created a "sitemap". It was an ugly HTML page with nothing but links to all the site\'s content. It was a bit of an eyesore, and human users had little use for it. But it helped the spiders to find pages and it got them into the index.', 'rank-math' ); ?></p>

<p><?php esc_html_e( 'Search engines realized that people were building these pages that didn\'t do anything for human visitors, and they decided to cut us a break. They invented a new XML based file that we could use to communicate directly with the robots. We could announce our new pages without cluttering up our sites with pointless pages.', 'rank-math' ); ?></p>

<p><a href="<?php KB::the( 'configure-sitemaps' ); ?>" target="_blank"><?php esc_html_e( 'Click here to read how to configure Sitemaps', 'rank-math' ); ?></a></p>
