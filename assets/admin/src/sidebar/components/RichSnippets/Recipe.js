/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { dispatch, withSelect } from '@wordpress/data'
import { Fragment, Component } from '@wordpress/element'
import {
	Button,
	PanelBody,
	TextControl,
	TextareaControl,
	SelectControl,
} from '@wordpress/components'

/**
 * Internal dependencies
 */
import DatePicker from '@components/DateTimePicker'

class RecipeSnippet extends Component {
	render() {
		return (
			<Fragment>
				<PanelBody initialOpen={ true }>
					{ this.renderCommonFields() }

					{ this.renderTimeFields() }

					{ this.renderRatingFields() }

					{ this.renderVideoFields() }

					{ this.renderInstructionsFields() }
				</PanelBody>

				{ 'HowToSection' === this.props.recipeInstructionType &&
					this.renderInstructions( this.props.recipeInstructions ) }

				{ 'HowToSection' === this.props.recipeInstructionType && (
					<PanelBody initialOpen={ true }>
						<Button
							className="button"
							isPrimary
							onClick={ () => {
								const instructions = [
									...this.props.recipeInstructions,
									{ name: '', text: '' },
								]
								dispatch( 'rank-math' ).updateRichSnippet(
									'recipeInstructions',
									'recipe_instructions',
									instructions
								)
								this.forceUpdate()
							} }
						>
							{ __( 'Add New Instructions', 'rank-math' ) }
						</Button>
					</PanelBody>
				) }
			</Fragment>
		)
	}

	renderCommonFields() {
		return (
			<Fragment>
				<TextControl
					label={ __( 'Type', 'rank-math' ) }
					help={ __(
						'Type of dish, for example "appetizer", or "dessert".',
						'rank-math'
					) }
					value={ this.props.recipeType }
					onChange={ ( value ) => {
						dispatch( 'rank-math' ).updateRichSnippet(
							'recipeType',
							'recipe_type',
							value
						)
					} }
				/>

				<TextControl
					label={ __( 'Cuisine', 'rank-math' ) }
					help={ __(
						'The cuisine of the recipe (for example, French or Ethiopian).',
						'rank-math'
					) }
					value={ this.props.recipeCuisine }
					onChange={ ( value ) => {
						dispatch( 'rank-math' ).updateRichSnippet(
							'recipeCuisine',
							'recipe_cuisine',
							value
						)
					} }
				/>

				<TextControl
					label={ __( 'Keywords', 'rank-math' ) }
					help={ __(
						'Other terms for your recipe such as the season, the holiday, or other descriptors. Separate multiple entries with commas.',
						'rank-math'
					) }
					value={ this.props.recipeKeywords }
					onChange={ ( value ) => {
						dispatch( 'rank-math' ).updateRichSnippet(
							'recipeKeywords',
							'recipe_keywords',
							value
						)
					} }
				/>

				<TextControl
					label={ __( 'Recipe Yield', 'rank-math' ) }
					help={ __(
						'Quantity produced by the recipe, for example "4 servings"',
						'rank-math'
					) }
					value={ this.props.recipeYield }
					onChange={ ( value ) => {
						dispatch( 'rank-math' ).updateRichSnippet(
							'recipeYield',
							'recipe_yield',
							value
						)
					} }
				/>

				<TextControl
					label={ __( 'Calories', 'rank-math' ) }
					help={ __(
						'The number of calories in the recipe. Optional.',
						'rank-math'
					) }
					value={ this.props.recipeCalories }
					onChange={ ( value ) => {
						dispatch( 'rank-math' ).updateRichSnippet(
							'recipeCalories',
							'recipe_calories',
							value
						)
					} }
				/>
			</Fragment>
		)
	}

