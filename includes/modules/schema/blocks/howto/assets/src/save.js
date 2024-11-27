/**
 * External dependencies
 */
import { isEmpty } from 'lodash'

/**
 * WordPress dependencies
 */
import { RichText, useBlockProps } from '@wordpress/block-editor'

/**
 * Save block for display on front
 *
 * @param {Object} props This component's props.
 */
export default ( props ) => {
	const { steps, titleWrapper } = props.attributes

	if ( isEmpty( steps ) ) {
		return null
	}

	return (
		<div { ...useBlockProps.save() }>
			{ steps.map( ( step, index ) => {
				if ( false === step.visible ) {
					return null
				}

				return (
					<div className="rank-math-howto-step" key={ index }>
						{ step.title && (
							<RichText.Content
								tagName={ titleWrapper }
								value={ step.title }
								className="rank-math-howto-title"
							/>
						) }

						{ step.content && (
							<RichText.Content
								tagName="div"
								value={ step.content }
								className="rank-math-howto-content"
							/>
						) }
					</div>
				)
			} ) }
		</div>
	)
}
