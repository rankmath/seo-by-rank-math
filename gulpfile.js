/*eslint camelcase: ["error", {properties: "never"}]*/

const { src, dest, watch } = require( 'gulp' )
const wpPot = require( 'gulp-wp-pot' )
const checktextdomain = require( 'gulp-checktextdomain' )
const sass = require( 'gulp-sass' )
const autoprefixer = require( 'gulp-autoprefixer' )

sass.compiler = require( 'node-sass' )

const paths = {
	front: {
		src: 'assets/front/scss/**/*.scss',
		dest: 'assets/front/css',
	},
	admin: {
		src: 'assets/admin/scss/**/*.scss',
		dest: 'assets/admin/css',
	},
	schema: {
		src: 'includes/modules/schema/assets/scss/**/*.scss',
		dest: 'includes/modules/schema/assets/css',
	},
	pot: {
		src: [ '**/*.php', '!node_modules/**/*', '!vendor/**/*' ],
		dest: 'languages/rank-math.pot',
	},
}

// Quality Assurance --------------------------------------
function ct() {
	return src( paths.pot.src ).pipe(
		checktextdomain( {
			text_domain: [ 'rank-math' ],
			keywords: [
				'__:1,2d',
				'_e:1,2d',
				'_x:1,2c,3d',
				'_ex:1,2c,3d',
				'esc_html__:1,2d',
				'esc_html_e:1,2d',
				'esc_html_x:1,2c,3d',
				'esc_attr__:1,2d',
				'esc_attr_e:1,2d',
				'esc_attr_x:1,2c,3d',
				'_n:1,2,4d',
				'_nx:1,2,4c,5d',
				'_n_noop:1,2,3d',
				'_nx_noop:1,2,3c,4d',
			],
		} )
	)
}

function pot() {
	return src( paths.pot.src )
		.pipe(
			wpPot( {
				domain: 'rank-math',
				lastTranslator: 'Rank Math',
				noFilePaths: true,
				team: 'Rank Math',
			} )
		)
		.pipe( dest( paths.pot.dest ) )
}

/**
 * Converting Front Sass into css
 *  1. Applying autoprefixer
 *  2. Creatings sourcemaps
 *
 * @return {Object} Gulp source.
 */
function frontCSS() {
	return src( paths.front.src, { sourcemaps: false } )
		.pipe(
			sass( { outputStyle: 'compressed' } ).on( 'error', sass.logError )
		)
		.pipe( autoprefixer() )
		.pipe( dest( paths.front.dest, { sourcemaps: '.' } ) )
}

/**
 * Converting Admin Sass into css
 *  1. Applying autoprefixer
 *  2. Creatings sourcemaps
 *
 * @return {Object} Gulp source.
 */
function adminCSS() {
	return src( paths.admin.src, { sourcemaps: false } )
		.pipe(
			sass( { outputStyle: 'compressed' } ).on( 'error', sass.logError )
		)
		.pipe( autoprefixer() )
		.pipe( dest( paths.admin.dest, { sourcemaps: '.' } ) )
}

/**
 * Converting Schema Sass into css
 *  1. Applying autoprefixer
 *  2. Creatings sourcemaps
 *
 * @return {Object} Gulp source.
 */
function schemaCSS() {
	return src( paths.schema.src, { sourcemaps: false } )
		.pipe(
			sass( { outputStyle: 'compressed' } ).on( 'error', sass.logError )
		)
		.pipe( autoprefixer() )
		.pipe( dest( paths.schema.dest, { sourcemaps: '.' } ) )
}

function watchFiles() {
	watch( paths.front.src, frontCSS )
	watch( paths.admin.src, adminCSS )
	watch( paths.schema.src, schemaCSS )
}

exports.ct = ct
exports.pot = pot
exports.frontCSS = frontCSS
exports.adminCSS = adminCSS
exports.schemaCSS = schemaCSS
exports.watch = watchFiles
