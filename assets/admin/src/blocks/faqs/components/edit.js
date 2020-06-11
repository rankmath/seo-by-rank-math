/**
 * External dependencies
 */
import { isEmpty } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Fragment, Component } from '@wordpress/element'
import { Button, Dashicon } from '@wordpress/components'
import { BlockControls, AlignmentToolbar } from '@wordpress/block-editor'

/**
 * Internal dependencies
 */
import Inspector from './Inspector'
import Question from './Question'
import generateId from '@helpers/generateId'

class Edit extends Component {
	constructor() {
		super( ...arguments )
		this.addNew = this.addNew.bind( this )
	}

	render() {
		const { className, isSelected } = this.props
		const { textAlign } = this.props.attributes

		return (
			<div
				id="rank-math-faq"
				className={ 'rank-math-block ' + className }
			>
				{ isSelected && <Inspector { ...this.props } /> }
				{ isSelected && (
					<Fragment>
						<BlockControls>
							<AlignmentToolbar
								value={ textAlign }
								onChange={ ( nextTextAlignment ) =>
									this.props.setAttributes( {
										textAlign: nextTextAlignment,
									} )
								}
							/>
						</BlockControls>
					</Fragment>
				) }

				<ul style={ { textAlign } }>{ this.renderQuestions() }</ul>

				<Button
					isPrimary={ true }
					isLarge={ true }
					onClick={ this.addNew }
				>
					{ __( 'Add New FAQ', 'rank-math' ) }
				</Button>

				<a
					href="https://rankmath.com/kb/faq-schema-block/"
					rel="noopener noreferrer"
					target="_blank"
					title={ __( 'More Info', 'rank-math' ) }
					className={ 'rank-math-block-info' }
				>
					<Dashicon icon="info" />
				</a>
			</div>
		)
	}

	renderQuestions() {
		const {
			sizeSlug,
			titleWrapper,
			titleCssClasses,
			contentCssClasses,
		} = this.props.attributes
		let { questions } = this.props.attributes
		if ( isEmpty( questions ) ) {
			questions = [
				{
					id: generateId( 'faq-question' ),
					title: '',
					content: '',
					visible: true,
				},
			]
		}

		return questions.map( ( question, index ) => {
			return (
				<li key={ question.id }>
					<Question
						{ ...question }
						index={ index }
						key={ question.id + '-question' }
						questions={ questions }
						setAttributes={ this.props.setAttributes }
						sizeSlug={ sizeSlug }
						titleWrapper={ titleWrapper }
						titleCssClasses={ titleCssClasses }
						contentCssClasses={ contentCssClasses }
					/>
				</li>
			)
		} )
	}

	addNew() {
		const questions = [ ...this.props.attributes.questions ]
		questions.push( {
			id: generateId( 'faq-question' ),
			title: '',
			content: '',
			visible: true,
		} )

		this.props.setAttributes( { questions } )
	}
}

export default Edit
