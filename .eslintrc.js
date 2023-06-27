module.exports = {
	root: true,
	extends: [
		'plugin:@wordpress/eslint-plugin/recommended-with-formatting',
		'plugin:eslint-comments/recommended',
	],
	env: {
		browser: false,
		es6: true,
		node: true,
		mocha: true,
	},
	parserOptions: {
		sourceType: 'module',
		ecmaFeatures: {
			jsx: true,
		},
	},
	globals: {
		ClipboardJS: true,
		rankMath: true,
		rankMathAdmin: true,
		rankMathEditor: true,
		tinymce: true,

		// Elementor
		$e: true,
		elementor: true,
		elementorCommon: true,
		ElementorConfig: true,
	},
	settings: {
		react: {
			pragma: 'wp',
		},
		jsdoc: {
			preferredTypes: [ 'jqXHR' ],
		},
	},
	rules: {
		semi: [ 1, 'never' ],
		'semi-spacing': 'error',

		// jsdoc.
		'jsdoc/check-access': 'off',
		'jsdoc/require-property': 'off',
		'jsdoc/require-property-type': 'off',
		'jsdoc/require-property-name': 'off',
		'jsdoc/require-property-description': 'off',
		'jsdoc/check-property-names': 'off',
		'jsdoc/empty-tags': 'off',
	},
}
