![WordPress Plugin Active Installs](https://img.shields.io/wordpress/plugin/installs/seo-by-rank-math?color=%234098d7&style=for-the-badge) ![WordPress Plugin Downloads](https://img.shields.io/wordpress/plugin/dt/seo-by-rank-math?color=%234098d7&style=for-the-badge)

Rank Math SEO is the [Best WordPress SEO plugin](https://rankmath.com) combines the features of many SEO tools in a single package & helps you multiply your SEO traffic.

## Getting Started

These instructions will help you to get the plugin up and running on your local machine for development and testing purposes.

## ⚠️ Prerequisites

We recommend using these tools for the development of Rank Math.

 - Local by Flywheel: For installing WordPress. Create two sites, one for regular development and another one for the fresh installation testing. You can use the [WP Reset](https://wordpress.org/plugins/wp-reset/) plugin to reset the fresh installation site after testing.
	 - Make sure [PHPCS & WPCS](https://www.edmundcwm.com/setting-up-wordpress-coding-standards-in-vs-code/) are installed and working properly
 - VS Code Editor, here is the list of recommended extensions:
 	* markdown-all-in-one
	* minifyall
	* php-debug
	* php-intellisense
	* php-pack
	* vscode-colorize
	* vscode-csscomb
	* vscode-eslint
	* vscode-html-css
	* vscode-html-format
	* vscode-intelephense-client
	* vscode-phpsab
	* vscode-scss
	* vscode-wordpress-hooks
	* wordpress-toolbox
 - [Github for desktop](https://desktop.github.com/)
 - Chrome Browser

### Required WP Plugins:
- [Query Monitor](https://wordpress.org/plugins/query-monitor/)
- [Classic Editor](https://wordpress.org/plugins/classic-editor/), to test the CE integration
- [RTL Tester](https://wordpress.org/plugins/rtl-tester/), to test the RTL related styling issues

Rank Math also requires [Node.js](https://nodejs.org/). The Project is built using the latest active LTS release of the Node and the latest version of NPM.

Refer to [this tutorial](https://docs.npmjs.com/downloading-and-installing-node-js-and-npm) to download and install Node.js and NPM

After installing Node, run the below command in the plugin directory to install all the required dependencies.

`npm ci`

## 🏗️ Development

**Analytics Module:**

`npm run devca` - Development

`npm run ca` - Production

**Analytics Module:**

`npm run deva` - Development

`npm run a' - Production

**Schema Module**

`npm run devs` - Development

`npm run s` - Production

**Rest of the Files**

`npm run dev` - Development

`npm run build` - Production

And use `npm run dist` before the final commit, this command will regenerate the final JS, CSS, and pot files.

**CSS Compilatoin**
`gulp watch`

### Issue Formatting:
- Issue title should start with the Module name, for example `[Analytics Module/Feature]: Issue Text`
- Make sure to add all details related to the issue in the description area, so everyone can understand it

### Pull Request Formatting
 - Before creating a new branch for any issue, make sure the issue is created, if not please create it before creating a new branch and PR
 - Branch slug format should be `fixed-issueid`
 - PR Title should start with the `Fixed #issueid: Issue Title`
 - Make sure to close the issue from the PR description area [ `Closes/Fixes #issueid`]
