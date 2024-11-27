/**
 * External dependencies
 */
import { isEmpty } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Fragment } from '@wordpress/element'
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
	useBlockProps,
} from '@wordpress/block-editor'

/**
 * Internal dependencies
 */
import Step from './components/Step'
import Inspector from './components/inspector'
import generateId from '@helpers/generateId'
import MediaUploader from '@blocks/shared/MediaUploader'

/**
 * Add an empty Step into block.
 *
 * @param {Object} props Block props.
 */
const addNew = ( props ) => {
	const { steps } = props.attributes
	const newSteps = isEmpty( steps ) ? [] : [ ...steps ]
	newSteps.push( {
		id: generateId( 'howto-step' ),
		title: '',
		content: '',
		visible: true,
	} )

	props.setAttributes( { steps: newSteps } )
}

/**
 * Toggle duration form visibility.
 *
 * @param {Object} props Block props
 */
const toggleDuration = ( props ) => {
	props.setAttributes( {
		hasDuration: ! props.attributes.hasDuration,
	} )
}

/**
 * When an image selected.
 *
 * @param {Object} image Seelected image object.
 * @param {Object} props Block props.
 */
const onSelectImage = ( image, props ) => {
	const { setAttributes } = props

	setAttributes( { imageID: image.id } )
}

/**
 * Remove image from step.
 *
 * @param {Object} props Block props.
 */
const removeImage = ( props ) => {
	const { setAttributes } = props

	setAttributes( { imageID: 0 } )
}

/**
 * Render Steps component.
 *
 * @param {Object} props Block props.
 * @return {Array} Array of step editor.
 */
const renderSteps = ( props ) => {
	const {
		steps,
		sizeSlug,
		titleWrapper,
		titleCssClasses,
		contentCssClasses,
	} = props.attributes
	if ( isEmpty( steps ) ) {
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
 * HowTo block edit component.
 *
 * @param {Object} props Block props.
 */
export default ( props ) => {
	const { className, isSelected, attributes, setAttributes } = props
	const { imageID, mainSizeSlug, textAlign } = attributes
	const blockProps = useBlockProps()

	return (
		<div { ...blockProps }>
			<div
				id="rank-math-howto"
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
								removeImage={ () => {
									removeImage( props )
								} }
							/>
						</div>
					) }
					onSelect={ ( image ) => {
						onSelectImage( image, props )
					} }
				/>

				<RichText
					style={ { textAlign } }
					tagName="div"
					className="rank-math-howto-description"
					value={ attributes.description }
					onChange={ ( description ) => {
						setAttributes( { description } )
					} }
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
							onChange={ () => {
								toggleDuration( props )
							} }
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

				{ applyFilters( 'rank_math_block_howto_data', '', props ) }

				<ul style={ { textAlign } }>{ renderSteps( props ) }</ul>

				<Button
					variant="primary"
					onClick={ () => {
						addNew( props )
					} }
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
		</div>
	)
}
