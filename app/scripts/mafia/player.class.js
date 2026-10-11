class Player {
    id = '0';
    num = 0;
    name = 'Player';
    role = 0;
    fouls = 0;
    _prim = ''

    points = 0.0;
    adds = 0.0;
    pointsLog = [];

    muted = false;
    out = false;
    bestMove = false;

    puted = {};

    #row = null;
    #putedCell = null
    #primField = null;
    #mutedText = 'Мовчить';

    constructor(playerData = null) {
        if (!playerData) return true;

        for (let property in playerData) {
            this[property] = playerData[property];
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

        let nickPlace = document.createElement('td');
        nickPlace.dataset.actionDblclick = 'game-put-him';
        
        let nick = document.createElement('div');
        nick.classList.add('player__name');
        nick.innerText = this.name;

        nickPlace.append(nick);

        let fouls = document.createElement('div');
        fouls.classList.add('player__fouls');
        for (let f = 1; f < 5; f++) {
            let foul = document.createElement('div');
            // foul.dataset.actionDblclick = 'game-fouls';
            foul.dataset.foul = f;
            foul.classList.add('fail');
            fouls.append(foul);
        }
        nickPlace.append(fouls);
        
        this.#primField = document.createElement('div');
        this.#primField.classList.add('player__prim');
        this.#primField.innerText = this.prim;
        nickPlace.append(this.#primField);

        this.row.append(nickPlace);

        this.#putedCell = document.createElement('td');
        this.row.append(this.#putedCell);

        let dashboard = document.createElement('td');

        let foulButton = document.createElement('i');
        foulButton.classList.add('fa', 'fa-exclamation-triangle');
        foulButton.dataset.actionClick = 'game-fouls-menu';
        foulButton.dataset.playerId = index;

        dashboard.append(foulButton);

        this.row.append(dashboard);

        this.putedCell.addEventListener('mouseenter', (event) => this.showPointsLog.call(this, event));
        this.putedCell.addEventListener('mouseleave', (event) => this.hidePointsLog.call(this, event));
        return this.row;
    }
    addPoints() {
        let points = prompt(`Додаткові бали!\nНа Ваш розсуд, скільки можна додати балів гравцю №${this.num} (${this.name})?`, '0.0')
        if (points && points != 0.0) {
            points = parseFloat(points.replace(',', '.'));
            const message = `Гравцю №${this.num} ${(points > 0.0 ? ' додано ' : ' виписан штраф в ')} ${points} балів рейтинга!`;
            alert(message);

            this.adds += points;
            this.pointsLog.push({'Manager Adds': points});
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
    showPointsLog(event){
        const target = event.target;
        if (!(target.classList.contains('positive') || target.classList.contains('negative'))) return false;

        target.style.position = 'relative';
        
        const pointsLogBlock = document.createElement('div');
        pointsLogBlock.classList.add('pointlog');

        const pointsLogTitle = document.createElement('h4');
        pointsLogTitle.classList.add('pointlog__title');
        pointsLogTitle.innerText = 'Points history:';
        pointsLogBlock.append(pointsLogTitle);

        const pointsLogContents = document.createElement('div');
        let summ = 0.0;
        for (let index=0; index < this.pointsLog.length; index++){
            const logRow = document.createElement('div');
            for(const [key, value] of Object.entries(this.pointsLog[index])){
                logRow.classList.add('pointlog__row', value > 0 ? 'positive' : 'negative');
                const logRowLabel = document.createElement('span');
                logRowLabel.classList.add('pointlog__label');
                logRowLabel.innerText = key + ': ';
                const logRowValue = document.createElement('span');
                logRowLabel.classList.add('pointlog__value');
                logRowValue.innerText = value;
                logRow.append(logRowLabel);
                logRow.append(logRowValue);
                summ += value;
            }
            pointsLogContents.append(logRow);
        }
        const logRow = document.createElement('div');
        logRow.classList.add('pointlog__result', summ > 0 ? 'positive' : 'negative');
        const logRowLabel = document.createElement('span');
        logRowLabel.classList.add('pointlog__label');
        logRowLabel.innerText = 'Result: ';
        const logRowValue = document.createElement('span');
        logRowLabel.classList.add('pointlog__value');
        logRowValue.innerText = summ;
        logRow.append(logRowLabel);
        logRow.append(logRowValue);
        pointsLogContents.append(logRow);

        pointsLogBlock.append(pointsLogContents);
        this.putedCell.append(pointsLogBlock);
    }
    hidePointsLog(event){
        const target = event.target;
        if (!(target.classList.contains('positive') || target.classList.contains('negative'))) return false;

        const pointsLogBlock = target.querySelector('.pointlog');
        if (!pointsLogBlock) return false;

        pointsLogBlock.remove();
    }
}