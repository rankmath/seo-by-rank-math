export default () => {
	return ! rankMath.contentAI.isUserRegistered || ! rankMath.contentAI.plan || ! rankMath.contentAI.credits || rankMath.contentAI.isMigrating
}
