const resolve = require( 'path' ).resolve
const TerserPlugin = require( 'terser-webpack-plugin' )

const externals = {
	jquery: 'jQuery',
	lodash: 'lodash',
	react: 'React',
	moment: 'moment',
	'react-dom': 'ReactDOM',
	'@yaireo/tagify': 'Tagify',
	'@rankMath/analyzer': 'rankMathAnalyzer',

	// WordPress Packages.
	'@wordpress/api-fetch': 'wp.apiFetch',
	'@wordpress/blocks': 'wp.blocks',
	'@wordpress/block-editor': 'wp.blockEditor',
	'@wordpress/components': 'wp.components',
	'@wordpress/compose': 'wp.compose',
	'@wordpress/data': 'wp.data',
	'@wordpress/date': 'wp.date',
	'@wordpress/dom': 'wp.dom',
	'@wordpress/editor': 'wp.editor',
	'@wordpress/edit-post': 'wp.editPost',
	'@wordpress/element': 'wp.element',
	'@wordpress/hooks': 'wp.hooks',
	'@wordpress/html-entities': 'wp.htmlEntities',
	'@wordpress/i18n': 'wp.i18n',
	'@wordpress/keycodes': 'wp.keycodes',
	'@wordpress/media-utils': 'wp.mediaUtils',
	'@wordpress/plugins': 'wp.plugins',
	'@wordpress/rich-text': 'wp.richText',
	'@wordpress/url': 'wp.url',

	// Elementor
	Marionette: 'Marionette',
	'@elementor/modules': 'window.elementorModules',
}

const alias = {
	'@root': resolve( __dirname, './assets/admin/src/' ),
	'@blocks': resolve( __dirname, './assets/admin/src/blocks' ),
	'@components': resolve(
		__dirname,
		'./assets/admin/src/sidebar/components'
	),
	'@containers': resolve(
		__dirname,
		'./assets/admin/src/sidebar/containers'
	),
	'@analytics': resolve(
		__dirname,
		'./includes/modules/analytics/assets/src'
	),
	'@schema': resolve(
		__dirname,
		'./includes/modules/schema/assets/src'
	),
	'@scShared': resolve(
		__dirname,
		'./includes/modules/analytics/assets/src/shared'
	),
	'@shared': resolve( __dirname, './assets/shared/src' ),
	'@helpers': resolve( __dirname, './assets/admin/src/helpers' ),
	'@slots': resolve( __dirname, './assets/admin/src/sidebar/slots' ),
	'@classic': resolve( __dirname, './assets/admin/src/classic' ),
}

const entryPoints = {
	plugin: {
		blocks: './assets/admin/src/blocks.js',
		classic: './assets/admin/src/classic/classic.js',
		gutenberg: './assets/admin/src/gutenberg/gutenberg.js',
		elementor: './assets/admin/src/elementor/elementor.js',
		'gutenberg-formats': './assets/admin/src/gutenberg/formats/index.js',
		'gutenberg-primary-term': './assets/admin/src/gutenberg-primary-term.js',
		'glue-custom-fields': './assets/admin/src/glue-custom-fields.js',
		common: './assets/admin/src/common.js',
		'custom-fields': './assets/admin/src/custom-fields.js',
		dashboard: './assets/admin/src/dashboard.js',
		'import-export': './assets/admin/src/import-export.js',
		'option-panel': './assets/admin/src/option-panel.js',
		'post-list': './assets/admin/src/post-list.js',
		wizard: './assets/admin/src/wizard.js',
		wplink: './assets/admin/src/wplink.js',
		validate: './assets/admin/src/validate.js',
	},
	'instant-indexing': {
		'instant-indexing': './includes/modules/instant-indexing/assets/src/instant-indexing.js',
	},
	'404-monitor': {
		'404-monitor': './includes/modules/404-monitor/assets/src/404-monitor.js',
	},
	redirections: {
		redirections: './includes/modules/redirections/assets/src/redirections.js',
	},
	acf: {
		acf: './includes/modules/acf/assets/src/index.js',
	},
	woocommerce: {
		woocommerce: './includes/modules/woocommerce/assets/src/woocommerce.js',
	},
	'role-manager': {
		'role-manager': './includes/modules/role-manager/assets/src/role-manager.js',
	},
	'seo-analysis': {
		'seo-analysis': './includes/modules/seo-analysis/assets/src/seo-analysis.js',
	},
	'version-control': {
		'version-control': './includes/modules/version-control/assets/src/version-control.js',
	},
	analytics: {
		stats: './includes/modules/analytics/assets/src/index.js',
	},
	status: {
		status: './includes/modules/status/assets/src/status.js',
	},
	front: {
		'rank-math': './assets/front/src/rank-math.js',
	},
	schema: {
		'schema-gutenberg': './includes/modules/schema/assets/src/index.js',
		'schema-classic': './includes/modules/schema/assets/src/metabox-classic.js',
		'schema-template': './includes/modules/schema/assets/src/metabox-template.js',
	},
	divi: {
		divi: './assets/admin/src/divi/divi.js',
		'divi-iframe': './assets/admin/src/divi/divi-iframe.js',
		'divi-admin': './assets/admin/src/divi/divi-admin.js',
	},
}

const paths = {
	plugin: './assets/admin/js',
	divi: './assets/admin/js',
	analytics: './includes/modules/analytics/assets/js',
	front: './assets/front/js',
	'instant-indexing': './includes/modules/instant-indexing/assets/js',
	schema: './includes/modules/schema/assets/js',
	'404-monitor': './includes/modules/404-monitor/assets/js',
	redirections: './includes/modules/redirections/assets/js',
	acf: './includes/modules/acf/assets/js',
	woocommerce: './includes/modules/woocommerce/assets/js',
	'role-manager': './includes/modules/role-manager/assets/js',
	'seo-analysis': './includes/modules/seo-analysis/assets/js',
	status: './includes/modules/status/assets/js',
	'version-control': './includes/modules/version-control/assets/js',
}

module.exports = function( env, arg ) {
	const mode =
		( env && env.environment ) ||
		process.env.NODE_ENV ||
		arg.mode ||
		'production'

	const what = arg.what || 'plugin'

	return {
		devtool:
			mode === 'development' ? 'cheap-module-eval-source-map' : false,
		entry: entryPoints[ what ],
		output: {
			path: resolve( __dirname, paths[ what ] ),
			filename: '[name].js',
		},
		resolve: {
			alias,
		},
		module: {
			rules: [
				{
					test: /\.js$/,
					exclude: /(node_modules|bower_components)/,
					loader: 'babel-loader',
					options: {
						cacheDirectory: true,
						presets: [ '@babel/preset-env' ],
					},
				},
				{
					test: /.svg$/,
					use: [ { loader: 'svg-react-loader' } ],
				},
			],
		},
		externals,
		optimization: {
			minimize: true,
			minimizer: [ new TerserPlugin( {
				parallel: true,
				extractComments: false,
				terserOptions: {
					output: {
						comments: false,
					},
				},
			} ) ],
		},
	}
}
