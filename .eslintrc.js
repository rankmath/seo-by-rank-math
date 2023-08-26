module.exports = {
	root: true,
	parser: 'babel-eslint',
	extends: [
		'plugin:@wordpress/eslint-plugin/recommended',
		'plugin:eslint-comments/recommended',
	],
	env: {
		browser: false,
		es6: true,
		node: true,
		mocha: true,
	},
	parserOptions: {
		sourceType: "module",
		ecmaFeatures: {
			jsx: true
		}
	},
	globals: {
		_: true,
		wp: true,
		wpApiSettings: true,
		window: true,
		document: true,
		rankMath: true,
		rankMathAdmin: true,
		rankMathEditor: true,
		rankMathGutenberg: true,
		ClipboardJS: true,
		rankMathAnalyzer: true,
		AnalysisResult: true,
		ElementorConfig: true,
		elementor: true,
		$e: true,
		tinymce: true,
	},
	settings: {
		react: {
			pragma: "wp"
		},
		jsdoc: {
			preferredTypes: ["jqXHR"]
		}
	},
	rules: {
		"array-bracket-spacing": ["error", "always"],
		"brace-style": ["error", "1tbs"],
		"camelcase": ["error", { "properties": "never" }],
		"comma-dangle": ["error", "always-multiline"],
		"comma-spacing": "error",
		"comma-style": "error",
		"computed-property-spacing": ["error", "always"],
		"constructor-super": "error",
		"dot-notation": "error",
		"eol-last": "error",
		"eqeqeq": "error",
		"func-call-spacing": "error",
		"indent": ["error", "tab", { "SwitchCase": 1 }],
		"key-spacing": "error",
		"keyword-spacing": "error",
		"lines-around-comment": "off",
		"no-alert": "error",
		"no-bitwise": "error",
		"no-caller": "error",
		"no-console": "error",
		"no-const-assign": "error",
		"no-debugger": "error",
		"no-dupe-args": "error",
		"no-dupe-class-members": "error",
		"no-dupe-keys": "error",
		"no-duplicate-case": "error",
		"no-duplicate-imports": "error",
		"no-else-return": "error",
		"no-eval": "error",
		"no-extra-semi": "error",
		"no-fallthrough": "error",
		"no-lonely-if": "error",
		"no-mixed-operators": "error",
		"no-mixed-spaces-and-tabs": "error",
		"no-multiple-empty-lines": ["error", { "max": 1 }],
		"no-multi-spaces": "error",
		"no-multi-str": "off",
		"no-negated-in-lhs": "error",
		"no-nested-ternary": "error",
		"no-redeclare": "error",
		"no-restricted-syntax": [
			"error",
			{
				"selector":
					"ImportDeclaration[source.value=/^@wordpress\\u002F.+\\u002F/]",
				"message": "Path access on WordPress dependencies is not allowed."
			},
			{
				"selector": "ImportDeclaration[source.value=/^blocks$/]",
				"message": "Use @wordpress/blocks as import path instead."
			},
			{
				"selector": "ImportDeclaration[source.value=/^components$/]",
				"message": "Use @wordpress/components as import path instead."
			},
			{
				"selector": "ImportDeclaration[source.value=/^date$/]",
				"message": "Use @wordpress/date as import path instead."
			},
			{
				"selector": "ImportDeclaration[source.value=/^editor$/]",
				"message": "Use @wordpress/editor as import path instead."
			},
			{
				"selector": "ImportDeclaration[source.value=/^element$/]",
				"message": "Use @wordpress/element as import path instead."
			},
			{
				"selector": "ImportDeclaration[source.value=/^i18n$/]",
				"message": "Use @wordpress/i18n as import path instead."
			},
			{
				"selector": "ImportDeclaration[source.value=/^data$/]",
				"message": "Use @wordpress/data as import path instead."
			},
			{
				"selector": "ImportDeclaration[source.value=/^utils$/]",
				"message": "Use @wordpress/utils as import path instead."
			},
			{
				"selector":
					"CallExpression[callee.name=/^__|_n|_x$/]:not([arguments.0.type=/^Literal|BinaryExpression$/])",
				"message": "Translate function arguments must be string literals."
			},
			{
				"selector":
					"CallExpression[callee.name=/^_n|_x$/]:not([arguments.1.type=/^Literal|BinaryExpression$/])",
				"message": "Translate function arguments must be string literals."
			},
			{
				"selector":
					"CallExpression[callee.name=_nx]:not([arguments.2.type=/^Literal|BinaryExpression$/])",
				"message": "Translate function arguments must be string literals."
			}
		],
		"no-shadow": "error",
		"no-undef": "error",
		"no-undef-init": "error",
		"no-unreachable": "error",
		"no-unsafe-negation": "error",
		"no-unused-expressions": "error",
		"no-unused-vars": "error",
		"no-useless-computed-key": "error",
		"no-useless-constructor": "error",
		"no-useless-return": "error",
		"no-var": "error",
		"no-whitespace-before-property": "error",
		"object-curly-spacing": ["error", "always"],
		"padded-blocks": ["error", "never"],
		"prefer-const": "error",
		"quote-props": ["error", "as-needed"],
		"semi": [1, "never"],
		"semi-spacing": "error",
		"space-before-blocks": ["error", "always"],
		"space-before-function-paren": ["error", "never"],
		"space-in-parens": ["error", "always"],
		"space-infix-ops": ["error", { "int32Hint": false }],
		"space-unary-ops": [
			"error",
			{
				"overrides": {
					"!": true
				}
			}
		],
		"template-curly-spacing": ["error", "always"],
		"valid-jsdoc": ["error", { "requireReturn": false }],
		"valid-typeof": "error",
		"yoda": "off"
	}
}
