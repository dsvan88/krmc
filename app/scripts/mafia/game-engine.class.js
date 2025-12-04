class GameEngine {

    #gameTable = null;
    #logBlock = null;

    gameId = 0;
    players = [];
    maxPlayers = 10;

    prevStates = [];
    maxStatesSave = 30;

    _log = {};

    config = {};

    get gameTable() {
        return this.#gameTable;
    }
    get logBlock() {
        if (!this.#logBlock) {
            this.#logBlock = this.gameTable.closest('.game').querySelector('.game__log');
        }
        return this.#logBlock;
    }
    /**
     * @param {(arg0: Object) => void} data
     */
    set log(data) {
        for (let [key, value] of Object.entries(data)) {
            if (!this._log[key])
                this._log[key] = [];
            if (value instanceof Array)
                this._log[key] = this._log[key].concat(value);
            else
                this._log[key].push(value);
        }
        this.logBlock.innerHTML = '';

        for (let [key, value] of Object.entries(this._log)) {
            let message = value.join('</br></br>').replace(/\n/g, '<br>');
            let block = `
            <div class="game__log-entity">
                <div class="game__log-day">${key}: </div>
                <div class="game__log-events">${message}</div>
            </div>`
            this.logBlock.insertAdjacentHTML('beforeend', block);
        }
    }
    constructor({ gameTable = null }) {
        if (typeof gameTable === "string") {
            gameTable = document.querySelector(gameTable);
        }
        this.#gameTable = gameTable;
        this.init();
    }
    async init() {
        this.gameId = parseInt(window.location.pathname.replace(/[^0-9]+/g, ''));
        const promise = new Promise((resolve) => {
            request({url: 'game/' + this.gameId, success: resolve})
        })
        const result = await promise.then()
        this.pending  = false;

        let players = JSON.parse(result.players);

        for (let index = 0; index < this.maxPlayers; index++) {
            let player = new Player({
                id: index,
                name: players[index].name,
                role: players[index].role,
            });
            this.players.push(player);
            this.gameTable.append(player.getRow(index));
        }
        if (result.state) {
            this.load(result.state);
            if (result.prevstates)
                this.prevStates = JSON.parse(result.prevstates);
        }
        this.gameTable.dispatchEvent(new Event('ready'));
        return true;        
    }
    save() {
        let state = {};
        for (let property in this) {
            if (['prevStates', 'timer'].includes(property)) continue;
            state[property] = this[property];
        }
        state = JSON.stringify(state);
        this.prevStates.push(state);

        if (this.prevStates.length > this.maxStatesSave)
            this.prevStates.shift();
        return true;
    }
    load(state) {
        state = JSON.parse(state);
        for (let property in state) {
            if (property === 'players') {
                this.loadPlayersStates(state[property]);
                continue;
            }
            if (property === 'config') {
                for (let setting in state[property]) {
                    this.config[setting] = state[property][setting];
                }
                continue;
            }
            if (property === 'activeSpeaker') {
                if (state[property])
                    this.activeSpeaker = this.players[state[property].id];
                else
                    this.activeSpeaker = null;
                continue;
            }
            this[property] = state[property];
        }
        return true;
    }
    loadPlayersStates(state) {
        return this.players.forEach((player, index) => player.load(state[index]));
    }
    async send(state) {
        const data = new FormData;
        data.append('state', state);
        data.append('prevstates', JSON.stringify(this.prevStates));
        await request({
            url: 'game/save/' + this.gameId,
            data: data,
            success: (result) => debug && console.log(result),
        })
    }
}