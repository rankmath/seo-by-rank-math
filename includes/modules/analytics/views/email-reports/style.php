<?php
/**
 * Analytics Report email styling.
 *
 * @package    RankMath
 * @subpackage RankMath\Admin
 */

defined( 'ABSPATH' ) || exit;
?>
<style>
	/* -------------------------------------
			GLOBAL RESETS
	------------------------------------- */
	/* All the styling goes here */
	img {
		border: none;
		-ms-interpolation-mode: bicubic;
		max-width: 100%; 
	}

	body {
		background-color: #f7f9fb;
		-webkit-font-smoothing: antialiased;
		font-size: 14px;
		line-height: 1.4;
		margin: 0;
		padding: 0;
		-ms-text-size-adjust: 100%;
		-webkit-text-size-adjust: 100%; 
	}

	table {
		border-collapse: separate;
		mso-table-lspace: 0pt;
		mso-table-rspace: 0pt;
		width: 100%;
	}

	table td {
		font-size: 15px;
		vertical-align: top; 
	}

	/* -------------------------------------
			BODY & CONTAINER
	------------------------------------- */

	.body, td {
		font-family: -apple-system,BlinkMacSystemFont,"Segoe UI","Roboto","Oxygen","Ubuntu","Cantarell","Fira Sans","Droid Sans","Helvetica Neue",sans-serif;
	}

	.body {
		background-color: #F0F4F8;
		width: 100%; 
	}

	/* Set a max-width, and make it display as block. */
	.container {
		display: block;
		margin: 0 auto !important;
		/* makes it centered */
		max-width: 90%;
		padding: 50px 0;
		width: 600px; 
	}

	.content {
		box-sizing: border-box;
		display: block;
		margin: 0 auto;
		width: 100%;
	}

	/* -------------------------------------
			HEADER, FOOTER, MAIN
	------------------------------------- */
	.main {
		background: #ffffff;
		border-radius: 6px;
		width: 100%;
		color: #1a1e22;
	}

	.wrapper {
		box-sizing: border-box;
		padding: 30px 30px 60px;
	}

	.header {
		background: #724BB7;
		background: linear-gradient(90deg, #724BB7 0%, #4098D7 100%);
		border-radius: 8px 8px 0 0;
		height: 76px;
		vertical-align: middle;
		padding: 0 30px;
		color: #ffffff;
	}

	td.logo {
		vertical-align: middle;
	}

	td.logo img {
		width: auto;
		height: 26px;
		margin-top: 6px;
	}

	.period-days {
		text-align: right;
		vertical-align: middle;
		font-weight: 500;
		letter-spacing: 0.5px;
		font-size: 14px;
	}

	.content-block {
		padding-bottom: 10px;
		padding-top: 10px;
	}

	.footer {
		clear: both;
		margin-top: 10px;
		width: 100%; 
	}

	.footer .wrapper {
		padding-bottom: 30px;
	}

	.footer td,
	.footer p,
	.footer span {
		color: #999ba7;
		font-size: 14px; 
	}

	.footer td {
		padding-top: 0;
	}

	.footer p.first {
		padding-top: 20px;
		border-top: 1px solid #e5e5e7;
		line-height: 1.8;
		margin-bottom: 0;
	}

	.footer .rank-math-contact-address {
		font-style: normal;
	}

	.footer p:empty {
		display: none;
	}

	.footer address {
		display: inline-block;
		font-style: normal;
		margin-top: 10px;
	}

	/* -------------------------------------
			TYPOGRAPHY
	------------------------------------- */
	h1,
	h2,
	h3,
	h4 {
		color: #000000;
		font-weight: 600;
		line-height: 1.4;
		margin: 0;
	}

	h1 {
		font-size: 30px;
	}

	p,
	ul,
	ol {
		font-size: 14px;
		font-weight: normal;
		margin: 0;
		margin-bottom: 15px; 
	}
		p li,
		ul li,
		ol li {
			list-style-position: inside;
			margin-left: 5px; 
	}

	a {
		color: #22a8e6;
		text-decoration: none; 
	}

	h2.report-date {
		margin: 25px 0 4px;
		font-size: 18px;
	}

	.site-url {
		color: #595d6f;
		text-decoration: none;
		font-size: 15px;
	}

	.full-report-link {
		vertical-align: bottom;
		text-align: right;
		width: 110px;
	}

	.full-report-link a {
		font-size: 12px;
		font-weight: 600;
		text-decoration: none;
	}

	.full-report-link img {
		vertical-align: -1px;
		margin-left: 2px;
	}

	table.report-error {
		border: 2px solid #f1d400;
		background: #fffdec;
		margin: 10px 0;
	}

	table.report-error td {
		padding: 5px 10px;
	}
	table.stats {
		border-collapse: separate;
		margin-top: 10px;
	}

	table.stats td {
		width: 50%;
		padding: 20px 20px;
		background: #f7f9fb;
		border: 10px solid #fff;
		border-radius: 16px;
	}

	table.stats td.col-2 {
		border-right: none;
	}

	table.stats td.col-1 {
		border-left: none;
	}

	h3 {
		font-size: 13px;
		font-weight: 500;
		color: #565a6b;
		text-transform: uppercase;
	}

	.stat-value {
		color: #000000;
		font-size: 25px;
		font-weight: 700;
	}

	.stat-diff {
		font-size: 14px;
		font-weight: 500;
	}

	.stat-diff.positive {
		color: #339e75;
	}

	span.stat-diff.negative {
		color: #e2454f;
	}

	.stat-diff.no-diff {
		color: #999ba7;
	}

	.diff-sign {
		font-size: 10px;
	}

	.stats-2 {
		margin: 50px 0 24px;
	}

	.stats-2 td.col-1, .stats-2 td.col-2 {
		border-right: 3px solid #f7f9fb;
	}

	.stats-2 td.col-2, .stats-2 td.col-3 {
		padding-left: 40px;
	}

	.cta {
		margin-bottom: 0;
	}

	/* -------------------------------------
			BUTTONS
	------------------------------------- */
	.btn {
		box-sizing: border-box;
		width: 100%;
	}

	.btn > tbody > tr > td {
		padding-bottom: 48px;
		text-align: center;
		padding-top: 34px;
	}

	.btn table {
		width: auto; 
	}

	.btn table td {
		background-color: #ffffff;
		border-radius: 5px;
		text-align: center; 
	}

	.btn a {
		border: none;
		border-radius: 31px;
		box-sizing: border-box;
		color: #59403b;
		cursor: pointer;
		display: inline-block;
		font-size: 16px;
		font-weight: 700;
		margin: 0;
		padding: 18px 44px;
		text-decoration: none;
		text-transform: capitalize;
		background: rgb(47,166,129);
		background: linear-gradient( 0deg, #f7d070 0%, #f7dc6f 100%);
		letter-spacing: 0.7px;
	}

	.btn-primary table td {
		background-color: #3498db;
	}

	/* -------------------------------------
			OTHER STYLES THAT MIGHT BE USEFUL
	------------------------------------- */
	.last {
		margin-bottom: 0; 
	}

	.first {
		margin-top: 0; 
	}

	.align-center {
		text-align: center; 
	}

	.align-right {
		text-align: right; 
	}

	.align-left {
		text-align: left; 
	}

	.clear {
		clear: both; 
	}

	.mt0 {
		margin-top: 0; 
	}

	.mb0 {
		margin-bottom: 0; 
	}

	.preheader {
		color: transparent;
		display: none;
		height: 0;
		max-height: 0;
		max-width: 0;
		opacity: 0;
		overflow: hidden;
		mso-hide: all;
		visibility: hidden;
		width: 0; 
	}

	hr {
		border: 0;
		border-bottom: 1px solid #F0F4F8;
		margin: 20px 0; 
	}

	/* -------------------------------------
			RESPONSIVE AND MOBILE FRIENDLY STYLES
	------------------------------------- */
	@media only screen and (max-width: 620px) {
		table[class=body] h1 {
			font-size: 28px !important;
			margin-bottom: 10px !important; 
		}
		table[class=body] p,
		table[class=body] ul,
		table[class=body] ol,
		table[class=body] td,
		table[class=body] span,
		table[class=body] a {
			font-size: 16px !important; 
		}
		table[class=body] .wrapper,
		table[class=body] .article {
			padding: 10px !important; 
		}
		table[class=body] .content {
			padding: 0 !important; 
		}
		table[class=body] .container {
			padding: 0 !important;
			width: 100% !important; 
		}
		table[class=body] .main {
			border-left-width: 0 !important;
			border-radius: 0 !important;
			border-right-width: 0 !important; 
		}
		table[class=body] .btn table {
			width: 100% !important; 
		}
		table[class=body] .btn a {
			width: 100% !important; 
		}
		table[class=body] .img-responsive {
			height: auto !important;
			max-width: 100% !important;
			width: auto !important; 
		}
	}

	/* -------------------------------------
			PRESERVE THESE STYLES IN THE HEAD
	------------------------------------- */
	@media all {
		.ExternalClass {
			width: 100%; 
		}
		.ExternalClass,
		.ExternalClass p,
		.ExternalClass span,
		.ExternalClass font,
		.ExternalClass td,
		.ExternalClass div {
			line-height: 100%; 
		}
		.rankmath-link a {
			color: inherit !important;
			font-family: inherit !important;
			font-size: inherit !important;
			font-weight: inherit !important;
			line-height: inherit !important;
			text-decoration: none !important; 
		}
		#MessageViewBody a {
			color: inherit;
			text-decoration: none;
			font-size: inherit;
			font-family: inherit;
			font-weight: inherit;
			line-height: inherit;
		}
		.btn-primary table td:hover {
			background-color: #34495e !important; 
		}
		.btn-primary a:hover {
			background-color: #34495e !important;
			border-color: #34495e !important; 
		} 
	}
</style>

<?php $this->template_part( 'pro-style' ); ?>
