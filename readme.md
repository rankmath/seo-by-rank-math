![WordPress Plugin Active Installs](https://img.shields.io/wordpress/plugin/installs/seo-by-rank-math?color=%234098d7&style=for-the-badge) ![WordPress Plugin Downloads](https://img.shields.io/wordpress/plugin/dt/seo-by-rank-math?color=%234098d7&style=for-the-badge) [![Deploy to WordPress](https://github.com/rankmath/seo-by-rank-math-private/actions/workflows/deploy-wp.yml/badge.svg)](https://github.com/rankmath/seo-by-rank-math-private/actions/workflows/deploy-wp.yml)

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
- [Elementor](https://wordpress.org/plugins/elementor/), to test the Elementor integration
- [RTL Tester](https://wordpress.org/plugins/rtl-tester/), to test the RTL related styling issues

Rank Math also requires [Node.js](https://nodejs.org/). The Project is built using the latest active LTS release of the Node and the latest version of NPM.

Refer to [this tutorial](https://docs.npmjs.com/downloading-and-installing-node-js-and-npm) to download and install Node.js and NPM

After installing Node, run the below command in the plugin directory to install all the required dependencies.

`npm ci`

## 🏗️ Development

***NOTES***:
 1. `production` branch will have only the current stable released version
 2. `develop` branch will have the upcoming release
 3. Never directly commit in both of these branches, create a separate branch and send the PR request to the `develop` branch
 4. Anything which is strictly used for the development environment, make sure those files are marked `export-ignore` in the *.gitattributes* file

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

### Misc:
- When any `scss` file is edited make sure to [run the CSSComb](https://i.rankmath.com/lKelov) extension

## 🦺 Issue and Pull Request Requirements

### Issue Formatting:
- Issue title should start with the Module name, for example `[Analytics Module/Feature]: Issue Text`
- Make sure to add all details related to the issue in the description area, so everyone can understand it
- Every issue should have 2 initial labels, Type & Priority
- Product manager will assign and issue, including Project and Milestone
- If the issue is invalid or there is no plan to fix it, then before closing it and `invalid` or `won't fix` label, also add an appropriate comment mentioning why it is closed
- For the duplicate issue, add the `duplicate` label and mention the duplicate issue ID before closing it.

### Pull Request Formatting
 - Before creating a new branch for any issue, make sure the issue is created, if not please create it before creating a new branch and PR
 - Branch slug format should be `fixed-issueid`
 - PR Title should start with the `Fixed #issueid: Issue Title`
 - Make sure to close the issue from the PR description area [ `Closes/Fixes #issueid`]
 - Don't add any labels to PRs
 - Make sure at least 2 reviewers, Project and Milestone are added
 - PR author should self-assign that PR to themself
 - If changes are requested, make sure [to click](https://docs.github.com/assets/images/help/pull_requests/request-re-review.png "this icon") this icon after adding those changes, will send the new review request to the reviewer
 - If related PR is present in the PRO repo, don't forget to mention it in the description area. Ex: `Related PR in the PRO repo: PRLink`

### Merging
- PR should not be merged without minimum 2 approvals
- If you think PR might affect the plugin security(even if a small possibility), then add the `Security Review Needed` label to that PR
- Once the security review is done, the reviewer will remove that security-related label
- If PR is invalid, before closing it, add the appropriate label `invalid` or `won't merge` along with the appropriate comment

## 🏘️ Versioning
- For Versioning, we use this formatting **1.0.major.patch**, example 1.0.53.1
- Each major version will have its own Project with all the issues included for that sprint.
- Same Project will have columns for the patch releases https://i.rankmath.com/wvPzbU
- Once the next major version is released, move the rest of the remaining issues to the next sprint and close that Project

### Releasing the Update
- Product Manager will do the final release
- Once all the required PRs are merged into the `develop` branch, create a new branch `release/v*`(* = version number)
- run `npm run dist`
- Create the final PR pointing towards the `develop` branch, PR title should contain `Stable release v1.x.x` or `Beta release v1.x.x-beta`, as this pattern is detected by our servers to deliver the final release to users along with the new changelog.
- Merge into the `develop` branch, and updates are immediately delivered to our users.
- Create a GH Release
- Update the `production` branch from the `develop` branch, so in case we want to release the urgent patch, we can use the `production` branch to prepare the patch and don't have to stop the next release cycle of `develop` branch

If you face any issue while setting up the development environment, please ping anyone from the `Dev` team on Slack.
