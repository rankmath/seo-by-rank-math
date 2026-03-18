/**
 * Internal dependencies
 */
import ErrorCTA from '@components/ErrorCTA'

const KeywordMaps = () => {
	const imagesUrl = window.rankMath.links?.imagesUrl || ''

	return (
		<div className="rank-math-pro-tab-preview">
			<img src={ imagesUrl + 'keyword-maps.jpg' } alt="" />
			<ErrorCTA showProNotice={ true } width="width-50" medium="Keyword+Maps" />
		</div>
	)
}

export default KeywordMaps