	renderVideoFields() {
		return (
			<Fragment>
				<TextControl
					type="url"
					label={ __( 'Recipe Video', 'rank-math' ) }
					help={ __( 'A recipe video URL. Optional.', 'rank-math' ) }
					value={ this.props.recipeVideo }
					onChange={ ( value ) => {
						dispatch( 'rank-math' ).updateRichSnippet(
							'recipeVideo',
							'recipe_video',
							value
						)
					} }
				/>

				<TextControl
					type="url"
					label={ __( 'Video Content URL', 'rank-math' ) }
					help={ __(
						'A URL pointing to the actual video media file.',
						'rank-math'
					) }
					value={ this.props.recipeVideoContentUrl }
					onChange={ ( value ) => {
						dispatch( 'rank-math' ).updateRichSnippet(
							'recipeVideoContentUrl',
							'recipe_video_content_url',
							value
						)
					} }
				/>

				<TextControl
					type="url"
					label={ __( 'Recipe Video Thumbnail', 'rank-math' ) }
					help={ __( 'A recipe video thumbnail URL.', 'rank-math' ) }
					value={ this.props.recipeVideoThumbnail }
					onChange={ ( value ) => {
						dispatch( 'rank-math' ).updateRichSnippet(
							'recipeVideoThumbnail',
							'recipe_video_thumbnail',
							value
						)
					} }
				/>

				<TextControl
					label={ __( 'Recipe Video Name', 'rank-math' ) }
					help={ __( 'A recipe video Name.', 'rank-math' ) }
					value={ this.props.recipeVideoName }
					onChange={ ( value ) => {
						dispatch( 'rank-math' ).updateRichSnippet(
							'recipeVideoName',
							'recipe_video_name',
							value
						)
					} }
				/>

				<DatePicker
					value={ this.props.recipeVideoDate }
					onChange={ ( value ) => {
						dispatch( 'rank-math' ).updateRichSnippet(
							'recipeVideoDate',
							'recipe_video_date',
							value
						)
					} }
				>
					<TextControl
						autoComplete="off"
						label={ __( 'Video Upload Date', 'rank-math' ) }
						value={ this.props.recipeVideoDate }
						onChange={ ( value ) => {
							dispatch( 'rank-math' ).updateRichSnippet(
								'recipeVideoDate',
								'recipe_video_date',
								value
							)
						} }
					/>
				</DatePicker>

				<TextareaControl
					label={ __( 'Recipe Video Description', 'rank-math' ) }
					help={ __( 'A recipe video Description.', 'rank-math' ) }
					value={ this.props.recipeVideoDescription }
					onChange={ ( value ) => {
						dispatch( 'rank-math' ).updateRichSnippet(
							'recipeVideoDescription',
							'recipe_video_description',
							value
						)
					} }
				/>
			</Fragment>
		)
	}

	renderRatingFields() {
		return (
			<Fragment>
				<TextControl
					type="number"
					label={ __( 'Rating', 'rank-math' ) }
					help={ __(
						'Rating score of the recipe. Optional.',
						'rank-math'
					) }
					value={ this.props.recipeRating }
					step="any"
					onChange={ ( value ) => {
						dispatch( 'rank-math' ).updateRichSnippet(
							'recipeRating',
							'recipe_rating',
							value
						)
					} }
				/>

				<TextControl
					type="number"
					label={ __( 'Rating Minimum', 'rank-math' ) }
					help={ __(
						'Rating minimum score of the recipe.',
						'rank-math'
					) }
					value={ this.props.recipeRatingMin }
					onChange={ ( value ) => {
						dispatch( 'rank-math' ).updateRichSnippet(
							'recipeRatingMin',
							'recipe_rating_min',
							value
						)
					} }
				/>

				<TextControl
					type="number"
					label={ __( 'Rating Maximum', 'rank-math' ) }
					help={ __(
						'Rating maximum score of the recipe.',
						'rank-math'
					) }
					value={ this.props.recipeRatingMax }
					onChange={ ( value ) => {
						dispatch( 'rank-math' ).updateRichSnippet(
							'recipeRatingMax',
							'recipe_rating_max',
							value
						)
					} }
				/>
			</Fragment>
		)
	}

	renderTimeFields() {
		return (
			<Fragment>
				<TextControl
					label={ __( 'Preparation Time', 'rank-math' ) }
					help={ __(
						'ISO 8601 duration format. Example: 1H30M',
						'rank-math'
					) }
					value={ this.props.recipePreptime }
					onChange={ ( value ) => {
						dispatch( 'rank-math' ).updateRichSnippet(
							'recipePreptime',
							'recipe_preptime',
							value
						)
					} }
				/>

				<TextControl
					label={ __( 'Cooking Time', 'rank-math' ) }
					help={ __(
						'ISO 8601 duration format. Example: 1H30M',
						'rank-math'
					) }
					value={ this.props.recipeCooktime }
					onChange={ ( value ) => {
						dispatch( 'rank-math' ).updateRichSnippet(
							'recipeCooktime',
							'recipe_cooktime',
							value
						)
					} }
				/>

				<TextControl
					label={ __( 'Total Time', 'rank-math' ) }
					help={ __(
						'ISO 8601 duration format. Example: 1H30M',
						'rank-math'
					) }
					value={ this.props.recipeTotaltime }
					onChange={ ( value ) => {
						dispatch( 'rank-math' ).updateRichSnippet(
							'recipeTotaltime',
							'recipe_totaltime',
							value
						)
					} }
				/>
			</Fragment>
		)
	}

