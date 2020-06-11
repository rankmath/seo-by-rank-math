/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { withDispatch, withSelect } from '@wordpress/data'
import { Fragment, Component } from '@wordpress/element'
import {
	Button,
	PanelBody,
	RadioControl,
	TextControl,
} from '@wordpress/components'

/**
 * Internal dependencies
 */
import DatePicker from '@components/DatePicker'

class BookSnippet extends Component {
	render() {
		return (
			<Fragment>
				<PanelBody initialOpen={ true }>
					<TextControl
						type="number"
						label={ __( 'Rating', 'rank-math' ) }
						help={ __(
							'Rating score of the book. Optional.',
							'rank-math'
						) }
						autoComplete="off"
						step="any"
						value={ this.props.bookRating }
						onChange={ this.props.updateRating }
					/>

					<TextControl
						type="number"
						label={ __( 'Rating Minimum', 'rank-math' ) }
						help={ __(
							'Rating minimum score of the book.',
							'rank-math'
						) }
						autoComplete="off"
						value={ this.props.bookRatingMin }
						onChange={ this.props.updateRatingMin }
					/>

					<TextControl
						type="number"
						label={ __( 'Rating Maximum', 'rank-math' ) }
						help={ __(
							'Rating maximum score of the book.',
							'rank-math'
						) }
						autoComplete="off"
						value={ this.props.bookRatingMax }
						onChange={ this.props.updateRatingMax }
					/>
				</PanelBody>

				{ this.renderEditionFields( this.props.bookEditions ) }

				<PanelBody initialOpen={ true }>
					<p className="components-base-control__help">
						{ __(
							'Either a specific edition of the written work, or the volume of the work.',
							'rank-math'
						) }
					</p>
					<Button
						className="button"
						isPrimary
						onClick={ () => {
							const editions = [
								...this.props.bookEditions,
								{ book_format: 'Hardcover' },
							]
							this.props.updateEditions( editions )
							this.forceUpdate()
						} }
					>
						{ __( 'Add New Edition', 'rank-math' ) }
					</Button>
				</PanelBody>
			</Fragment>
		)
	}

	renderEditionFields( bookEditions ) {
		return bookEditions.map( ( edition, index ) => {
			return (
				<PanelBody
					title={
						edition.name
							? edition.name
							: 'Book Edition ' + ( index + 1 )
					}
					key={ index }
					initialOpen={ false }
				>
					<TextControl
						label={ __( 'Title', 'rank-math' ) }
						help={
							<Fragment>
								{ __(
									'The title of the tome. Use for the title of the tome if it differs from the book.',
									'rank-math'
								) }
								<br />
								{ __(
									'*Optional when tome has the same title as the book.',
									'rank-math'
								) }
							</Fragment>
						}
						value={ edition.name }
						autoComplete="off"
						onChange={ ( value ) => {
							this.handleChange(
								index,
								'name',
								value,
								bookEditions
							)
						} }
					/>

					<TextControl
						label={ __( 'Edition', 'rank-math' ) }
						help={ __( 'The edition of the book.', 'rank-math' ) }
						value={ edition.book_edition }
						autoComplete="off"
						onChange={ ( value ) => {
							this.handleChange(
								index,
								'book_edition',
								value,
								bookEditions
							)
						} }
					/>

					<TextControl
						label={ __( 'ISBN', 'rank-math' ) }
						help={ __(
							'The ISBN of the print book.',
							'rank-math'
						) }
						value={ edition.isbn }
						autoComplete="off"
						onChange={ ( value ) => {
							this.handleChange(
								index,
								'isbn',
								value,
								bookEditions
							)
						} }
					/>

					<TextControl
						type="url"
						label={ __( 'URL', 'rank-math' ) }
						help={ __(
							'URL specific to this edition if one exists.',
							'rank-math'
						) }
						value={ edition.url }
						autoComplete="off"
						onChange={ ( value ) => {
							this.handleChange(
								index,
								'url',
								value,
								bookEditions
							)
						} }
					/>

					<TextControl
						label={ __( 'Author(s)', 'rank-mathh' ) }
						help={ __(
							'The author(s) of the tome. Use if the author(s) of the tome differ from the related book. Provide one Person entity per author. *Optional when the tome has the same set of authors as the book.',
							'rank-math'
						) }
						value={ edition.author }
						autoComplete="off"
						onChange={ ( value ) => {
							this.handleChange(
								index,
								'author',
								value,
								bookEditions
							)
						} }
					/>

					<DatePicker
						value={ edition.date_published }
						onChange={ ( value ) => {
							this.handleChange(
								index,
								'date_published',
								value,
								bookEditions
							)
						} }
					>
						<TextControl
							label={ __( 'Date Published', 'rank-math' ) }
							help={ __(
								'Date of first publication of this tome.',
								'rank-math'
							) }
							value={ edition.date_published }
							autoComplete="off"
							onChange={ ( value ) => {
								this.handleChange(
									index,
									'date_published',
									value,
									bookEditions
								)
							} }
						/>
					</DatePicker>

					<RadioControl
						label={ __( 'Book Format', 'rank-math' ) }
						help={ __( 'The format of the book', 'rank-math' ) }
						selected={ edition.book_format }
						options={ [
							{
								value: 'EBook',
								label: __( 'EBook', 'rank-math' ),
							},
							{
								value: 'Hardcover',
								label: __( 'Hardcover', 'rank-math' ),
							},
							{
								value: 'Paperback',
								label: __( 'Paperback', 'rank-math' ),
							},
							{
								value: 'AudioBook',
								label: __( 'Audio Book', 'rank-math' ),
							},
						] }
						onChange={ ( value ) => {
							this.handleChange(
								index,
								'book_format',
								value,
								bookEditions
							)
						} }
					/>

					<Button
						isDestructive
						isLink
						onClick={ () => {
							bookEditions.splice( index, 1 )
							this.props.updateEditions( bookEditions )
							this.forceUpdate()
						} }
					>
						{ __( 'Remove', 'rank-math' ) }
					</Button>
				</PanelBody>
			)
		} )
	}

	handleChange( index, prop, value, editions ) {
		if ( 0 === editions.length ) {
			return
		}

		editions[ index ][ prop ] = value
		this.props.updateEditions( editions )
		this.forceUpdate()
	}
}

export default compose(
	withSelect( ( select ) => {
		const data = select( 'rank-math' ).getRichSnippets()

		return {
			bookRating: data.bookRating,
			bookRatingMin: data.bookRatingMin,
			bookRatingMax: data.bookRatingMax,
			bookEditions: data.bookEditions ? data.bookEditions : [],
		}
	} ),
	withDispatch( ( dispatch ) => {
		return {
			updateEditions( editions ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'bookEditions',
					'book_editions',
					editions
				)
			},

			updateRating( rating ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'bookRating',
					'book_rating',
					rating
				)
			},

			updateRatingMin( rating ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'bookRatingMin',
					'book_rating_min',
					rating
				)
			},

			updateRatingMax( rating ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'bookRatingMax',
					'book_rating_max',
					rating
				)
			},
		}
	} )
)( BookSnippet )
