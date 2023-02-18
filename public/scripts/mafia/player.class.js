class Player {
    id = '0';
    num = 0;
    name = 'Player';
    role = 0;
    fouls = 0;
    prim = ''

    points = 0.0;
    adds = 0.0;

    muted = false;
    out = false;
    bestMove = false;

    puted = {};

    #row = null;
    #putedCell = null
    // #primCell = null;

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

    // get primCell() {
    //     return this.#primCell;
    // }

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
        nick.dataset.actionDblclick = 'game-put-him';
        this.row.append(nick);

        this.#putedCell = document.createElement('td');
        this.row.append(this.putedCell);

        for (let foul = 1; foul < 5; foul++){
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
    addPoints()
    {
        let points = prompt(`Додаткові бали!\nНа Ваш розсуд, скільки можна додати балів гравцю №${this.num} (${this.name})?`,'0.0')
        if (points && points != 0.0)
        {
            points = parseFloat(points.replace(',', '.'));
            alert(`Гравцю №${this.num} ${(points > 0.0 ? ' додано ' : ' виписан штраф в ')} ${points} балів рейтинга!`);

            this.adds += points;
        }
    }
    addFouls(foulNum) {
        if (foulNum === '1' && this.fouls > 0){
            this.fouls--;
        }
        else if (foulNum === '4'){
            this.fouls = confirm(`Гравець №${this.num} (${this.name}) отримав дискваліфікуючий фол?`) ? 5 : this.fouls + 1;
        }
        else {
            this.fouls++;
        }
        if (this.fouls === 3) {
            this.muted = true;
        }
        return this.fouls;
    }
    unmute() {
        this.muted = false;
    }
    load(state) {
        for (let property in state) {
            this[property] = state[property];
        }
        return this;
    }
}