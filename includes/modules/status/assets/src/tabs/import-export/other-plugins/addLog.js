export default ( message, logger, setLogger ) => {
	const currentdate = new Date()
	const text =
		'[' +
		( 10 > currentdate.getHours() ? '0' : '' ) +
		currentdate.getHours() +
		':' +
		( 10 > currentdate.getMinutes() ? '0' : '' ) +
		currentdate.getMinutes() +
		':' +
		( 10 > currentdate.getSeconds() ? '0' : '' ) +
		currentdate.getSeconds() +
		'] ' +
		message

	logger.push( text )
	setLogger( [ ...logger ] )
}
