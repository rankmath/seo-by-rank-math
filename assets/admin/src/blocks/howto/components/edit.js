/**
 * External dependencies
 */
import { isEmpty } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Fragment, Component } from '@wordpress/element'
import { applyFilters } from '@wordpress/hooks'
import {
	Button,
	Dashicon,
	TextControl,
	ToggleControl,
} from '@wordpress/components'
import {
	BlockControls,
	AlignmentToolbar,
	RichText,
	MediaUpload,
} from '@wordpress/block-editor'

/**
 * Internal dependencies
 */
import Step from './Step'
import Inspector from './inspector'
import generateId from '@helpers/generateId'
import MediaUploader from '@blocks/shared/MediaUploader'

/**
 * HowTo block edit component.
 */
class Edit extends Component {
	/**
	 * Renders the component.
	 *
	 * @return {Component} HowTo block editor.
	 */
	render() {
		const { className, isSelected, attributes, setAttributes } = this.props
		const { imageID, mainSizeSlug, textAlign } = attributes

		return (
			<div
				id="rank-math-howto"
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

				<MediaUpload
					allowedTypes={ [ 'image' ] }
					multiple={ false }
					value={ imageID }
					render={ ( { open } ) => (
						<div className="rank-math-howto-final-image">
							<MediaUploader
								imageID={ imageID }
								sizeSlug={ mainSizeSlug }
								open={ open }
								addButtonLabel={ __(
									'Add Final Image',
									'rank-math'
								) }
								removeImage={ this.removeImage }
							/>
						</div>
					) }
					onSelect={ this.onSelectImage }
				/>

				<RichText
					style={ { textAlign } }
					tagName="div"
					className="rank-math-howto-description"
					value={ attributes.description }
					onChange={ ( description ) => {
						setAttributes( { description } )
					} }
					keepPlaceholderOnFocus={ true }
					placeholder={ __(
						'Enter a main description',
						'rank-math'
					) }
				/>

				<div className={ 'rank-math-howto-duration' }>
					<div
						className={
							'components-base-control rank-math-howto-duration-label'
						}
					>
						<span>{ __( 'Duration', 'rank-math' ) }</span>

						<ToggleControl
							checked={ attributes.hasDuration }
							onChange={ this.toggleDuration }
						/>
					</div>

					<div
						className={
							'rank-math-howto-duration-fields' +
							( attributes.hasDuration ? '' : ' hidden' )
						}
					>
						<TextControl
							value={ attributes.timeLabel }
							placeholder={ __( 'Total time:', 'rank-math' ) }
							onChange={ ( timeLabel ) => {
								setAttributes( { timeLabel } )
							} }
						/>

						<TextControl
							type="number"
							value={ attributes.days }
							placeholder={ __( 'DD', 'rank-math' ) }
							onChange={ ( days ) => {
								setAttributes( { days } )
							} }
						/>

						<TextControl
							type="number"
							value={ attributes.hours }
							placeholder={ __( 'HH', 'rank-math' ) }
							onChange={ ( hours ) => {
								setAttributes( { hours } )
							} }
						/>

						<TextControl
							type="number"
							value={ attributes.minutes }
							placeholder={ __( 'MM', 'rank-math' ) }
							onChange={ ( minutes ) => {
								setAttributes( { minutes } )
							} }
						/>
					</div>

					<div
						className={
							'rank-math-howto-duration-instructions' +
							( attributes.hasDuration ? '' : ' hidden' )
						}
					>
						{ __(
							'Optional, use first field to describe the duration.',
							'rank-math'
						) }
					</div>
				</div>

				{ applyFilters( 'rank_math_block_howto_data', '', this.props ) }

				<ul style={ { textAlign } }>{ this.renderSteps() }</ul>

				<Button
					isPrimary={ true }
					isLarge={ true }
					onClick={ this.addNew }
				>
					{ __( 'Add New Step', 'rank-math' ) }
				</Button>

				<a
					href="http://rankmath.com/blog/howto-schema/"
					title={ __( 'More Info', 'rank-math' ) }
					target="_blank"
					rel="noopener noreferrer"
					className={ 'rank-math-block-info' }
				>
					<Dashicon icon="info" />
				</a>
			</div>
		)
	}

	/**
	 * Render Steps component.
	 *
	 * @return {Array} Array of step editor.
	 */
	renderSteps() {
		const {
			steps,
			sizeSlug,
			titleWrapper,
			titleCssClasses,
			contentCssClasses,
		} = this.props.attributes
		if ( isEmpty( steps ) ) {
			this.addNew()
			return null
		}

		return steps.map( ( step, index ) => {
			return (
				<li key={ step.id }>
					<Step
						{ ...step }
						index={ index }
						key={ step.id + '-step' }
						steps={ steps }
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

	/**
	 * Add an empty Step into block.
	 */
	addNew = () => {
		const { steps } = this.props.attributes
		const newSteps = isEmpty( steps ) ? [] : [ ...steps ]
		newSteps.push( {
			id: generateId( 'howto-step' ),
			title: '',
			content: '',
			visible: true,
		} )

		this.props.setAttributes( { steps: newSteps } )
	}

	/**
	 * Toggle duration form visibility.
	 */
	toggleDuration = () => {
		this.props.setAttributes( {
			hasDuration: ! this.props.attributes.hasDuration,
		} )
	}

	/**
	 * When an image selected.
	 *
	 * @param {Object} image Seelected image object.
	 */
	onSelectImage = ( image ) => {
		const { setAttributes } = this.props

		setAttributes( { imageID: image.id } )
	}

	/**
	 * Remove image from step.
	 */
	removeImage = () => {
		const { setAttributes } = this.props

		setAttributes( { imageID: 0 } )
	}
}

export default Edit
