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
				<div className="rank-math-block-actions">
					<Button
						className="rank-math-item-visbility"
						icon={ visible ? 'visibility' : 'hidden' }
						onClick={ this.toggleVisibility }
						title={ __( 'Hide Question', 'rank-math' ) }
					/>

					<Button
						icon="trash"
						className="rank-math-item-delete"
						onClick={ this.deleteQuestion }
						title={ __( 'Delete Question', 'rank-math' ) }
					/>
				</div>

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
					keepPlaceholderOnFocus={ true }
					placeholder={ __( 'Question…', 'rank-math' ) }
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

				<RichText
					tagName="div"
					className={ 'rank-math-faq-answer ' + contentCssClasses }
					value={ content }
					onChange={ ( newContent ) => {
						this.setQuestionProp( 'content', newContent )
					} }
					keepPlaceholderOnFocus={ true }
					placeholder={ __(
						'Enter the answer to the question',
						'rank-math'
					) }
				/>
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
