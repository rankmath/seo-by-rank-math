/**
 * External dependencies
 */
import { isEmpty } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Fragment } from '@wordpress/element'
import { Button, Dashicon } from '@wordpress/components'
import { BlockControls, AlignmentToolbar, useBlockProps } from '@wordpress/block-editor'

/**
 * Internal dependencies
 */
import getLink from '@helpers/getLink'
import Inspector from './components/Inspector'
import Question from './components/Question'
import generateId from '@helpers/generateId'

/**
 * Render Quetion component.
 *
 * @param {Object} props Block attributes
 *
 * @return {Array} Array of question editor.
 */
const renderQuestions = ( props ) => {
	const {
		sizeSlug,
		titleWrapper,
		titleCssClasses,
		contentCssClasses,
	} = props.attributes
	let { questions } = props.attributes
	if ( isEmpty( questions ) ) {
		questions = [
			{
				id: generateId( 'faq-question' ),
				title: '',
				content: '',
				visible: true,
			},
		]
		props.setAttributes( { questions } )
	}

	return questions.map( ( question, index ) => {
		return (
			<li key={ question.id }>
				<Question
					{ ...question }
					index={ index }
					key={ question.id + '-question' }
					questions={ questions }
					setAttributes={ props.setAttributes }
					sizeSlug={ sizeSlug }
					titleWrapper={ titleWrapper }
					titleCssClasses={ titleCssClasses }
					contentCssClasses={ contentCssClasses }
				/>
			</li>
		)
	} )
}

/**
 * Add an empty Question into block.
 *
 * @param {Object} props Block props.
 */
const addNew = ( props ) => {
	const questions = [ ...props.attributes.questions ]
	questions.push( {
		id: generateId( 'faq-question' ),
		title: '',
		content: '',
		visible: true,
	} )

	props.setAttributes( { questions } )
}

/**
 * FAQ block edit component.
 *
 * @param {Object} props Block props.
 */
export default ( props ) => {
	const { className, isSelected } = props
	const { textAlign } = props.attributes
	const blockProps = useBlockProps()

	return (
		<div { ...blockProps }>
			<div
				id="rank-math-faq"
				className={ 'rank-math-block ' + className }
			>
				{ isSelected && <Inspector { ...props } /> }
				{ isSelected && (
					<Fragment>
						<BlockControls>
							<AlignmentToolbar
								value={ textAlign }
								onChange={ ( nextTextAlignment ) =>
									props.setAttributes( {
										textAlign: nextTextAlignment,
									} )
								}
							/>
						</BlockControls>
					</Fragment>
				) }

				<ul style={ { textAlign } }>{ renderQuestions( props ) }</ul>

				<Button
					variant="primary"
					onClick={ () => {
						addNew( props )
					} }
				>
					{ __( 'Add New FAQ', 'rank-math' ) }
				</Button>

				<a
					href={ getLink( 'faq-schema-block', 'Add New FAQ' ) }
					rel="noopener noreferrer"
					target="_blank"
					title={ __( 'More Info', 'rank-math' ) }
					className={ 'rank-math-block-info' }
				>
					<Dashicon icon="info" />
				</a>
			</div>
		</div>
	)
}

