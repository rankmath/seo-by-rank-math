/**
 * External Dependencies
 */
import $ from 'jquery'
import { isEmpty, forEach, some } from 'lodash'

/**
 * Internal Dependencies
 */
import Button from './buttons/Button'
import CheckboxControl from './controls/CheckboxControl'
import CheckboxList from './controls/CheckboxList'
import RadioControl from './controls/RadioControl'
import ToggleControl from './controls/ToggleControl'
import Group from './group/Group'
import RepeatableGroup from './group/RepeatableGroup'
import DatePicker from './inputs/DatePicker'
import TextareaControl from './inputs/TextareaControl'
import TextControl from './inputs/TextControl'
import Table from './layouts/Table'
import Notice from './notice/Notice'
import InvalidSiteUrlNotice from './notice/InvalidSiteUrlNotice'
import HelpTooltip from './others/HelpTooltip'
import PrivacyBox from './others/PrivacyBox'
import ProBadge from './others/ProBadge'
import Rating from './others/Rating'
import SocialShare from './others/SocialShare'
import Tooltip from './others/Tooltip'
import UploadFile from './others/UploadFile'
import SelectControl from './select/SelectControl'
import SelectVariable from './select/SelectVariable'
import SelectWithSearch from './select/SelectWithSearch'
import SearchPage from './select/SearchPage'
import ToggleGroupControl from './select/ToggleGroupControl'
import StatusAnchorTag from './status/StatusAnchorTag'
import StatusButton from './status/StatusButton'
import StatusList from './status/StatusList'
import useClickOutside from './hooks/useClickOutside'
import TabPanel from './tabs/TabPanel'
import AnalyzerResult from './analyzer-result'
import ProgressBar from './others/ProgressBar'
import Graphs from './analyzer-result/Graphs'
import getCategoryLabel from './analyzer-result/helpers/getCategoryLabel'
import getStatusIcons from './analyzer-result/helpers/getStatusIcons'
import getStatusLabels from './analyzer-result/helpers/getStatusLabels'
import DashboardHeader from './dashboard-header'

window.rankMathComponents = {
	DashboardHeader,
	Button,
	CheckboxControl,
	CheckboxList,
	RadioControl,
	ToggleControl,
	Group,
	RepeatableGroup,
	DatePicker,
	TextareaControl,
	TextControl,
	Table,
	Notice,
	InvalidSiteUrlNotice,
	HelpTooltip,
	PrivacyBox,
	ProBadge,
	Rating,
	SocialShare,
	Tooltip,
	UploadFile,
	SelectControl,
	SelectVariable,
	SelectWithSearch,
	SearchPage,
	ToggleGroupControl,
	StatusAnchorTag,
	StatusButton,
	StatusList,
	useClickOutside,
	TabPanel,
	AnalyzerResult,
	ProgressBar,
	Graphs,
	getCategoryLabel,
	getStatusIcons,
	getStatusLabels,
}

$( function() {
	const body = $( 'body' )

	const pages = [
		'rank-math_page_rank-math-role-manager',
		'rank-math_page_rank-math-seo-analysis',
		'rank-math_page_rank-math-status',
		'toplevel_page_rank-math',
		'rank-math_page_rank-math-options-general',
		'rank-math_page_rank-math-options-titles',
		'rank-math_page_rank-math-options-sitemap',
		'rank-math_page_rank-math-options-instant-indexing',
	]

	if ( ! some( pages, ( page ) => body.hasClass( page ) ) ) {
		return
	}

	/**
	 * Move notice below Breadcrumbs
	 */
	const moveNoticeBelowBreadcrumbs = () => {
		const notices = document.querySelectorAll( '#wpbody-content > .rank-math-notice' )
		const targetLocation = document.querySelector( '.rank-math-wrap' )

		if ( isEmpty( notices ) || ! targetLocation ) {
			return
		}

		forEach( notices, ( notice ) => {
			targetLocation.insertAdjacentElement( 'afterbegin', notice )
		} )
	}

	setTimeout( () => {
		moveNoticeBelowBreadcrumbs()
	}, 50 )
} )
