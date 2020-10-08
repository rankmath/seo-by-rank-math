/**
 * External dependencies
 */
import { get } from 'lodash'
import DefaultTooltipContent from 'recharts/lib/component/DefaultTooltipContent'

const CustomTooltip = ( props ) => {
	const payload = get( props, 'payload.0', false )
	if ( false !== payload ) {
		return <DefaultTooltipContent { ...props } label={ payload.payload.dateFormatted } />
	}

	return <DefaultTooltipContent { ...props } />
}

export default CustomTooltip