	renderInstructions( recipeInstructions ) {
		return recipeInstructions.map( ( instruction, index ) => {
			return (
				<PanelBody
					title={
						instruction.name
							? instruction.name
							: 'Instruction ' + ( index + 1 )
					}
					key={ index }
					initialOpen={ false }
				>
					<TextControl
						label={ __( 'Name', 'rank-math' ) }
						help={ __(
							'Instruction name of the recipe.',
							'rank-math'
						) }
						value={ instruction.name }
						onChange={ ( value ) => {
							this.handleChange(
								index,
								'name',
								value,
								recipeInstructions
							)
						} }
					/>

					<TextareaControl
						label={ __( 'Text', 'rank-math' ) }
						help={ __(
							'Steps to take, add one instruction per line',
							'rank-math'
						) }
						value={ instruction.text }
						onChange={ ( value ) => {
							this.handleChange(
								index,
								'text',
								value,
								recipeInstructions
							)
						} }
					/>

					<Button
						isDestructive
						isLink
						onClick={ () => {
							recipeInstructions.splice( index, 1 )
							dispatch( 'rank-math' ).updateRichSnippet(
								'recipeInstructions',
								'recipe_instructions',
								recipeInstructions
							)
							this.forceUpdate()
						} }
					>
						{ __( 'Remove', 'rank-math' ) }
					</Button>
				</PanelBody>
			)
		} )
	}

	renderInstructionsFields() {
		return (
			<Fragment>
				<TextareaControl
					label={ __( 'Recipe Ingredients', 'rank-math' ) }
					help={ __(
						'Recipe ingredients, add one item per line',
						'rank-math'
					) }
					value={ this.props.recipeIngredients }
					onChange={ ( value ) => {
						dispatch( 'rank-math' ).updateRichSnippet(
							'recipeIngredients',
							'recipe_ingredients',
							value
						)
					} }
				/>

				<SelectControl
					label={ __( 'Instruction Type', 'rank-math' ) }
					value={ this.props.recipeInstructionType }
					options={ [
						{
							value: 'SingleField',
							label: __( 'Single Field', 'rank-math' ),
						},
						{
							value: 'HowToStep',
							label: __( 'How To Step', 'rank-math' ),
						},
						{
							value: 'HowToSection',
							label: __( 'How To Section', 'rank-math' ),
						},
					] }
					onChange={ ( value ) => {
						dispatch( 'rank-math' ).updateRichSnippet(
							'recipeInstructionType',
							'recipe_instruction_type',
							value
						)
					} }
				/>

				{ 'HowToStep' === this.props.recipeInstructionType && (
					<TextControl
						label={ __( 'Recipe Instruction Name', 'rank-math' ) }
						help={ __(
							'Instruction name of the recipe',
							'rank-math'
						) }
						value={ this.props.recipeInstructionName }
						onChange={ ( value ) => {
							dispatch( 'rank-math' ).updateRichSnippet(
								'recipeInstructionName',
								'recipe_instruction_name',
								value
							)
						} }
					/>
				) }

				{ 'HowToSection' !== this.props.recipeInstructionType && (
					<TextareaControl
						label={ __( 'Recipe Instructions', 'rank-math' ) }
						value={ this.props.recipeSingleInstructions }
						onChange={ ( value ) => {
							dispatch( 'rank-math' ).updateRichSnippet(
								'recipeSingleInstructions',
								'recipe_single_instructions',
								value
							)
						} }
					/>
				) }
			</Fragment>
		)
	}

	handleChange( index, prop, value, collection ) {
		if ( 0 === collection.length ) {
			return
		}

		collection[ index ][ prop ] = value
		dispatch( 'rank-math' ).updateRichSnippet(
			'recipeInstructions',
			'recipe_instructions',
			collection
		)
		this.forceUpdate()
	}
}

export default withSelect( ( select ) => {
	const data = select( 'rank-math' ).getRichSnippets()

	return {
		recipeCalories: data.recipeCalories,
		recipeCooktime: data.recipeCooktime,
		recipeCuisine: data.recipeCuisine,
		recipeIngredients: data.recipeIngredients,
		recipeInstructionName: data.recipeInstructionName,
		recipeInstructionType: data.recipeInstructionType,
		recipeInstructions: data.recipeInstructions
			? data.recipeInstructions
			: [],
		recipeKeywords: data.recipeKeywords,
		recipePreptime: data.recipePreptime,
		recipeRating: data.recipeRating,
		recipeRatingMax: data.recipeRatingMax,
		recipeRatingMin: data.recipeRatingMin,
		recipeSingleInstructions: data.recipeSingleInstructions,
		recipeTotaltime: data.recipeTotaltime,
		recipeType: data.recipeType,
		recipeVideo: data.recipeVideo,
		recipeVideoContentUrl: data.recipeVideoContentUrl,
		recipeVideoDate: data.recipeVideoDate,
		recipeVideoDescription: data.recipeVideoDescription,
		recipeVideoName: data.recipeVideoName,
		recipeVideoThumbnail: data.recipeVideoThumbnail,
		recipeYield: data.recipeYield,
	}
} )( RecipeSnippet )
