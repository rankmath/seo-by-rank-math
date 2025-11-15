import { highlightContent } from '../../../../../includes/modules/content-ai/assets/src/helpers/fixAnalysisTest'

function getWidgetContentById( widgetId ) {
	// Check if Elementor is available
	if ( typeof elementor === 'undefined' || typeof $e === 'undefined' ) {
		outPutError( 'Elementor is not available' )
		return ''
	}

	// Validate input
	if ( ! widgetId || typeof widgetId !== 'string' ) {
		outPutError( 'Invalid widget ID provided' )
		return ''
	}

	try {
		if ( widgetId.includes( '-' ) ) {
			// Return tab content.
			return getTabContent( widgetId )
		}

		// Find widget by ID
		const widget = elementor.getContainer( widgetId )
		if ( ! widget ) {
			outPutError( 'Widget with ID not found: ' + widgetId )
			return ''
		}

		// Get widget type and settings
		const widgetType = widget.model.get( 'widgetType' )
		const settings = widget.model.get( 'settings' ).attributes

		// Get text content based on widget type
		let content = ''

		switch ( widgetType ) {
			case 'heading':
				content = `<${ settings.header_size }>${ settings.title }</${ settings.header_size }>`
				break
			case 'text-editor':
				content = settings.editor
				break
			default:
				// For unknown widgets, try common text settings
				content = settings.title || settings.text || settings.content || settings.editor || ''
		}

		return content
	} catch ( error ) {
		outPutError( 'Error getting widget text' )
		return ''
	}
}

function getContainersForContentFixWithAI() {
	const widgetTypes = [ 'heading', 'text-editor', 'accordion', 'tabs' ]
	// Check if Elementor is available
	if ( typeof elementor === 'undefined' || typeof $e === 'undefined' ) {
		outPutError( 'Elementor is not available' )
		return []
	}

	try {
		// Get current document
		const doc = elementor.documents.getCurrent()
		if ( ! doc ) {
			outPutError( 'No document found' )
			return []
		}

		// Find all heading and text editor containers
		const containers = {}

		function findContainers( container ) {
			if ( ! container.children ) {
				return
			}
			container.children.forEach( ( child ) => {
				const widgetType = child?.model.get( 'widgetType' )
				if ( widgetType && widgetTypes.includes( widgetType ) ) {
					if ( 'accordion' === widgetType || 'tabs' === widgetType ) {
						child.children.forEach( ( grandChild, index ) => {
							containers[ child.model.get( 'id' ) + '-' + index ] = grandChild
						} )
					} else {
						containers[ child.model.get( 'id' ) ] = child
					}
				}
				if ( child.children ) {
					findContainers( child )
				}
			} )
		}

		findContainers( doc.container )

		return containers
	} catch ( error ) {
		outPutError( 'Error finding containers: ' + error )
		return []
	}
}

export function updateWithAIGeneratedContent( apiResponse, referencesForWidgetIds ) {
	function insertOrAppendWidgetUsingContent( position, refWidgetId, content, prevTag, index ) {
		if ( ! position ) {
			// Replacing content.
			const originalContent = getWidgetContentById( refWidgetId )
			if ( index === 0 ) {
				updateWidgetContentById( refWidgetId, highlightContent( originalContent, content ) )
				return prevTag
			}
		}
		const currentReferenceId = referencesForWidgetIds[ refWidgetId ]
		if ( content.startsWith( '<h' ) ) {
			referencesForWidgetIds[ refWidgetId ] = insertWidget( position || 'after', currentReferenceId, highlightContent( '', content ) )
			return 'h'
		}

		if ( prevTag !== 'p' ) {
			referencesForWidgetIds[ refWidgetId ] = insertWidget( position || 'after', currentReferenceId, highlightContent( '', content ) )
		} else {
			// Append to previous widget
			const widgetText = getWidgetContentById( currentReferenceId )
			updateWidgetContentById( currentReferenceId, highlightContent( '', widgetText + content ) )
		}
		return 'p'
	}

	apiResponse.forEach( ( response ) => {
		const refWidgetId = response.refBlockId
		let prevTag = ''
		const contentsBetweenHTMLTags = response.content.match( /<([a-z0-9]+)([^>]*)>(.*?)<\/\1>/gis )
		if ( response.action === 'replace' ) {
			const originalContent = getWidgetContentById( refWidgetId )
			if ( ! contentsBetweenHTMLTags.length ) {
				updateWidgetContentById( refWidgetId, highlightContent( originalContent, response.content ) )
				return
			}
		}
		if ( ! referencesForWidgetIds.hasOwnProperty( refWidgetId ) ) {
			referencesForWidgetIds[ refWidgetId ] = refWidgetId
		}

		contentsBetweenHTMLTags.forEach( ( contentBetweenHTMLTag, index ) => {
			prevTag = insertOrAppendWidgetUsingContent( response.position, refWidgetId, contentBetweenHTMLTag, prevTag, index )
		} )
	} )
}

function getTabContainer( tabContainerId ) {
	const idParts = tabContainerId.split( '-' )
	const parentContainer = elementor.getContainer( idParts[ 0 ] )
	return parentContainer.children[ idParts[ 1 ] ]
}

function getTabContent( tabContainerId ) {
	return getTabContainer( tabContainerId ).getSetting( 'tab_content' )
}

