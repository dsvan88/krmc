class GameTimer {

    _left = 0;
    maxTime = 0;
	timerInterval = null;
	timerStep = 50;
	game = null;

    timerBlock = null;
    stopwatch = null;

	buttons = {};
	audioContext = null;

	#MainTimer = null;
	
    constructor({ maxTime = 2000, timerBlock = ".timer", gameEngine = null} ={})
	{
		this.timerBlock = timerBlock;
        if (typeof timerBlock === "string"){
			this.timerBlock = document.querySelector(timerBlock);
        }
		this.stopwatch = this.timerBlock.querySelector('.stopwatch');
		
		let buttons = this.timerBlock.querySelectorAll('[data-timer]');
		buttons.forEach(button => {
			button.addEventListener('click', (event) => this[button.dataset.timer].call(this, event));
			this.buttons[button.dataset.timer] = button;
		});
		this.buttons.pause.style.display = 'none';

		if (gameEngine) {
			this.game = gameEngine;
			this.game.timer = this;
		}
		this.left = this.maxTime = (gameEngine && gameEngine.config.timerMax) ? gameEngine.config.timerMax : maxTime;
	};
	/**
	 * @param {number} value
	 */
	set left(value){
		this._left = value;
		this.stopwatch.innerText = this.inttotime(value);
	}

	get left() {
		return this._left;
	}
	start() {
		if (!this.audioContext) {
			this.audioContext = new AudioContext();
		}

		if (this.#MainTimer) return false;

		this.#MainTimer = setInterval(() => this.countdown(), this.timerStep);
		
		this.buttons.start.style.display = 'none';
		this.buttons.pause.style.display = 'block';
	}
	pause() {
		clearInterval(this.#MainTimer);
		this.#MainTimer = null;

		this.buttons.pause.style.display = 'none';
		this.buttons.start.style.display = 'block';
	}
	reset() {
		this.pause();
		this.left = this.maxTime;

		this.buttons.pause.style.display = 'none';
		this.buttons.start.style.display = 'block';
	}
	undo() {
		this.game.undo();

		this.buttons.pause.style.display = 'none';
		this.buttons.start.style.display = 'block';
	}
	next() {
		this.reset()
		this.game.next();

		this.buttons.pause.style.display = 'none';
		this.buttons.start.style.display = 'block';
	}
	countdown() {

		this.left -= 5

		if (this.left < 0)
		{
			this.reset();
			this.next();
			return true;
		}
		
		if (this.left === 100){
			return this.beep(900, 800)
		}

		if ([1000,500,300, 200].indexOf(this.left) !== -1){
			return this.beep();
		}
	}
	beep(duration=100, frequency=500) {
		let oscillator = this.audioContext.createOscillator();
		oscillator.type = 'sine'; // форма сигнала
		oscillator.frequency.value = frequency; // частота
		oscillator.connect(this.audioContext.destination);
		oscillator.start(); //для запуска
		
		let oscillatorTimer = setTimeout(() => oscillator.stop(), duration);
	};
	inttotime(t)
	{
		let params = [
			new String(Math.floor(t/6000)).padStart(2,'0'),
			new String(Math.floor(t%6000/100)).padStart(2,'0'),
			new String(t%100).padStart(2,'0'),
		];
		return params.join(':');
	}
};
