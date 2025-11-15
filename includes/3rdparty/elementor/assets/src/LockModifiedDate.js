/**
 * External dependencies
 */
import jQuery from 'jquery'
import { isUndefined } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Dashicon } from '@wordpress/components'

// Add Lock Modified Date button in Elementor editor.
class LockModifiedDate {
	constructor() {
		if ( ! rankMath.showLockModifiedDate ) {
			return
		}

		this.addTopBarButton()
		jQuery( () => ( this.init() ) )
	}

	init() {
		this.previewLoaded()
		this.onDocumentLoaded()
		this.onContentChange()
	}

	// Add Lock Modified Date under the Publish button in Elementor Top Bar Feature.
	addTopBarButton() {
		const elementorv2 = window.elementorV2
		if ( isUndefined( elementorv2 ) ) {
			return
		}

		const { documentOptionsMenu } = elementorv2.editorAppBar
		documentOptionsMenu.registerToggleAction( {
			id: 'rank-math-lock-modified-date',
			priority: 10,
			useProps: () => {
				return {
					title: __( 'Update (Lock Modified Date)', 'rank-math' ),
					icon: () => <Dashicon icon="calendar" />,
					onClick: () => {
						this.triggerUpdate()
					},
				}
			},
		} )
	}

	// Add Lock Modified Date under the Publish button when Elementor Top Bar Feature is disabled.
	previewLoaded() {
		elementor.once( 'preview:loaded', () => {
			elementor.getRegion( 'panel' ).currentView.footer.currentView.addSubMenuItem( 'saver-options', {
				icon: 'eicon-date',
				name: 'rank-math-lock-modified-date',
				title: __( 'Update (Lock Modified Date)', 'rank-math' ),
				callback: ( e ) => {
					if ( e.currentTarget.classList.contains( 'elementor-disabled' ) ) {
						return
					}

					this.triggerUpdate()
				},
			} )
		} )
	}

	// Set default state for the Lock modified date button.
	onDocumentLoaded() {
		elementor.on( 'document:loaded', ( elementorDocument ) => {
			this.activateButton( 'draft' === elementorDocument.container.settings.get( 'post_status' ) )
			this.lockModifiedDate( false )
		} )
	}

	// Enable Lock Modified date button when content is changed.
	onContentChange() {
		$e.commandsInternal.on( 'run:after', ( _component, command, args ) => {
			switch ( command ) {
				case 'document/save/set-is-modified':
					this.activateButton( args.status )
					break
				case 'document/save/save':
				case 'document/save/default':
					delete elementorCommon.ajax.requestConstants.lock_modified_date
					this.lockModifiedDate( false )
					break
			}
		} )
	}

	// Trigger Update when Lock Modified Date button is clicked.
	triggerUpdate() {
		this.lockModifiedDate( true )
		elementorCommon.ajax.addRequestConstant( 'lock_modified_date', true )
		$e.run( 'document/save/default' )
	}

	// Enable/Disable Lock Modified date button when content is changed.
	activateButton( isChanged ) {
		const updateBtn = document.getElementById( 'elementor-panel-footer-sub-menu-item-rank-math-lock-modified-date' )
		if ( ! updateBtn ) {
			return
		}

		updateBtn.classList.remove( 'elementor-disabled' )
		if ( ! isChanged ) {
			updateBtn.classList.add( 'elementor-disabled' )
		}
	}

	// Update lock modified date state in the store.
	lockModifiedDate( lock ) {
		wp.data.dispatch( 'rank-math' ).lockModifiedDate( lock )
	}
}

export default LockModifiedDate
