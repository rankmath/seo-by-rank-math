/* global Worker */

const typingWorkerCode = ( speed = 25 ) => {
	return `
	let words = [];
	let currentWordIndex = 0;
	let currentLetterIndex = 0;
	let typingSpeed = ${ speed }
	
	function typeWords() {
		if ( currentWordIndex >= words.length ) {
			postMessage('rank_math_process_complete')
			return
		}
	
		const currentWord = words[ currentWordIndex ];
		if ( currentLetterIndex < currentWord.length ) {
			postMessage( currentWord );
			currentLetterIndex = currentLetterIndex + currentWord.length;
		} else {
			currentWordIndex++;
			currentLetterIndex = 0;
			postMessage(' '); // Add space between words
		}
	
		setTimeout( typeWords, typingSpeed );
	}
	
	self.onmessage = function (event) {
		words = event.data.split(' ');
		typeWords();
	};
	`
}

export default ( endpoint = '' ) => {
	const typingSpeed = 'Blog_Post_Outline' !== endpoint ? 25 : 10
	return new Worker( URL.createObjectURL( new Blob( [ typingWorkerCode( typingSpeed ) ], { type: 'application/javascript' } ) ) )
}
