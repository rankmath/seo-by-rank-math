/**
 * External dependencies
 */
import { map } from 'lodash'

/**
 * Internal dependencies
 */
import TabContent from '@rank-math-settings/components/TabContent'
import getFields from './getFields'

export default ( { data, updateData } ) => {
	const fields = map( getFields( data ), ( field ) => {
		field.value = data[ field.id ]
		field.onChange = ( val ) => ( updateData( field.id, val ) )
		return field
	} )

	return <TabContent fields={ fields } settings={ data } />
}
