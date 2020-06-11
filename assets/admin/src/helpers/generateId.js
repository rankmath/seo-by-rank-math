export default ( prefix ) => {
	return `${ prefix }-${ new Date().getTime() }`
}
