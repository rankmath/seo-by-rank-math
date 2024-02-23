/**
 * External dependencies
 */
import { map, forEach, startCase, isEmpty, isArray, isObject } from 'lodash'

/**
 * Internal dependencies
 */
import ModalContent from './ModalContent'

const ErrorMessage = ( { value } ) => {
	return (
		<div
			className="content-ai-error"
			dangerouslySetInnerHTML={ {
				__html: value,
			} }
		></div>
	)
}

export default ( results, isPage, endpoint, typingEffect = true, isSerpPreview = false ) => {
	if ( isEmpty( results ) ) {
		return
	}

	if ( isObject( results ) && ! isArray( results ) ) {
		let content = ''
		forEach( Object.keys( results ), ( value ) => {
			let text = ''
			forEach( results[ value ], ( item ) => {
				text += item + '<br /><br />'
			} )

			content += '<div><h4>' + startCase( value ) + '</h4>' + text + '</div>'
		} )

		return (
			<div className="inner-wrapper">
				<ModalContent value={ content } isPage={ isPage } endpoint={ endpoint } typingEffect={ typingEffect } isSerpPreview={ isSerpPreview } />
			</div>
		)
	}

	if ( isArray( results ) ) {
		return (
			<div className="inner-wrapper">
				{
					'Frequently_Asked_Questions' !== endpoint &&
					map( results, ( value, key ) => {
						if ( ! isEmpty( value.warning ) ) {
							return <ErrorMessage value={ '<div class="notice notice-error">' + value.warning + '</div>' } />
						}

						return <ModalContent value={ value } key={ key } index={ key } isPage={ isPage } endpoint={ endpoint } typingEffect={ typingEffect } isSerpPreview={ isSerpPreview } />
					} )
				}

				{
					'Frequently_Asked_Questions' === endpoint && ! isArray( results[ 0 ] ) &&
					<ModalContent value={ results } isPage={ isPage } endpoint={ endpoint } typingEffect={ typingEffect } isSerpPreview={ isSerpPreview } />
				}

				{
					'Frequently_Asked_Questions' === endpoint && isArray( results[ 0 ] ) &&
					map( results, ( value ) => {
						return <ModalContent value={ value } isPage={ isPage } endpoint={ endpoint } typingEffect={ typingEffect } isSerpPreview={ isSerpPreview } />
					} )
				}
			</div>
		)
	}

	if ( ! isArray( results ) && ! isObject( results ) ) {
		return <ErrorMessage value={ results } />
	}
}
