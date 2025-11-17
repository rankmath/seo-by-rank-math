/**
 * Local SEO additional fields.
 */
export default () => {
	return [
		{
			id: 'type',
			type: 'select',
			options: rankMath.organizationInfo,
			default: '',
		},
		{
			id: 'value',
			type: 'text',
		},
	]
}
