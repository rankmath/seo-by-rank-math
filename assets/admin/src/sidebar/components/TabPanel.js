/**
 * External dependencies
 */
import classnames from 'classnames'
import { partial, noop, find, isEmpty } from 'lodash'

/**
 * WordPress dependencies
 */
import { Component } from '@wordpress/element'
import { withInstanceId } from '@wordpress/compose'
import { Button, NavigableMenu } from '@wordpress/components'

const TabButton = ( { tabId, onClick, children, selected, ...rest } ) => (
	<Button
		role="tab"
		tabIndex={ selected ? null : -1 }
		aria-selected={ selected }
		id={ tabId }
		onClick={ onClick }
		{ ...rest }
	>
		{ children }
	</Button>
)

class TabPanel extends Component {
	constructor() {
		super( ...arguments )
		const { tabs, initialTabName } = this.props

		this.state = {
			isOpen: false,
			selected:
				initialTabName || ( tabs.length > 0 ? tabs[ 0 ].name : null ),
		}
	}

	handleClick = ( tabKey ) => {
		const { onSelect = noop } = this.props
		this.setState( {
			selected: tabKey,
		} )
		onSelect( tabKey )
	}

	onNavigate = ( childIndex, child ) => {
		child.click()
	}

	toggle = () => {
		this.setState( ( state ) => ( {
			isOpen: ! state.isOpen,
		} ) )
	}

	render() {
		const { selected } = this.state
		const {
			activeClass = 'is-active',
			className,
			instanceId,
			orientation = 'horizontal',
			tabs,
		} = this.props

		const selectedTab = find( tabs, { name: selected } )
		const selectedId = instanceId + '-' + selectedTab.name
		const remaining = tabs.slice( 4 )

		return (
			<div className={ className }>
				<NavigableMenu
					role="tablist"
					orientation={ orientation }
					onNavigate={ this.onNavigate }
					className="components-tab-panel__tabs"
				>
					{ tabs.slice( 0, 4 ).map( ( tab ) => (
						<TabButton
							className={ classnames(
								'components-tab-panel__tabs-item',
								tab.className,
								{
									[ activeClass ]: tab.name === selected,
								}
							) }
							tabId={ instanceId + '-' + tab.name }
							aria-controls={
								instanceId + '-' + tab.name + '-view'
							}
							selected={ tab.name === selected }
							key={ tab.name }
							onClick={ partial( this.handleClick, tab.name ) }
						>
							{ tab.title }
						</TabButton>
					) ) }

					{ ! isEmpty( remaining ) && (
						<Button
							onClick={ this.toggle }
							aria-expanded={ this.state.isOpen }
							className={ classnames( 'components-tab-panel__tabs-item', { active: this.state.isOpen } ) }
						>
							<i className="rm-icon rm-icon-plus"></i>
							<span>More</span>
						</Button>
					) }

					{ this.state.isOpen && (
						<div className="rank-math-extra-menu">
							{ remaining.map( ( tab ) => (
								<TabButton
									className={ classnames(
										'components-tab-panel__tabs-item',
										tab.className,
										{
											[ activeClass ]: tab.name === selected,
										}
									) }
									tabId={ instanceId + '-' + tab.name }
									aria-controls={
										instanceId + '-' + tab.name + '-view'
									}
									selected={ tab.name === selected }
									key={ tab.name }
									onClick={ partial( this.handleClick, tab.name ) }
								>
									{ tab.title }
								</TabButton>
							) ) }
						</div>
					) }
				</NavigableMenu>
				{ selectedTab && (
					<div
						aria-labelledby={ selectedId }
						role="tabpanel"
						id={ selectedId + '-view' }
						className="components-tab-panel__tab-content"
					>
						{ this.props.children( selectedTab ) }
					</div>
				) }
			</div>
		)
	}
}

export default withInstanceId( TabPanel )