function updateWidgetContentById( widgetId, text ) {
	// Validate input
	if ( ! widgetId || typeof widgetId !== 'string' ) {
		outPutError( 'Invalid widget ID provided' )
		return
	}

	if ( ! text || typeof text !== 'string' ) {
		outPutError( 'Invalid text provided. Text must be a non-empty string.' )
		return
	}

	try {
		// Get current document
		const doc = elementor.documents.getCurrent()
		if ( ! doc ) {
			outPutError( 'No document found' )
			return
		}

		if ( widgetId.includes( '-' ) ) {
			const tabContainer = getTabContainer( widgetId )
			if ( tabContainer ) {
				$e.run( 'document/elements/settings', {
					container: tabContainer,
					settings: {
						tab_content: text,
					},
				} )
				return
			}
		}

		// Find widget by ID
		const widget = elementor.getContainer( widgetId )
		if ( ! widget ) {
			outPutError( 'Widget with ID not found: ' + widgetId )
			return
		}

		// Get widget type
		const widgetType = widget.model.get( 'widgetType' )

		// Determine the correct setting key based on widget type
		let settingKey = 'title' // default for most widgets

		switch ( widgetType ) {
			case 'heading':
				settingKey = 'title'
				text = text.replace( /<\/?h[1-6][^>]*>/gi, '' )
				break
			case 'text-editor':
				settingKey = 'editor'
				break
			case 'tabs':
			case 'accordion':
				settingKey = 'tabs'
				break
		}

		// Prepare settings object
		const settings = {}
		settings[ settingKey ] = text

		// Update the widget
		$e.run( 'document/elements/settings', {
			container: widget,
			settings,
		} )
	} catch ( error ) {
		outPutError( 'Error updating widget text: ' + error )
	}
}

function insertWidget( position, refWidgetId, content ) {
	// Validate inputs
	if ( ! position || ! [ 'before', 'after' ].includes( position ) ) {
		outPutError( 'Position must be "before" or "after"' )
		return refWidgetId
	}

	if ( ! refWidgetId || typeof refWidgetId !== 'string' ) {
		outPutError( 'Invalid reference widget ID' )
		return refWidgetId
	}

	if ( ! content || typeof content !== 'string' ) {
		outPutError( 'Content must be a non-empty string' )
		return refWidgetId
	}

	try {
		// Get current document
		const doc = elementor.documents.getCurrent()
		if ( ! doc ) {
			outPutError( 'No document found' )
			return refWidgetId
		}
		if ( refWidgetId.includes( '-' ) ) {
			const referencedTab = getTabContainer( refWidgetId )
			if ( ! referencedTab ) {
				outPutError( 'Referenced Tab not found: ' + refWidgetId )
				return refWidgetId
			}
			$e.run( 'document/elements/settings', {
				container: referencedTab,
				settings: {
					tab_content: referencedTab.getSetting( 'tab_content' ) + content,
				},
			} )
			return refWidgetId
		}
		// Find reference widget
		const refWidget = elementor.getContainer( refWidgetId )
		if ( ! refWidget ) {
			outPutError( 'Reference widget not found: ' + refWidgetId )
			return refWidgetId
		}

		// Detect if content contains HTML heading tags
		const headingMatch = content.match( /<h([1-6])[^>]*>(.*?)<\/h[1-6]>/i )
		let widgetType, settings, headerSize

		if ( headingMatch ) {
			// Content contains HTML heading tag
			widgetType = 'heading'
			headerSize = 'h' + headingMatch[ 1 ] // Extract the heading level (h1, h2, etc.)
			const headingText = headingMatch[ 2 ] // Extract text content from the heading tag

			settings = {
				title: headingText,
				header_size: headerSize,
			}
		} else {
			// No heading tag, use text editor
			widgetType = 'text-editor'
			settings = {
				editor: content,
			}
		}

		const referenceContainer = elementor.getContainer( refWidgetId )
		const referenceIndex = referenceContainer.parent.model.get( 'elements' ).findIndex( referenceContainer.model )

		// Insert the widget
		const newWidget = $e.run( 'document/elements/create', {
			container: referenceContainer.parent,
			model: {
				elType: 'widget',
				widgetType,
				settings,
			},
			options: {
				at: position === 'before' ? referenceIndex : referenceIndex + 1,
			},
		} )
		return newWidget.model.get( 'id' )
	} catch ( error ) {
		outPutError( 'Error inserting widget: ' + error )
		return refWidgetId
	}
}

function outPutError( message ) {
	console.error( '❌ ' + message )
}

export function contentTestApprove() {
	const containers = getContainersForContentFixWithAI()
	Object.keys( containers ).forEach( ( id ) => {
		const widgetContent = getWidgetContentById( id ).replace( /<mark\s+class="rank-math-highlight"\s+style="background-color:\s*#fee894;?"[^>]*>(.*?)<\/mark>/gi, '$1' )
		updateWidgetContentById( id, widgetContent )
	} )
}

export function contentTestReject( originalBlocks ) {
	const containers = getContainersForContentFixWithAI()
	// If container is not in the originalblocks, delete it
	const blockIds = Object.keys( originalBlocks )
	Object.keys( containers ).forEach( ( id ) => {
		if ( blockIds.includes( id ) ) {
			return
		}
		$e.run( 'document/elements/delete', {
			container: containers[ id ],
		} )
	} )

	Object.keys( originalBlocks ).forEach( ( widgetId ) => {
		updateWidgetContentById( widgetId, originalBlocks[ widgetId ] )
	} )
}

export function getContentAIOriginalBlocks() {
	const containers = getContainersForContentFixWithAI()
	const result = {}
	Object.keys( containers ).forEach( ( id ) => {
		result[ id ] = getWidgetContentById( id )
	} )
	return result
}
