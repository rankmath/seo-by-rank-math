/**
 * External dependencies
 */
import classnames from 'classnames'

/**
 * WordPress dependencies
 */
import { Fragment } from '@wordpress/element'
import { Dashicon } from '@wordpress/components'

/**
 * Internal dependencies
 */
import { swapVariables } from '@helpers/swapVariables'

const SocialPreview = ( props ) => {
	const classes = classnames(
		'rank-math-social-preview',
		'rank-math-social-preview-' + props.network,
		props.cardType
	)

	return (
		<div className={ classes }>
			<div className="rank-math-social-preview-item">
				<div className="rank-math-social-preview-meta">
					<div className="social-profile-image"></div>
					{ props.children }
				</div>

				<div className="rank-math-social-preview-item-wrapper">
					<div className="rank-math-social-preview-image">
						<img
							className="rank-math-social-image-thumbnail"
							src={ props.image }
							alt=""
						/>
						{ props.hasOverlay && props.imageOverlay && (
							<img
								src={
									rankMath.overlayImages[ props.imageOverlay ]
										.url
								}
								className="rank-math-social-preview-image-overlay"
								alt=""
							/>
						) }
					</div>

					<div className="rank-math-social-preview-caption">
						{ 'facebook' === props.network && (
							<h4 className="rank-math-social-preview-publisher">
								{ props.siteurl }
							</h4>
						) }
						<h3 className="rank-math-social-preview-title">
							{ props.title
								? swapVariables.swap( props.title )
								: props.serpTitle }
						</h3>
						<p className="rank-math-social-preview-description">
							{ props.description
								? swapVariables.swap( props.description )
								: props.serpDescription }
						</p>
						{ 'twitter' === props.network && (
							<Fragment>
								<h4 className="rank-math-social-preview-publisher">
									<Dashicon icon="admin-links" />
									{ props.siteurl }
								</h4>
							</Fragment>
						) }
					</div>
				</div>
			</div>
		</div>
	)
}

export default SocialPreview
