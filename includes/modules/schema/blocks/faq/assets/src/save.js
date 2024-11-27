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
	const { questions, titleWrapper } = props.attributes

	if ( isEmpty( questions ) ) {
		return null
	}

	return (
		<div { ...useBlockProps.save() }>
			{ questions.map( ( question, index ) => {
				if (
					isEmpty( question.title ) ||
					isEmpty( question.content ) ||
					false === question.visible
				) {
					return null
				}

				return (
					<div className="rank-math-faq-item" key={ index }>
						<RichText.Content
							tagName={ titleWrapper }
							value={ question.title }
							className="rank-math-question"
						/>

						<RichText.Content
							tagName="div"
							value={ question.content }
							className="rank-math-answer"
						/>
					</div>
				)
			} ) }
		</div>
	)
}
