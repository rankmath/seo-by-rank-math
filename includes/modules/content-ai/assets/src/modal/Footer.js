/**
 * External dependencies
 */
import jQuery from 'jquery'
import { isEmpty } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import {
	Button,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalNumberControl as NumberControl,
} from '@wordpress/components'

/**
 * Internal dependencies
 */
import getData from '../helpers/getData'
import markdownConverter from '../helpers/markdownConverter'
import createPost from '../helpers/createPost'

// Function to get the Generate button label.
const getGenerateButtonLabel = ( generating, results, originalEndpoint ) => {
	if ( generating ) {
		return __( 'Generating…', 'rank-math' )
	}

	if ( ! isEmpty( results ) && 'Blog_Post_Wizard' === originalEndpoint ) {
		return __( 'Regenerate', 'rank-math' )
	}

	if ( ! isEmpty( results ) ) {
		return __( 'Generate More', 'rank-math' )
	}

	return __( 'Generate', 'rank-math' )
}

// Function to get the Next button label used in the Wizard.
const getNextButtonLabel = ( endpoint, results ) => {
	if ( 'Blog_Post_Idea' === endpoint && isEmpty( results ) ) {
		return __( 'Skip', 'rank-math' )
	}

	return endpoint === 'Blog_Post_Outline' ? __( 'Write Post', 'rank-math' ) : __( 'Next Step', 'rank-math' )
}

export default ( { endpoint, originalEndpoint, apiContent, output, attributes, generating, results, steps, setSteps, isDisabled, onChange, setGenerating, setDisabled, setHistory, setData, setResults } ) => {
	const isWizard = 'Blog_Post_Wizard' === originalEndpoint
	return (
		<div className="footer">
			{
				! isWizard &&
				<>
					<NumberControl
						min="1"
						max={ output.max }
						value={ attributes.choices ?? output.default }
						onChange={ ( newChoice ) => ( onChange( 'choices', newChoice ) ) }
					/>
					<span className="output-label">{ __( 'Outputs', 'rank-math' ) }</span>
				</>
			}
			<Button
				variant={ ! isWizard ? 'primary' : ( ! isEmpty( results ) ? 'secondary' : 'primary' ) }
				className="button"
				disabled={ isDisabled }
				onClick={ () => {
					const form = jQuery( 'form.rank-math-ai-tools' ).get( 0 )
					if ( ! form.checkValidity() ) {
						form.reportValidity()
						return
					}
					setGenerating( true )
					setDisabled( true )
					setHistory( false )
					if ( isWizard && steps.endpoint === 'Long_Form_Content' ) {
						getData( 'Long_Form_Content', steps.attributes, apiContent, false )
						return
					}

					getData( endpoint, attributes, apiContent, false )
				} }
			>
				<span className="text">
					{
						getGenerateButtonLabel( generating, results, originalEndpoint )
					}
				</span>
			</Button>
			{
				isWizard &&
				steps.endpoint !== 'Long_Form_Content' &&
				<Button
					variant={ ! isEmpty( results ) ? 'primary' : 'secondary' }
					className="button"
					disabled={ isDisabled || ( isEmpty( results ) && steps.endpoint !== 'Blog_Post_Idea' ) }
					onClick={ () => {
						if ( endpoint === 'Blog_Post_Outline' ) {
							const topic = attributes.topic
							attributes = {
								outline: markdownConverter( results[ 0 ], true ),
								language: ! isEmpty( attributes.language ) ? attributes.language : '',
								tone: ! isEmpty( attributes.tone ) ? attributes.tone : '',
								audience: ! isEmpty( attributes.audience ) ? attributes.audience : '',
								style: ! isEmpty( attributes.style ) ? attributes.style : '',
								choices: 1,
							}
							setData( '' )
							setResults( [] )
							setGenerating( true )

							getData( 'Long_Form_Content', attributes, apiContent, false )
							setSteps( { endpoint: 'Long_Form_Content', content: '', topic, attributes } )
							return
						}

						const content = ! isEmpty( results ) ? results[ 0 ] : ''
						setSteps( { endpoint: 'Blog_Post_Outline', content } )
						setData( '' )
						setResults( [] )
					} }
				>
					<span className="text">
						{ getNextButtonLabel( endpoint, results ) }
					</span>
				</Button>
			}
			{
				isWizard &&
				steps.endpoint === 'Long_Form_Content' &&
				! isEmpty( results ) &&
				rankMath.contentAI.isContentAIPage &&
				<Button
					variant={ ! isEmpty( results ) ? 'primary' : 'secondary' }
					className="button"
					onClick={ () => ( createPost( markdownConverter( results[ 0 ] ), steps.topic ) ) }
				>
					<span className="text">
						{
							rankMath.contentAI.isContentAIPage ? __( 'Create New Post', 'rank-math' ) : __( 'Insert', 'rank-math' )
						}
					</span>
				</Button>
			}
		</div>
	)
}
