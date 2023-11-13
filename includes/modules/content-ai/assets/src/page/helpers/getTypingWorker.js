/* global Worker */

const typingWorkerCode = () => {
	return `
	let words = [];
	let currentWordIndex = 0;
	let currentLetterIndex = 0;
	let typingSpeed = 50; // Adjust the typing speed as needed
	
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

export default () => {
	return new Worker( URL.createObjectURL( new Blob( [ typingWorkerCode() ], { type: 'application/javascript' } ) ) )
}
