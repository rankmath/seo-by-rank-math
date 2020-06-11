/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Fragment } from '@wordpress/element'
import { compose } from '@wordpress/compose'
import { withSelect, withDispatch } from '@wordpress/data'
import { BaseControl, CheckboxControl } from '@wordpress/components'

/**
 * Internal dependencies
 */
import Tooltip from '@components/Tooltip'

const updateRobotsValue = function( robots, key, value ) {
	if ( false === value ) {
		delete robots[ key ]
	} else {
		robots[ key ] = true
	}

	return robots
}

const Robots = ( props ) => (
	<BaseControl
		className="rank-math-robots"
		id="rank-math-robots"
		label={ __( 'Robots Meta', 'rank-math' ) }
	>
		<div className="rank-math-robots-list">
			<CheckboxControl
				className="robot-choice-index"
				label={
					<Fragment>
						{ __( 'Index', 'rank-math' ) }
						<Tooltip>
							{ __(
								'Instructs search engines to index and show these pages in the search results',
								'rank-math'
							) }
						</Tooltip>
					</Fragment>
				}
				checked={ props.isRobotIndex }
				onChange={ ( value ) => props.updateRobots( 'index', value ) }
			/>

			<CheckboxControl
				className="robot-choice-noindex"
				label={
					<Fragment>
						{ __( 'No Index', 'rank-math' ) }
						<Tooltip>
							{ __(
								'Prevents pages from being indexed and displayed in search engine result pages',
								'rank-math'
							) }
						</Tooltip>
					</Fragment>
				}
				checked={ props.isRobotNoIndex }
				onChange={ ( value ) => props.updateRobots( 'noindex', value ) }
			/>

			<CheckboxControl
				className="robot-choice-nofollow"
				label={
					<Fragment>
						{ __( 'Nofollow', 'rank-math' ) }
						<Tooltip>
							{ __(
								'Prevents search engines from following links on the pages',
								'rank-math'
							) }
						</Tooltip>
					</Fragment>
				}
				checked={ props.isRobotNoFollow }
				onChange={ ( value ) =>
					props.updateRobots( 'nofollow', value )
				}
			/>

			<CheckboxControl
				className="robot-choice-noarchive"
				label={
					<Fragment>
						{ __( 'No Archive', 'rank-math' ) }
						<Tooltip>
							{ __(
								'Prevents search engines from showing Cached links for pages',
								'rank-math'
							) }
						</Tooltip>
					</Fragment>
				}
				checked={ props.isRobotNoArchive }
				onChange={ ( value ) =>
					props.updateRobots( 'noarchive', value )
				}
			/>

			<CheckboxControl
				className="robot-choice-noimageindex"
				label={
					<Fragment>
						{ __( 'No Image Index', 'rank-math' ) }
						<Tooltip>
							{ __(
								'Lets you specify that you do not want your pages to appear as the referring page for images that appear in image search results',
								'rank-math'
							) }
						</Tooltip>
					</Fragment>
				}
				checked={ props.isRobotNoImageIndex }
				onChange={ ( value ) =>
					props.updateRobots( 'noimageindex', value )
				}
			/>

			<CheckboxControl
				className="robot-choice-nosnippet"
				label={
					<Fragment>
						{ __( 'No Snippet', 'rank-math' ) }
						<Tooltip>
							{ __(
								'Prevents a snippet from being shown in the search results',
								'rank-math'
							) }
						</Tooltip>
					</Fragment>
				}
				checked={ props.isRobotNoSnippet }
				onChange={ ( value ) =>
					props.updateRobots( 'nosnippet', value )
				}
			/>
		</div>
	</BaseControl>
)

export default compose(
	withSelect( ( select ) => {
		const robots = select( 'rank-math' ).getRobots()

		return {
			robots,
			isRobotIndex: 'index' in robots,
			isRobotNoIndex: 'noindex' in robots,
			isRobotNoFollow: 'nofollow' in robots,
			isRobotNoArchive: 'noarchive' in robots,
			isRobotNoImageIndex: 'noimageindex' in robots,
			isRobotNoSnippet: 'nosnippet' in robots,
		}
	} ),
	withDispatch( ( dispatch, props ) => {
		let { robots } = props
		return {
			updateRobots( key, value ) {
				robots = updateRobotsValue( robots, key, value )

				if ( 'index' === key ) {
					robots = updateRobotsValue( robots, 'noindex', ! value )
				}

				if ( 'noindex' === key ) {
					robots = updateRobotsValue( robots, 'index', ! value )
				}

				dispatch( 'rank-math' ).updateRobots( robots )
			},
		}
	} )
)( Robots )
