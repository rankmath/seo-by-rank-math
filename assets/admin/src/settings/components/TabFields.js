/**
 * External dependencies
 */
import { map } from 'lodash'
import classNames from 'classnames'

/**
 * Internal dependenies
 */
import Field from './Field'
import canAddField from '../helpers/canAddField'
import canDisableField from '../helpers/canDisableField'

/**
 * Render tab fields.
 *
 * @param {Object} props             Component props.
 * @param {string} props.settingType The type of setting to check within the app data.
 * @param {Array}  props.fields      Array of tab fields.
 * @param {Object} props.settings    Settings data.
 */
export default ( { settingType, fields, settings = null } ) => {
	return (
		<div className="field-wrap form-table wp-core-ui rank-math-ui">
			<div
				id={ `field-metabox-rank-math-${ settingType }` }
				className="field-metabox field-list"
			>
				{ map( fields, ( field, index ) => {
					const { id, type, name, desc, classes, content, dep, disableDep, afterfield } = field

					// eslint-disable-next-line @wordpress/no-unused-vars-before-return
					const isDisabled = canDisableField( disableDep, settings )

					if ( dep && ! canAddField( dep, settings ) ) {
						return
					}

					if ( type === 'hidden' ) {
						return
					}

					if ( type === 'raw' ) {
						return content
					}

					const containerClasses = classNames(
						'field-row',
						classes,
						{
							'field-disabled': isDisabled,
							[ 'field-id-' + id ]: id,
							[ 'field-type-' + type ]: type,
						}
					)

					return (
						<div key={ id || index } className={ containerClasses }>
							{ name && (
								<div className="field-th">
									<label htmlFor={ id }>{ name }</label>
								</div>
							) }

							<div className="field-td">
								<Field settingType={ settingType } field={ { ...field, isDisabled } } settings={ settings } />

								{ desc && (
									<p
										className="field-description"
										dangerouslySetInnerHTML={ { __html: desc } }
									/>
								) }

								{ afterfield }
							</div>
						</div>
					)
				} ) }
			</div>
		</div>
	)
}
