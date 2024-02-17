/**
 * External dependencies
 */
import classnames from 'classnames'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Component } from '@wordpress/element'
import { Button } from '@wordpress/components'
import { RichText, MediaUpload } from '@wordpress/block-editor'

/**
 * Internal dependencies
 */
import MediaUploader from '@blocks/shared/MediaUploader'
import { applyFilters } from "@wordpress/hooks";

/**
 * A Step within HowTo block.
 */
class Step extends Component {
	/**
	 * Renders the component.
	 *
	 * @return {Component} Step editor.
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
		const wrapperClasses = classnames( 'rank-math-step-wrapper', {
			'step-not-visible': ! visible,
		} )

		return (
			<div className={ wrapperClasses }>
				<div className="rank-math-item-header">
					<RichText
						tagName={ titleWrapper }
						className={
							'rank-math-howto-step-title rank-math-block-title' +
							titleCssClasses
						}
						value={ title }
						onChange={ ( newTitle ) => {
							this.setStepProp( 'title', newTitle )
						} }
						placeholder={ __( 'Enter a step title', 'rank-math' ) }
					/>

					<div className="rank-math-block-actions">
						{ applyFilters( 'rank_math_block_howto_actions', '', this.props ) }

						<Button
							className="rank-math-item-visbility"
							icon={ visible ? 'visibility' : 'hidden' }
							onClick={ this.toggleVisibility }
							title={ __( 'Hide Step', 'rank-math' ) }
						/>

						<Button
							icon="trash"
							className="rank-math-item-delete"
							onClick={ this.deleteStep }
							title={ __( 'Delete Step', 'rank-math' ) }
						/>
					</div>
				</div>

				<MediaUpload
					allowedTypes={ [ 'image' ] }
					multiple={ false }
					value={ imageID }
					render={ ( { open } ) => (
						<MediaUploader
							imageID={ imageID }
							sizeSlug={ sizeSlug }
							open={ open }
							addButtonLabel={ __(
								'Add Step Image',
								'rank-math'
							) }
							removeImage={ () => {
								this.setStepProp( 'imageID', 0 )
							} }
						/>
					) }
					onSelect={ ( image ) => {
						this.setStepProp( 'imageID', image.id )
					} }
				/>

				<RichText
					tagName="div"
					className={
						'rank-math-howto-step-content' + contentCssClasses
					}
					value={ content }
					onChange={ ( newContent ) => {
						this.setStepProp( 'content', newContent )
					} }
					placeholder={ __(
						'Enter a step description',
						'rank-math'
					) }
				/>
			</div>
		)
	}

	/**
	 * Update step properties.
	 *
	 * @param {string} prop  Poperty name.
	 * @param {string} value Property value.
	 */
	setStepProp( prop, value ) {
		const { setAttributes, index } = this.props
		const steps = [ ...this.props.steps ]
		steps[ index ][ prop ] = value

		setAttributes( { steps } )
	}

	/**
	 * Toggle step visibility.
	 */
	toggleVisibility = () => {
		const { setAttributes, index } = this.props
		const steps = [ ...this.props.steps ]
		steps[ index ].visible = ! this.props.visible

		setAttributes( { steps } )
	}

	/**
	 * Delete step from block.
	 */
	deleteStep = () => {
		const { setAttributes, index } = this.props
		const steps = [ ...this.props.steps ]
		steps.splice( index, 1 )

		setAttributes( { steps } )
	}
}

export default Step
