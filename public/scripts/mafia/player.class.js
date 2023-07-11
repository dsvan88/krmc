class Player {
    id = '0';
    num = 0;
    name = 'Player';
    role = 0;
    fouls = 0;
    _prim = ''

    points = 0.0;
    adds = 0.0;

    muted = false;
    out = false;
    bestMove = false;

    puted = {};

    #row = null;
    #putedCell = null
    #primField = null;
    #mutedText = 'Мовчить';

    constructor(playerData = null) {
        if (playerData) {
            for (let property in playerData) {
                this[property] = playerData[property];
            }
        }
    }

    get row() {
        return this.#row;
    }

    get putedCell() {
        return this.#putedCell;
    }

    get primField() {
        return this.#primField;
    }
    set prim(text) {
        this._prim = text;
        this.#primField.innerText = text;
    }
    get prim() {
        return this._prim;
    }

    getRow(index) {
        this.#row = document.createElement('tr');
        this.row.classList.add('player');
        this.row.dataset.playerId = index;

        this.num = index + 1;
        let num = document.createElement('td');
        num.innerText = this.num;
        num.dataset.actionDblclick = 'game-put-him';
        this.row.append(num);

        let nick = document.createElement('td');
        nick.innerText = this.name;
        nick.classList.add('player__name');
        nick.dataset.actionDblclick = 'game-put-him';

        this.#primField = document.createElement('div');
        this.#primField.classList.add('player__prim');
        this.#primField.innerText = this.prim;
        nick.append(this.#primField);

        this.row.append(nick);

        this.#putedCell = document.createElement('td');
        this.row.append(this.putedCell);

        for (let foul = 1; foul < 5; foul++) {
            let cell = document.createElement('td');
            cell.dataset.actionDblclick = 'game-fouls';
            cell.dataset.foul = foul;
            this.row.append(cell);
        }

        // this.#primCell = document.createElement('td');
        // this.primCell.innerText = this.prim;
        // this.row.append(this.primCell);

        return this.row;
    }
    addPoints() {
        let points = prompt(`Додаткові бали!\nНа Ваш розсуд, скільки можна додати балів гравцю №${this.num} (${this.name})?`, '0.0')
        if (points && points != 0.0) {
            points = parseFloat(points.replace(',', '.'));
            alert(`Гравцю №${this.num} ${(points > 0.0 ? ' додано ' : ' виписан штраф в ')} ${points} балів рейтинга!`);

            this.adds += points;
        }
    }
    addFouls(foulNum) {
        if (foulNum === '1' && this.fouls > 0) {
            this.fouls--;
        }
        else if (foulNum === '4') {
            this.fouls = confirm(`Гравець №${this.num} (${this.name}) отримав дискваліфікуючий фол?`) ? 5 : this.fouls + 1;
        }
        else {
            this.fouls++;
        }
        if (this.fouls === 3) {
            this.mute();
        }
        return this.fouls;
    }
    mute() {
        this.muted = true;
        this.prim = this.#mutedText;
    }
    unmute() {
        this.muted = false;
        if (this.prim === this.#mutedText)
            this.prim = '';
    }
    load(state) {
        for (let property in state) {
            this[property] = state[property];
        }
        return this;
    }
}