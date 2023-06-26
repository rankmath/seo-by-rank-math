/**
 * External dependencies
 */
import classnames from 'classnames'

/**
 * Internal dependencies
 */
import MediaUploader from '@blocks/shared/MediaUploader'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { applyFilters } from '@wordpress/hooks'
import { Button } from '@wordpress/components'
import { Component } from '@wordpress/element'
import { RichText, MediaUpload } from '@wordpress/block-editor'

/**
 * A Question and answer pair within FAQ block.
 */
class Question extends Component {
	/**
	 * Renders the component.
	 *
	 * @return {Component} Question editor.
	 */
	render() {
		const {
			title,
			content,
			visible,
			imageID,
			sizeSlug,
			titleWrapper,
			titleCssClasses,
			contentCssClasses,
		} = this.props
		const wrapperClasses = classnames( 'rank-math-question-wrapper', {
			'question-not-visible': ! visible,
		} )

		return (
			<div className={ wrapperClasses }>
				<div className="rank-math-item-header">
					<RichText
						tagName={ titleWrapper }
						className={
							'rank-math-faq-question rank-math-block-title' +
							titleCssClasses
						}
						value={ title }
						onChange={ ( newTitle ) => {
							this.setQuestionProp( 'title', newTitle )
						} }
						placeholder={ __( 'Question…', 'rank-math' ) }
					/>

					<div className="rank-math-block-actions">
						{ applyFilters( 'rank_math_block_faq_actions', '', this.props, this ) }

						<Button
							className="rank-math-item-visbility"
							icon={ visible ? 'visibility' : 'hidden' }
							onClick={ this.toggleVisibility }
							label={ __( 'Hide Question', 'rank-math' ) }
							showTooltip={ true }
						/>

						<Button
							icon="trash"
							className="rank-math-item-delete"
							onClick={ this.deleteQuestion }
							label={ __( 'Delete Question', 'rank-math' ) }
							showTooltip={ true }
						/>
					</div>
				</div>

				<div className="rank-math-item-content">
					<RichText
						tagName="div"
						className={ 'rank-math-faq-answer ' + contentCssClasses }
						value={ content }
						onChange={ ( newContent ) => {
							this.setQuestionProp( 'content', newContent )
						} }
						placeholder={ __(
							'Enter the answer to the question',
							'rank-math'
						) }
					/>

					<MediaUpload
						allowedTypes={ [ 'image' ] }
						multiple={ false }
						value={ imageID }
						render={ ( { open } ) => (
							<MediaUploader
								imageID={ imageID }
								sizeSlug={ sizeSlug }
								open={ open }
								removeImage={ () => {
									this.setQuestionProp( 'imageID', 0 )
								} }
							/>
						) }
						onSelect={ ( image ) => {
							this.setQuestionProp( 'imageID', image.id )
						} }
					/>
				</div>
			</div>
		)
	}

	/**
	 * Update question properties.
	 *
	 * @param {string} prop  Poperty name.
	 * @param {string} value Property value.
	 */
	setQuestionProp( prop, value ) {
		const { setAttributes, index } = this.props
		const questions = [ ...this.props.questions ]
		questions[ index ][ prop ] = value

		setAttributes( { questions } )
	}

	/**
	 * Toggle question visibility.
	 */
	toggleVisibility = () => {
		const { setAttributes, index } = this.props
		const questions = [ ...this.props.questions ]
		questions[ index ].visible = ! this.props.visible

		setAttributes( { questions } )
	}

	/**
	 * Delete question from block.
	 */
	deleteQuestion = () => {
		const { setAttributes, index } = this.props
		const questions = [ ...this.props.questions ]
		questions.splice( index, 1 )

		setAttributes( { questions } )
	}
}

export default Question
