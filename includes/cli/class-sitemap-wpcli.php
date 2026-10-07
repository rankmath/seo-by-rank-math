<?php
/**
 * Rank Math sitemap CLI commands.
 *
 * @since      1.0.280
 * @package    RankMath
 * @subpackage RankMath\WP_CLI
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\CLI;

use WP_CLI;
use RankMath\Traits\Hooker;
use RankMath\Sitemap\Cache;

defined( 'ABSPATH' ) || exit;

/**
 * Class Sitemap_WPCLI.
 * Generate sitemap cache files via CLI.
 *
 * Better used with the filter add_filter( 'rank_math/sitemap/invalidate_storage', '__return_false' );
 * so cache files are only deleted and regenerated via WP CLI
 */
class Sitemap_WPCLI extends \RankMath\Sitemap\Generator {
	use Hooker;

	/**
	 * Sitemap cache instance.
	 *
	 * @var Cache
	 */
	private $cache;

	/**
	 * Number of sitemaps that could not be generated.
	 *
	 * @var int
	 */
	private $failed = 0;

	/**
	 * Holds index pages discovered through the sitemap/index/entry filter.
	 * In the form of type (eg post) -> object type (eg product) -> index count.
	 *
	 * @var array
	 */
	public $index_counts = [];

	/**
	 * Disable sitemap redirection.
	 *
	 * Sitemap providers call Sitemap::maybe_redirect() while building links, which
	 * redirects and die()s on the front-end. That must never happen under WP-CLI.
	 *
	 * @return false
	 */
	public function disable_redirect() {
		return false;
	}

	/**
	 * Force cache invalidation.
	 *
	 * Sites are advised to disable automatic invalidation via
	 * `rank_math/sitemap/invalidate_storage` so caches are only rebuilt from here.
	 * This runs at PHP_INT_MAX so it reliably wins over such a filter regardless of
	 * the priority it was registered at.
	 *
	 * @param bool        $invalidate Whether to invalidate storage.
	 * @param null|string $type       Type of storage to invalidate.
	 *
	 * @return true
	 */
	public function invalidate_storage( $invalidate, $type ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		return true;
	}

	/**
	 * Cache sitemaps.
	 *
	 * @return void
	 */
	public function cache_sitemaps() {
		$this->filter( 'rank_math/sitemap/invalidate_storage', 'invalidate_storage', PHP_INT_MAX, 2 );
		try {
			Cache::invalidate_storage();
		} finally {
			$this->remove_filter( 'rank_math/sitemap/invalidate_storage', 'invalidate_storage', PHP_INT_MAX );
		}

		$this->filter( 'rank_math/sitemap/maybe_redirect', 'disable_redirect' );
		$this->filter( 'rank_math/sitemap/index/entry', 'get_indexes_listener', 100, 3 );
		try {
			$this->generate_sitemaps();
		} finally {
			$this->remove_filter( 'rank_math/sitemap/maybe_redirect', 'disable_redirect' );
			$this->remove_filter( 'rank_math/sitemap/index/entry', 'get_indexes_listener', 100 );
		}
	}

	/**
	 * Checks whether a sitemap can be built for the given object type.
	 *
	 * A type is buildable when a provider claims it through handles_type() - the
	 * canonical check used everywhere else in the sitemap code, which already accounts
	 * for filtered slugs and per-type settings - or when it is contributed purely
	 * through the `sitemap/{$type}/content` filter, as the Local SEO KML sitemap is.
	 *
	 * @param string $object_type The object type to check, eg product or local.
	 *
	 * @return bool
	 */
	protected function is_buildable_type( $object_type ) {
		foreach ( $this->providers as $provider ) {
			if ( $provider->handles_type( $object_type ) ) {
				return true;
			}
		}

		return has_filter( "rank_math/sitemap/{$object_type}/content" );
	}

	/**
	 * Listens to the sitemap/index/entry hook and records how many index pages each
	 * object type has, so the matching sitemaps can be generated afterwards.
	 *
	 * Providers do not pass a uniform third argument: post and taxonomy pass the object
	 * type as a string, author passes a WP_User, video passes an array, while news and
	 * the Local SEO KML file pass nothing at all. Anything that is not a usable string
	 * falls back to the provider type, which is also the sitemap slug for those cases.
	 *
	 * @param array  $item        The index URL entry.
	 * @param string $type        The provider type, eg post or term.
	 * @param mixed  $object_type The object type, eg product or category. Not always a string.
	 *
	 * @return array
	 */
	public function get_indexes_listener( $item, $type = '', $object_type = null ) {
		$type = 'term' === $type ? 'taxonomy' : $type;

		if ( ! is_string( $object_type ) || '' === $object_type ) {
			$object_type = $type;
		}

		if ( '' === $object_type ) {
			return $item;
		}

		if ( ! isset( $this->index_counts[ $type ][ $object_type ] ) ) {
			$this->index_counts[ $type ][ $object_type ] = 1;
		} else {
			++$this->index_counts[ $type ][ $object_type ];
		}

		return $item;
	}

	/**
	 * Generates sitemaps and adds related cache files.
	 *
	 * @return void
	 */
	public function generate_sitemaps() {
		$this->cache  = new Cache();
		$this->failed = 0;
		$total_files  = 0;

		try {
			// Saves the rootmap i.e sitemap_index.xml.
			$total_files += $this->store( '1', 1, 'index' );

			foreach ( $this->index_counts as $items ) {
				foreach ( $items as $object_type => $count ) {
					if ( ! $this->is_buildable_type( $object_type ) ) {
						WP_CLI::warning( sprintf( 'Nothing can build a sitemap for "%s", skipping.', $object_type ) );
						continue;
					}

					$pages = max( 1, $count );
					for ( $page = 1; $page <= $pages; $page++ ) {
						$total_files += $this->store( $object_type, $page, $object_type );
					}
				}
			}
		} catch ( \Throwable $ex ) {
			WP_CLI::error( $ex->getMessage() );
		}

		if ( $this->failed > 0 ) {
			WP_CLI::error( sprintf( 'Generated %d sitemap cache file(s), %d failed.', $total_files, $this->failed ) );
		}

		WP_CLI::success( sprintf( 'Generated %d sitemap cache file(s).', $total_files ) );
	}

	/**
	 * Builds a single sitemap and writes it to the cache.
	 *
	 * @param string $type  Sitemap type, or '1' for the index sitemap.
	 * @param int    $page  Page number to generate.
	 * @param string $label Human readable name used in the CLI output.
	 *
	 * @return int 1 when the sitemap was stored, 0 otherwise.
	 */
	private function store( $type, $page, $label ) {
		$sitemap = $this->get_output( $type, $page );
		if ( '' === $sitemap ) {
			WP_CLI::warning( sprintf( 'Empty output for "%s" page %d, nothing cached.', $label, $page ) );
			++$this->failed;
			return 0;
		}

		if ( false === $this->cache->store_sitemap( $type, $page, $sitemap ) ) {
			WP_CLI::warning( sprintf( 'Could not cache "%s" page %d.', $label, $page ) );
			++$this->failed;
			return 0;
		}

		WP_CLI::log( sprintf( 'Generated sitemap for %s, page %d.', $label, $page ) );

		return 1;
	}
}
