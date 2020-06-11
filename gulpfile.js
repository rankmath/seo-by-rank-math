/*eslint camelcase: ["error", {properties: "never"}]*/

const gulp = require( 'gulp' )
const wpPot = require( 'gulp-wp-pot' )
const checktextdomain = require( 'gulp-checktextdomain' )

// Quality Assurance --------------------------------------
gulp.task( 'ct', function() {
	return gulp
		.src( [ '**/*.php', '!node_modules/**/*', '!vendor/**/*' ] )
		.pipe(
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
} )

gulp.task( 'pot', function() {
	return gulp
		.src( [ '**/*.php', '!node_modules/**/*', '!vendor/**/*' ] )
		.pipe(
			wpPot( {
				domain: 'rank-math',
				lastTranslator: 'Rank Math',
				noFilePaths: true,
				team: 'Rank Math',
			} )
		)
		.pipe( gulp.dest( 'languages/rank-math.pot' ) )
} )
