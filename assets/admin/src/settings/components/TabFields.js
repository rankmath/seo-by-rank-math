/**
 * External dependencies
 */
import { map } from 'lodash'
import classNames from 'classnames'

/**
 * WordPress dependencies
 */
import { Fragment } from '@wordpress/element'

/**
 * Internal dependenies
 */
import Field from '../fields'
import canAddField from '@rank-math-settings/helpers/fieldDependency'
import '../scss/TabFields.scss'

/**
 * Render tab fields.
 *
 * @param {Object} props             Component props.
 * @param {Array}  props.fields      Array of tab fields.
 * @param {string} props.settingType The setting type.
 */
export default ( { settingType, fields } ) => {
	return (
		<div className="field-wrap form-table wp-core-ui rank-math-ui">
			<div
				id={ `field-metabox-rank-math-${ settingType }` }
				className="field-metabox field-list"
			>

				{ map( fields, ( field ) => {
					const { id, type, name, desc, classes, content } = field

					if ( field.dependency && ! canAddField( field ) ) {
						return
					}

					if ( type === 'raw' ) {
						return content
					}

					const containerClasses = classNames(
						'field-row',
						`field-id-${ id }`,
						`field-type-${ type }`,
						classes
					)

					return (
						<Fragment key={ id }>
							<div className={ containerClasses }>
								{ name && (
									<div className="field-th">
										<label htmlFor={ name }>{ name }</label>
									</div>
								) }

								<div className="field-td">
									<Field settingType={ settingType } field={ field } />

									{ ( desc && type !== 'file' ) && (
										<p
											className="field-description"
											dangerouslySetInnerHTML={ { __html: desc } }
										/>
									) }
								</div>
							</div>
						</Fragment>
					)
				} ) }
			</div>
		</div>
	)
}
