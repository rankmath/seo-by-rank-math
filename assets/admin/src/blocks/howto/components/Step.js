/**
 * External dependencies
 */
import classnames from 'classnames'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Component } from '@wordpress/element'
import { RichText, MediaUpload } from '@wordpress/block-editor'

/**
 * Internal dependencies
 */
import { IconButton } from '@helpers/deprecated'
import MediaUploader from '@blocks/shared/MediaUploader'

class Step extends Component {
	constructor() {
		super( ...arguments )
		this.deleteStep = this.deleteStep.bind( this )
		this.toggleVisibility = this.toggleVisibility.bind( this )
	}

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
				<div className="rank-math-block-actions">
					<IconButton
						className="rank-math-item-visbility"
						icon={ visible ? 'visibility' : 'hidden' }
						onClick={ this.toggleVisibility }
						title={ __( 'Hide Step', 'rank-math' ) }
					/>

					<IconButton
						icon="trash"
						className="rank-math-item-delete"
						onClick={ this.deleteStep }
						title={ __( 'Delete Step', 'rank-math' ) }
					/>
				</div>

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
					keepPlaceholderOnFocus={ true }
					placeholder={ __( 'Enter a step title', 'rank-math' ) }
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
					keepPlaceholderOnFocus={ true }
					placeholder={ __(
						'Enter a step description',
						'rank-math'
					) }
				/>
			</div>
		)
	}

	setStepProp( prop, value ) {
		const { setAttributes, index } = this.props
		const steps = [ ...this.props.steps ]
		steps[ index ][ prop ] = value

		setAttributes( { steps } )
	}

	toggleVisibility() {
		const { setAttributes, index } = this.props
		const steps = [ ...this.props.steps ]
		steps[ index ].visible = ! this.props.visible

		setAttributes( { steps } )
	}

	deleteStep() {
		const { setAttributes, index } = this.props
		const steps = [ ...this.props.steps ]
		steps.splice( index, 1 )

		setAttributes( { steps } )
	}
}

export default Step
