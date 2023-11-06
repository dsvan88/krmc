class MafiaEngine extends GameEngine {
    stage = 'Start';
    subStage = 'DistributeRoles';
    _stageDescr = '';
    
    finish = false;
    daysCount = -1;
    prevStage = null;
    timer = null;
    winners = false;
    showRoles = false;

    debate = false;
    needFix = false;
    courtBlock = false;
    dynamicOrder = true;

    speakers = [];
    speakersList = [];
    shooting = [];
    killed = [];
    bestMove = [];
    lastWill = [];
    debaters = [];
    courtRoom = [];
    courtLog = [];
    voted = [];
    breakers = [];
    leaveThisRound = [];
    staticOrder = [];
    roles = ['peace', 'peace', 'peace', 'peace', 'peace', 'peace', 'mafia', 'mafia', 'sherif', 'don'];
    roleNames = {
        'peace': 'Мирний',
        'mafia': 'Мафія',
        'don': 'Дон',
        'sherif': 'Шериф',
    };

    reasons = ['', 'Вбитий', 'Засуджений', '4 Фола', 'Дісквал.'];

    prevSpeaker = null;
    activeSpeaker = null;
    lastWillReason = null;
    playerVotedId = null;

    config = {
        getOutHalfPlayersMin: 4,
        bestMovePlayersMin: 9,
        killsPerNight: 1,
        mutedSpeakMaxCount: 5,
        timerMax: 6000,
        lastWillTime: 6000,
        debateTime: 3000,
        mutedSpeakTime: 3000,
        courtAfterFouls: true,
        wakeUpRoles: 2000,
        voteType: 'enum', // 'count'
        points: {
            winner: 1.0,
            bestMove: [0.0, 0.0, 0.25, 0.4],
            aliveMafs: [0.0, 0.3, 0.15, 0.3],
            aliveReds: [0.0, 0.0, 0.15, 0.1],
            fourFouls: -0.1,
            disqualified: -0.3,
            sherifFirstStaticKill: 0.3,
            sherifFirstDynamicKill: 0.3,
            voteInSherif: -0.1,
        }
    };

    #courtRoomList = null;
    #alert = null;
    #prompt = null;
    #noticer = null;

    constructor(data) {
        super(data);
        this.stageDescr = 'Початок гри.\nРоздача ролей на гру.';
        this.addEvents();
        try {
            this.noticer = new Noticer();
        } catch (error) {
            this.noticer = null;
        }
        this.config.gamePass = btoa(this.config.gamePass);
    }

    get logKey() {
        return `${this.stage} №${this.daysCount}`;
    }
    get defendant() {
        if (this.debaters.length > 0) {
            let defendant = this.debaters.shift();
            return defendant instanceof Player ? defendant : this.players[defendant];
        }
        return null;
    }
    get lastWiller() {
        if (this.lastWill.length > 0) {
            let willer = this.lastWill.shift();
            return willer instanceof Player ? willer : this.players[willer];
        }
        return null;
    }
    get courtRoomList() {
        if (this.#courtRoomList)
            return this.#courtRoomList;

        this.#courtRoomList = this.gameTable.closest('.game').querySelector('.courtroom');

        if (this.#courtRoomList)
            return this.#courtRoomList;

        throw new Error('Element Courtroom not found in DOM tree!');
    }

    get prompt() {
        return this.#prompt;
    }
    set prompt(data) {
        if (!data) {
            if (!this.#prompt)
                return null;

            this.#prompt.close();
            this.#prompt = null;

            return null;
        }
        if (this.config.voteType === 'enum') {
            if (data.block) {
                data.block = this.getOutPlayers();
                for (const voted of this.voted) {
                    data.block = [...data.block, ...voted.voted];
                }
            } else {
                data.block = this.getOutPlayers();
            }
            this.#prompt = new MafiaVoteNumpad(data);
        }
        else this.#prompt = new Prompt(data);
        return true;
    }
    get alert() {
        return this.#alert;
    }
    set alert(data) {
        if (!data) {
            if (!this.#alert)
                return null;

            this.#alert.close();
            this.#alert = null;

            return null;
        }
        this.#alert = new Alert(data);
        return true;
    }
    
    get rolePrompt() {
        return this.#prompt;
    }
    set rolePrompt(data) {
        if (!data) {
            if (!this.#prompt)
                return null;

            this.#prompt.close();
            this.#prompt = null;

            return null;
        }

        data.roles = Array.from(new Set(this.roles));
        this.#prompt = new MafiaRolesPad(data);
        return true;
    }
    /**
     * @param {string} descr
     */
    set stageDescr(descr) {
        this._stageDescr = descr;
        let descrBlock = this.gameTable.closest('.game').querySelector('.game__stage');
        descrBlock.innerHTML = descr.replace(/\n/g, '<br>');
    }
    get stageDescr() {
        return this._stageDescr;
    }
    /**
     * @param {Object} noticer
     */
    set noticer(noticer) {
        this.#noticer = noticer;
    }
    get noticer() {
        return this.#noticer;
    }
    load(state) {
        super.load(state);
        this.stageDescr = this._stageDescr;
        this.resetLog()
        this.resetView();

        if (this.prompt) {
            this.prompt = null;
        }

        this.players.forEach(player => player.role = player.role ? player.role : 'peace');

        if (this.playerVotedId !== null) {
            for (let x = this.voted.length - 1; x >= 0; --x) {
                if (this.voted[x]['id'] !== this.playerVotedId) continue;
                this.votesAll += this.voted[x].votes;
                this.voted.splice(x, 1);
                break;
            }

            this.prompt = {
                title: 'Голосування',
                text: `Хто за те, аби наше місто покинув гравець №${this.players[this.playerVotedId].num}`,
                input: {
                    type: 'number',
                    min: 0,
                    max: this.votesAll,
                },
                pause: true,
                block: true,
                value: '',
                action: voted => this.processVotes.call(this, voted),
            };
        }
        return true;
    }
    undo() {
        let state = this.prevStates.pop();
        if (!state) return false;
        this.load(state)
        this.timer.reset();
    };
    dispatchNext() {
        this.gameTable.dispatchEvent(new Event("next"));
    }
    addEvents(){
        const self = this;
        document.addEventListener("keyup", (event) => self.keyUpHandler.call(self, event));
        self.gameTable.addEventListener("next", (event) => self.next.call(self, event));
        const buttons = document.querySelectorAll('*[data-action-game]');
        buttons.forEach(button => button.addEventListener('click', (event) => self[camelize(button.dataset.actionGame)].call(self, event)));
    }
    keyUpHandler(event) {
        if (this.prompt) return false;
        let num = null;
        if (event.keyCode >= 48 && event.keyCode <= 57) {
            num = event.keyCode - 48;
        }
        else if (event.keyCode >= 96 && event.keyCode <= 105) {
            num = event.keyCode - 96;
        }
        if (num === null) return true;

        if (--num === -1) num = 9;
        this.putPlayer(num);
    }
    async send(){
        let state = {};
        for (let property in this) {
            if (['prevStates', 'timer'].includes(property)) continue;
            state[property] = this[property];
        }
        state = JSON.stringify(state);
        await super.send(state);
    }
    getNextSubStage() {
        if (this.stage !== 'Start' && this.theEnd()) return 'Finish';
        const method = `${this.stage}SubStage`;
        return this[method]();
    }
    getNextStage() {
        if (this.stage === 'Night' || this.stage === 'Start') {
            return 'Day';
        }
        if (this.stage === 'Day') {
            if (this.courtRoom.length)
                return 'Court';
            return 'Night';
        }
        if (this.stage === 'Court') {
            return 'Night';
        }
    }
    StartSubStage(){
        if (this.prevStage !== this.stage){
            return 'DistributeRoles';
        }
        if (this.subStage === 'DistributeRoles'){
            return this.checkRoles() ? 'WakeUpDon' : 'DistributeRoles';
        }
        if (this.subStage === 'WakeUpDon'){
            return 'WakeUpMafia';
        }
        if (this.subStage === 'WakeUpMafia'){
            return 'WakeUpSherif';
        }
        return false;
    }
    DaySubStage(){
        if (this.prevStage !== this.stage){
            return this.prevStage !== 'Start' && this.shootingCheck() ? 'LastWill' : 'Morning';
        }
        if (this.lastWill.length){
            return 'LastWill';
        }
        if (this.subStage === 'LastWill'){
            return 'Morning';
        }
        if (this.speakers.length){
            return 'Speaker';
        }
        return false;
    }
    CourtSubStage(){
        if (!this.checkLeaveThisRound()){
            return false;
        }
        if (this.prevStage !== this.stage){
            if (!this.debate && !confirm((this.courtRoom.length > 0 ? `На голосування обрані гравці з номерами: ${this.courtList(this.courtRoom)}.` : 'Ніхто не був виставлений.') + `\nУсе вірно?`)) {
                return 'FixCourtroom';
            }
            return 'CourtStart';
        }
        if (this.debaters.length){
            return 'CourtDebating';
        }
        if (this.subStage === 'FixCourtroom' || this.subStage === 'CourtDebating'){
            return 'CourtStart';
        }
        if (this.courtRoom.length){
            return 'CourtVoting';
        }
        if (this.subStage === 'CourtStart' && !this.courtRoom.length){
            return false;
        }
        if (this.subStage === 'CourtResult' || this.subStage === 'LastWill'){
            if (this.lastWill.length)
                return 'LastWill';
            return false;
        }
        return 'CourtResult';
    }
    NightSubStage(){
        if (this.prevStage !== this.stage){
            return 'MafiaShooting';
        }
        if (this.subStage === 'MafiaShooting'){
            return 'WakeUpDon';
        }
        if (this.subStage === 'WakeUpDon'){
            return 'WakeUpSherif';
        }
        return false;
    }
    next() {
        if (this.prompt && this.prompt.pause) return false;

        this.save();

        this.prevStage = this.stage;
        this.prevSubStage = this.subStage;

        this.subStage = this.getNextSubStage();
        if (!this.subStage){
            this.stage = this.getNextStage();
            this.subStage = this.getNextSubStage();
        }

        try {
            this[this.subStage]();
        }
        catch(error) {
            throw new Error(`Something went wrong:(\nStage: ${this.stage}\nSubStage: ${this.subStage}\nError: ${error.message}`);
        }
        finally{
            this.resetView()
            this.send();
        }         
    };
    resetLog() {
        let _log = this._log;
        this._log = {};
        this.log = _log;
    }
    displayRoles(event){
        if (this.stage === 'Start' || this.showRoles || btoa(prompt('Enter game PIN-code:')) === this.config.gamePass)
            this.showRoles = !this.showRoles;
        this.resetView();
    }
    stopGame(){
        const winner = prompt("Stop game?\nSet Winner's Team:\n0 - Cancel;\n1 - Peace;\n2 - Mafia;\n3 - Even.", '0');
        if (winner){
            this.theEnd(winner);
            this[this.subStage]();
            return false;
        }
        return false;
    }
    resetView() {
        this.clearView();
        this.applyView();
    }
    clearView() {
        this.players.forEach(player => {
            player.row.classList.remove('speaker', 'shooted', 'out', 'best-moved', 'fixing', 'peace', 'mafia', 'don', 'sherif');

            player.putedCell.innerText = '';
            player.putedCell.classList.remove('puted');

            player.primField.innerText = '';
            for (let foul = 1; foul <= 4; foul++) {
                let foulCell = player.row.querySelector(`[data-foul="${foul}"]`);
                if (foulCell)
                    foulCell.classList.remove('fail');
                if (foul === 3)
                    foulCell.innerText = '';
            }
        });
        this.closeCourtroom();
    };
    applyView() {
        this.players.forEach(player => {
            if (player.puted[this.daysCount] >= 0) {
                player.putedCell.innerText = player.puted[this.daysCount] + 1;
                player.putedCell.classList.add('puted');
            }
            if (player.out) {
                player.row.classList.add('out');
            }
            if (player.prim) {
                player.prim = player.prim;
            }
            if (this.showRoles && player.role !== 'peace')
                player.prim = player.role;
            else if (this.roles.includes(player.prim))
                player.prim = '';

            if (this.shooting.includes(player.id) || this.stage === 'firstNight' && this.staticOrder.includes(player.id)) {
                player.row.classList.add('shooted');
            }
            if (this.activeSpeaker && this.activeSpeaker.bestMove && this.bestMove.includes(player.id)) {
                player.row.classList.add('best-moved');
            }

            if (player.fouls > 0) {
                for (let foul = 1; foul <= player.fouls; foul++) {
                    let foulCell = player.row.querySelector(`[data-foul="${foul}"]`);
                    if (foulCell)
                        foulCell.classList.add('fail');
                    if (foul === 3 && player.muted) {
                        foulCell.innerText = '🤐';
                    }
                }
            }
            if (player.points || player.adds) {
                const points = player.points + player.adds
                player.putedCell.innerText = points > 0 ? `+${points}` : points;
                player.putedCell.classList.add('points', points >= 0 ? 'positive' : 'negative');
            }
        })
        if (this.activeSpeaker) {
            this.activeSpeaker.row.classList.add('speaker');
        }
        if (this.courtRoom.length > 0) {
            this.openCourtroom();
        }
    };
    putPlayer(playerId) {
        if (this.stage === 'Finish') {
            this.players[playerId].addPoints();
        }
        else if (this.stage === 'Start' && this.subStage === 'DistributeRoles') {
            this.actionSetRole(playerId)
        }
        else if (this.stage === 'Start' && this.subStage === 'WakeUpMafia') {
            this.actionAddStaticKillOrder(playerId)
        }
        else if (this.stage === 'Day' && this.subStage === 'LastWill' && this.activeSpeaker.bestMove) {
            this.actionBestMove(playerId)
        }
        else if (this.stage === 'Day' && this.subStage === 'Speaker') {
            this.putPlayerOnVote(playerId);
        }
        else if (this.stage === 'Court' && this.subStage === 'FixCourtroom') {
            this.fixPlayerVote(playerId);
        }
        else if (this.stage === 'Night' && this.subStage === 'MafiaShooting') {
            this.shootPlayer(playerId);
        }
        else if (this.stage === 'Night' && (this.subStage === 'WakeUpDon' || this.subStage === 'WakeUpSherif')) {
            this.checkPlayerRole(playerId);
        }
        this.resetView();
    };
    actionSetRole(playerId) {
        const self = this;
        this.rolePrompt = {
            title: `Обрання ролі`,
            text: `Оберіть роль для гравця №${self.players[playerId].num} (${self.players[playerId].name})`,
            pause: true,
            choosed: self.players[playerId].role,
            action: role => {
                self.players[playerId].role = role ? role : self.players[playerId].role;
                self.resetView();
                this.noticer.add({ message: `Гравцю №${self.players[playerId].num}, назначена роль ${self.players[playerId].role}.`, time: 5000 });
                this.rolePrompt = null;
            },
        };
    };
    actionAddStaticKillOrder(playerId) {
        let message = '';
        if (this.staticOrder.includes(playerId)) {
            this.staticOrder.splice(this.staticOrder.indexOf(playerId), 1);
            message = `Зміна замовлення!\nГравець №${this.players[playerId].num} - видалений зі статичного замовлення.`;
            this.addLog(message);
            this.noticer.add({ message: message, time: 5000 });
            if (this.staticOrder.length === 0)
                this.dynamicOrder = true;
            return false;
        }

        this.staticOrder.push(playerId);
        this.dynamicOrder = false;
        message = `Мафія обирає гравця №${this.players[playerId].num}, у статичне замовлення на відстріл.`;
        this.addLog(message);
        this.noticer.add({ message: message, time: 5000 });

        this.resetView();
    };
    shootPlayer(playerId) {
        if (this.shooting.includes(playerId)) {
            this.shooting.splice(this.shooting.indexOf(playerId), 1);
            return false;
        }

        this.shooting.push(playerId);

        this.addLog(`Мафія стріляє у гравця №${this.players[playerId].num}!`);

        this.resetView();
    };
    playerFouls(id, foulNum) {
        let player = this.players[id];

        if (player.out > 0) return false;

        if (player.addFouls(foulNum) >= 4) {
            this.outPlayer(id, player.fouls - 1);
            this.leaveThisRound.push(id);
        }
        this.resetView();
    }
    outPlayer(id, reason) {

        this.players[id].out = reason;

        if (reason < 3) {
            this.lastWillReason = reason;
            this.lastWill.push(id);
        }
        this.players[id].prim = this.reasons[reason];

        if (this.players[id].muted) {
            this.players[id].unmute();
        }

        this.addLog(`Гравець №${this.players[id].num} - залишає наше місто. Привід: ${this.reasons[reason]}!`);
        this.noticer.add({ message: `Гравець №${this.players[id].num} - залишає наше місто. Привід: ${this.reasons[reason]}!`, time: 5000 });
        return true;
    };
    putPlayerOnVote(putedId) {
        if (this.players[putedId].out > 0) {
            this.addLog(`Не прийнято! За столом нема гравця №${this.players[putedId].num}!`);
            return false;
        }
        let maker = (this.timer.left === this.config.timerMax ? this.players[this.prevSpeaker] : this.activeSpeaker);

        if (!maker) return false;

        if (maker.puted[this.daysCount] >= 0 && maker.puted[this.daysCount] !== putedId) return false;

        let check = this.courtRoom.indexOf(putedId);
        if (check === -1) {
            this.courtRoom.push(putedId);
            maker.puted[this.daysCount] = putedId;
            this.addLog(`Гравець №${maker.num} - виставляє гравця №${this.players[putedId].num} (${this.players[putedId].name})!`);
            this.noticer.add({ message: `Гравець №${maker.num} - виставляє гравця №${this.players[putedId].num} (${this.players[putedId].name})!`, time: 5000 });
        }
        else {
            if (maker.puted[this.daysCount] === putedId) {
                this.courtRoom.splice(check, 1);
                maker.puted[this.daysCount] = -1;
                this.addLog('Помилкове виставлення. Відміна!');
                this.noticer.add({ type: 'info', message: 'Помилкове виставлення. Відміна!', time: 5000 });
            }
            else {
                this.addLog(`Гравець №${maker.num} - виставляє гравця № ${this.players[putedId].num}!\nНе прийнято! Вже виставлений!`);
                this.noticer.add({ type: 'error', message: `Гравець №${maker.num} - виставляє гравця № ${this.players[putedId].num}!\nНе прийнято! Вже виставлений!`, time: 5000 });
                return false;
            }
        }
    };
    fixPlayerVote(playerId) {

        let maker = this.players[playerId];

        if (maker.out > 0) {
            alert(`За столом нема гравця №${maker.num}!`);
            return false;
        }

        let putedNum = null;
        do {
            putedNum = prompt(`Гравець №${maker.num}, під час своєї промови ставив гравця №:`, 0);
        } while (isNaN(putedNum) || putedNum < 0 || putedNum > 10)


        if (putedNum === null)
            return false;

        putedNum = parseInt(putedNum) || 0;
        if (!putedNum || putedNum > 10) {

            if (maker.puted[this.daysCount] === -1)
                return false;

            const wrongId = maker.puted[this.daysCount];
            this.addLog(`Гравець №${maker.num} - не виставляв гравця №${this.players[wrongId].num} (${this.players[wrongId].name})!`);
            this.noticer.add({ type: 'info', message: `Гравець №${maker.num} - не виставляв гравця №${this.players[wrongId].num} (${this.players[wrongId].name})!`, time: 5000 });
            maker.puted[this.daysCount] = -1;
            return this.rebuildCourtroom();
        }

        const putedId = putedNum - 1;
        let check = this.courtRoom.indexOf(putedId);
        if (check === -1) {
            maker.puted[this.daysCount] = putedId;
            this.addLog(`Гравець №${maker.num} - виставляє гравця №${this.players[putedId].num} (${this.players[putedId].name})!`);
            this.noticer.add({ message: `Гравець №${maker.num} - виставляє гравця №${this.players[putedId].num} (${this.players[putedId].name})!`, time: 5000 });
        }
        else {
            this.addLog(`Гравець №${maker.num} - виставляє гравця № ${this.players[putedId].num}!\nНе прийнято! Вже висталений!`);
            this.noticer.add({ type: 'error', message: `Гравець №${maker.num} - виставляє гравця № ${this.players[putedId].num}!\nНе прийнято! Вже висталений!`, time: 5000 });
            return false;
        }
        return this.rebuildCourtroom();
    };
    shootingCheck() {
        if (this.config.killsPerNight !== 1) return false;

        if (this.shooting.length === 1) {
            let killed = this.shooting.pop();
            this.killed[this.daysCount].push(killed);
            if (this.checkFirstKill() && this.getActivePlayersCount() >= this.config.bestMovePlayersMin) {
                this.players[killed].bestMove = true;
                const message = `Гравець №${this.players[killed].num} - вбит першим!\nВ нього є право залишити по собі кращій хід`;
                this.addLog(message, true);
            }
            return this.outPlayer(killed, 1);
        }
        this.shooting.length = 0;
        const message = 'Промах! Ніхто не був вбитий цією ніччю.';
        this.addLog(message, true);
        return false;
    }
    DistributeRoles(){
        this.stageDescr = 'Початок гри.\nРоздача ролей на гру.';
        return true;
    }
    Morning() {
        ++this.daysCount;
        this.killed.push([]);
        this.players.forEach(player => player.puted[this.daysCount] = -1);

        this.prevSpeaker = null;
        this.speakers = this.getSpeakers();
        this.speakersList = [...this.speakers];
        this.debate = false;
        this.courtBlock = false;
        this.leaveThisRound.length = 0;

        this.next();
    }
    getOutPlayers() {
        const players = [];
        this.players.forEach((player) => (player.out > 0) ? players.push(player.id) : false);
        return players;
    }
    getNonVotedPlayers() {
        let votedPlayers = [];
        for (const voted of this.voted) {
            votedPlayers = [...votedPlayers, ...voted.voted];
        }

        const players = [];
        this.players.forEach((player) => (player.out > 0 || votedPlayers.includes(`${player.id}`)) ? false : players.push(player.id));
        return players;
    }
    getActivePlayersCount(role = null) {
        return this.players.reduce((playersCount, player) => {
            if (player.out > 0) return playersCount;
            if (role === 'peace' && (player.role === 'mafia' || player.role === 'don')) return playersCount; // Если ищем мафов - отсекаем миров
            if (role === 'mafia' && (player.role === 'peace' || player.role === 'sherif')) return playersCount; // Если ищем миров - отсекаем мафов
            return ++playersCount;
        }, 0);
    }
    getSpeakers() {
        let speakers = [];
        let shifted = [];
        let speakerOffset = this.daysCount >= this.maxPlayers ? this.daysCount%this.maxPlayers : this.daysCount;

        this.players.forEach((player, index) => {
            if (player.out > 0) return;
            if (index < speakerOffset || index <= this.speakersList[0])
                shifted.push(player.id);
            else
                speakers.push(player.id);
        })
        if (shifted.length > 0) {
            return speakers.concat(shifted);
        }
        return speakers;
    }
    nextSpeaker() {
        let player;
        for (; ;) {
            player = this.players[this.speakers.shift()];
            if (player === this.activeSpeaker) continue;
            if (player.muted && player.out > 0) {
                player.unmute();
                continue;
            }
            if (player.out > 0) continue;

            if (!player.muted) return player;

            if (this.getActivePlayersCount() < this.config.mutedSpeakMaxCount && this.config.mutedSpeakTime > 0) {
                player.unmute();
                this.timer.left = this.config.mutedSpeakTime;
                return player;
            }
            let put = parseInt(prompt(`Гравець №${player.num} мовчить, але може виставити кандидатуру: `, '0').trim());
            if (put > 0) {
                this.prevSpeaker = player.id;
                this.putPlayerOnVote(put - 1);
            };
            player.unmute();
        }
    };
    checkLeaveThisRound() {
        if (this.config.courtAfterFouls || this.leaveThisRound.length === 0) return true;

        const message = `Сьогодні нас ${(this.leaveThisRound.length > 1 ? 'покинули гравці №' + this.courtList(this.leaveThisRound) : 'покинув гравець №' + this.players[this.leaveThisRound[0]].num)}.\nГолосування не проводится.`;
        this.courtRoom.length = 0;
        this.debaters.length = 0;
        this.defendantCount = 0;
        this.courtBlock = true;

        this.addLog(message, true);
        return false;
    }
    CourtStart() {
        this.activeSpeaker = null;

        do {
            this.courtLog.push([])
        } while (!this.courtLog[this.daysCount]);

        let message = '';

        if (this.courtRoom.length === 0) {
            message += 'Зал суда.\n\nНа голосування ніхто не був виставлений. Голосування не проводиться.'
            this.addLog(message);
            this.noticer.add(message)
            return this.dispatchNext();
        }

        this.stageDescr = 'Зал суда.\nПрохання до гравців припинити будь-яку комунікацію та прибрати руки від стола';
        message = `Шановні гравці, ми переходимо до зали суду!\nНа ${(this.debate ? 'перестрільці' : 'голосуванні')} знаходяться гравці з номерами: ${this.courtList(this.courtRoom)}\n`;

        this.needFix = false;

        this.voted = [];
        this.maxVotes = 0;
        this.votesAll = this.playersCount = this.getActivePlayersCount();
        this.defendantCount = this.courtRoom.length;

        alert(message);

        if (this.defendantCount === 1) {
            message = 'На голосування був виставлений лише 1 гравець\n';
            let playerId = this.courtRoom.pop();
            if (this.daysCount > 0) {
                message += `Наше місто покидає гравець №${this.players[playerId].num}!`;
                alert(message + '\nВи маєте 1 минуту для останьої промови.');
                this.outPlayer(playerId, 2);
            }
            else {
                message += `Цього недостатньо для проведення голосування.`;
                alert(message + '\n\nНаступає фаза ночі!')
            }
            this.addLog(message);
            return this.dispatchNext();
        }

        return this.dispatchNext();
    }
    CourtVoting() {

        this.playerVotedId = this.courtRoom.shift();

        if (this.votesAll < 1) {
            return this.processVotes(this.config.voteType === 'count' ? 0 : false);
        }
        if (this.courtRoom.length === 0) {
            return this.processVotes(this.config.voteType === 'count' ? this.votesAll : this.getNonVotedPlayers().join(','));
        }

        this.prompt = {
            title: 'Голосування',
            text: `Хто за те, аби наше місто покинув гравець №${this.players[this.playerVotedId].num}`,
            input: {
                type: 'number',
                min: 0,
                max: this.votesAll,
            },
            pause: true,
            block: true,
            value: '',
            action: voted => this.processVotes.call(this, voted),
        };
    }
    processVotes(vote) {
        let voted = [], votes = 0;
        if (this.config.voteType === 'enum') {
            if (vote !== false && vote !== '') {
                voted = vote.split(',');
                votes = voted.length;
            }
        }
        else {
            votes = parseInt(vote) || 0;
        }

        if (votes > this.votesAll) votes = this.votesAll;

        this.voted.push({ id: this.playerVotedId, votes: votes, voted: voted });

        this.votesAll -= votes;
        if (this.maxVotes < votes) {
            this.maxVotes = votes;
        }
        this.prompt = null;

        return this.dispatchNext();
    }
    processVotesMassGetOut(vote) {

        let voted = [], votes = 0;
        if (this.config.voteType === 'enum') {
            if (vote !== false && vote !== '') {
                voted = vote.split(',');
                votes = voted.length;
                this.courtLog[this.daysCount].push([]);
            }
        }
        else {
            votes = parseInt(vote) || 0;
        }

        const _debaters = this.courtList(this.debaters);

        if (votes > this.playersCount) votes = this.playersCount;

        let message = '';
        if (votes > this.playersCount / 2) {
            message += `Більшість (${votes} з ${this.playersCount}) - за!\nГравці під номерами: ${_debaters} залишають наше місто.`;
            while (this.debaters.length > 0) {
                let defendant = this.defendant;
                this.outPlayer(defendant.id, 2);
                if (voted.length > 0) {
                    this.courtLog[this.daysCount][this.courtLog[this.daysCount].length - 1].push({ id: defendant.id, votes: voted.length, voted: [...voted], massOut: true });
                }
            }
        }
        else
            message = `Більшість (${this.playersCount - votes}) з ${this.playersCount}) - проти!\nНіхто не покидає стол.`;

        this.prompt = null;
        this.debaters.length = 0;
        this.addLog(message, true);
        return this.dispatchNext();
    }
    CourtResult() {
        this.courtLog[this.daysCount].push([...this.voted]);
        this.playerVotedId = null;
        let message = `Підведемо ітоги голосування:\n`;
        this.voted.forEach(data => {
            message += `Гравець  № ${this.players[data.id].num} \tГолосів: ${data.votes}\n`;
            if (data.votes !== this.maxVotes) return;
            this.debaters.push(data.id);
        });

        if (this.debaters.length === 1) {
            const player = this.defendant;
            message += `\nНас залишає Гравець № ${player.num}.\nВи маєте хвилину на прощання.`;
            this.outPlayer(player.id, 2);
            this.addLog(message, true);

            const firstVoted = this.getFirstVoted();

            if (firstVoted.length === 1 && firstVoted[0].id === player.id) {
                this.prompt = {
                    title: 'Був злам голосування?',
                    text: `Якщо злам був й чітко зрозуміло, хто це зробив - вкажіть їх номери:`,
                    value: '',
                    pause: false,
                    action: breakers => this.saveBreakers.call(this, breakers),
                };
            }
            return this.dispatchNext();
        }

        const _debaters = this.courtList(this.debaters);
        message += 'В нашому місті перестрілка. Між гравцями, під номерами: ' + _debaters;
        alert(message);

        if (this.debate && this.debaters.length === this.defendantCount) {
            if (this.playersCount > this.config.getOutHalfPlayersMin) {
                this.voted.length = 0;
                return this.prompt = {
                    title: 'Голосування',
                    text: `Хто за те, аби гравці №${_debaters}, покинули наше місто?`,
                    input: {
                        type: 'number',
                        min: 0,
                        max: this.playersCount,
                    },
                    pause: true,
                    value: '',
                    action: voted => this.processVotesMassGetOut.call(this, voted),
                };
            }
            message = `При кількості гравців менш ${this.config.getOutHalfPlayersMin} не можна підняти 2 та більше гравців.\nНихто не покидає наше місто.`;
            this.debaters.length = 0;
        }
        if (this.debaters.length > 0) {
            this.debate = true;
            this.courtRoom = this.debaters.slice(0);
        }
        this.addLog(message);
        return this.dispatchNext();
    }
    saveBreakers(breakers) {
        this.prompt = null;

        if (!breakers) return false;

        breakers.split(',').forEach(breaker => this.breakers.push(this.config.voteType === 'enum' ? +breaker : --breaker));
        this.addLog('Злам на голосуванні! ' + (this.breakers.length > 1 ? 'Відповідальні, гравці під номерами: ' : 'Відповідальний, гравець № ') + this.courtList(this.breakers));
    }
    MafiaShooting(){
        this.activeSpeaker = null;
        const message = 'Мафія підіймає свою зброю та стріляє по гравцям.\nЗробіть Ваш вибір!'
        this.stageDescr = `Ніч №${this.daysCount}.\n${message}`;
        this.addLog(message);
    }
    WakeUpDon() {
        if (this.stage==='Start'){
            this.timer.left = Math.floor(this.config.wakeUpRoles / 2);
            this.stageDescr = `Прокидається Дон.\nВи маєте до ${this.config.wakeUpRoles / 200} секунд, аби подивитись на місто.`;
            return true;
        }
        this.timer.left = Math.floor(this.config.wakeUpRoles);
        this.stageDescr = `Прокидається Дон та шукає шерифа.\nДон може перевірити гравця на наявність роль Шерифа.`;
        return true;
    };
    WakeUpMafia() {
        this.stageDescr = 'Прокидаються гравці мафії.\nУ вас є хвилина на узгодження дій.';
        return true;
    };
    WakeUpSherif() {        
        this.timer.left = this.config.wakeUpRoles;
        this.stageDescr = 
            this.stage==='Start' ? 
            `Прокидається Шериф.\nВи маєте ${this.config.wakeUpRoles / 100} секунд, аби подивитись на місто.` : 
            `Прокидається Шериф та шукає мафію.\nШериф може перевірити належність гравця до однієї з команд.`;
        return true;
    };
    Speaker() {
        this.prevSpeaker = this.activeSpeaker ? this.activeSpeaker.id : null;
        this.activeSpeaker = this.nextSpeaker();
        this.stageDescr = `День №${this.daysCount}.\nПромова гравця №${this.activeSpeaker.num}`;
    };
    CourtDebating() {
        this.timer.left = this.config.debateTime;
        this.activeSpeaker = this.defendant;
        this.stageDescr = `Перестрілка.\nПромова гравця №${this.activeSpeaker.num}`;
    };
    LastWill() {
        this.timer.left = this.config.lastWillTime;
        this.activeSpeaker = this.lastWiller;
        this.stageDescr = `Заповіт.\nПромова гравця №${this.activeSpeaker.num}`;
        if (this.daysCount === 0 && this.lastWillReason === 1 && this.dynamicOrder) {
            this.dynamicOrder = confirm(`Гравця №${this.activeSpeaker.num}, вбили за динамікою?`)
        }
    };
    actionBestMove(playerId) {

        if (!this.activeSpeaker.bestMoveAuthor) {
            this.activeSpeaker.bestMoveAuthor = true;
        }

        this.bestMove.push(playerId);
        if (this.bestMove.length === 3) {
            const message = `Гравець №${this.activeSpeaker.num} вважає гравцями мафії, гравців, под номерами: ${this.courtList(this.bestMove)}`;
            if (confirm(message + '?')) {
                this.activeSpeaker.bestMove = false;
                this.addLog(message);
            }
            else {
                this.bestMove.length = 0;
                this.activeSpeaker.bestMoveAuthor = false;
            }
        }
    }
    compareBestMove() {
        return this.bestMove.reduce((count, playerId) => this.players[playerId].role === 'mafia' || this.players[playerId].role == 'don' ? ++count : count, 0);
    }
    FixCourtroom() {
        this.needFix = true;
        const message = 'Ведучій помилився із виставленими гравцями...\nВиправляємо!';
        this.stageDescr = `День №${this.daysCount}.\n${message}`;
        this.addLog(message);
    }
    rebuildCourtroom() {
        const courtroom = [];
        this.speakersList.forEach(playerId => {
            if (this.players[playerId].puted[this.daysCount] < 0) return false;
            courtroom.push(this.players[playerId].puted[this.daysCount]);
        })
        this.courtRoom = courtroom;
        this.openCourtroom();
    }
    courtList(list) {
        return list.reduce((result, defendant) => result += `${defendant + 1}, `, '').slice(0, -2);
    }
    openCourtroom() {
        let message = `На голосування обрані гравці під номерами: ${this.courtList(this.courtRoom)}.`;
        if (this.leaveThisRound.length > 0) {
            message += '\nАле голосування, цього раунду - не проводитиметься, бо нас ' +
                (this.leaveThisRound.length > 1 ?
                    `покинули гравці: ${this.courtList(this.leaveThisRound)}` :
                    `покинув гравець №${this.leaveThisRound[0] + 1}.`);
        }
        this.courtRoomList.innerText = message;
    }
    closeCourtroom() {
        this.courtRoomList.innerText = '';
    }
    checkPlayerRole(playerId){
        if (this.alert) return false;
        
        let check = '';
        if (this.subStage === 'WakeUpDon'){
            check = this.players[playerId].role === 'sherif' ? '<b class="positive">Шериф</b>👌' : '<b class="negative">не Шериф</b>🤞';
        }
        else if (this.subStage === 'WakeUpSherif'){
            check = this.players[playerId].role === 'mafia' || this.players[playerId].role === 'don' ? 'команда <b class="negative">Мафії</b>👎' : 'команда <b class="positive">Мирних</b>👍';
        }
        this.alert = {
            title: `Перевірка ролі гравця №${this.players[playerId].num} (${this.players[playerId].name}).`,
            text: `Гравець №${this.players[playerId].num} (${this.players[playerId].name}) - ${check}!`,
            close: () => this.alert = null,
        };
    }
    checkFirstKill() {
        let check = this.killed.reduce((killedCount, killedAtDay) => killedCount + killedAtDay.length, 0);
        return check === 1;
    }
    checkFirstKillSheriff() {
        for (const [day, killed] of this.killed.entries()) {
            if (killed[0]) return killed.shift();
        }
        return false;
    }
    checkBreakerIsMafia() {
        if (this.breakers.length === 0) return true;
        for (let playerId of this.breakers) {
            if (this.players[playerId].role === 'mafia' || this.players[playerId].role === 'don') return true;
        }
        return false;
    }
    checkVotedInSheriffFirst() {
        const voted = this.getFirstVoted();
        for (let candidat = 0; candidat < voted.length; candidat++) {
            if (this.players[voted[candidat].id].role !== 'sherif') continue;
            return voted[candidat].voted;
        }
        return [];
    }
    getFirstVoted() {
        let day = -1;
        let fisrtVoted = [];
        let maxVote = 0;
        do {
            if (++day >= this.daysCount + 2) break;

            if (!this.courtLog[day] || this.courtLog[day].length === 0 || this.courtLog[day][this.courtLog[day].length - 1].length === 0) continue;

            let voted = this.courtLog[day][this.courtLog[day].length - 1];

            for (let candidat = 0; candidat < voted.length; candidat++) {
                if (voted[candidat].massOut) return voted;
                if (voted[candidat].votes < maxVote) continue;

                if (fisrtVoted.length > 0) fisrtVoted.length = 0;
                fisrtVoted.push(voted[candidat]);
                maxVote = voted[candidat].votes;
            }
        }
        while (fisrtVoted.length === 0)

        return fisrtVoted;
    }
    addLog(message, show = false) {
        let logEntity = {};
        logEntity[this.logKey] = message;
        this.log = logEntity;

        if (show) {
            alert(message);
        }
    }
    checkRoles(){
        const roles = {
            'peace' : 0,
            'sherif' : 0,
            'mafia' : 0,
            'don' : 0
        }
        this.players.forEach(player => {
            roles[player.role]++;
        });

        if (roles['mafia'] !== 2 || roles['don'] !== 1 || roles['sherif'] !== 1) {
            this.noticer.add({type: 'info', message:'Невірно розподілені ролі!\nРолей всього:\nМафії - 2\nДон - 1\nШеріф - 1\nМирні - 6', time: 5000});
            return false;
        }
        return true;
    }
    Finish() {
        if (!this.finish)
            this.assignPoints();
        this.resetView();
        alert(this.stageDescr.replace(/BR/g, '\n'));
        this.finish = true;
        return 'Finish';
    }
    assignPoints() {
        let red = this.getActivePlayersCount('peace'),
            mafs = this.getActivePlayersCount('mafia'),
            bestMove = this.compareBestMove(),
            firstKill = this.checkFirstKillSheriff(),
            votedInSheriff = this.checkVotedInSheriffFirst(),
            breakerIsMafia = this.checkBreakerIsMafia();

        if (red > 3) red = 3;

        this.players.forEach(player => {
            player.pointsLog = [];
            if (player.out == 4) {
                player.points += this.config.points.disqualified;
                player.pointsLog.push({ 'Disqualification': this.config.points.disqualified })
                return true;
            }
            if (player.out == 3) {
                player.points += this.config.points.fourFouls;
                player.pointsLog.push({ 'FourFouls': this.config.points.fourFouls })
            }
            if (player.bestMoveAuthor && bestMove > 0) {
                player.points += this.config.points.bestMove[bestMove];
                player.pointsLog.push({ 'BestMove': this.config.points.bestMove[bestMove] })
            }
            if (player.role === 'peace' || player.role === 'sherif') {
                if (this.winners == 1) {
                    player.points += this.config.points.winner;
                    player.pointsLog.push({ 'Winners': this.config.points.winner });
                    if (!player.out) {
                        player.points += this.config.points.aliveReds[red];
                        player.pointsLog.push({ 'AliveRed': this.config.points.aliveReds[red] });
                    }
                }
                else {
                    if (!breakerIsMafia && votedInSheriff.includes(player.id)) {
                        player.points += this.config.voteInSherif;
                        player.pointsLog.push({ 'VotedInSherif': this.config.voteInSherif });
                    }
                }
            }
            else {
                if (this.winners == 2) {
                    player.points += this.config.points.winner;
                    player.pointsLog.push({ 'Winners': this.config.points.winner });
                    if (!player.out) {
                        player.points += this.config.points.aliveMafs[mafs];
                        player.pointsLog.push({ 'AliveMafia': this.config.points.aliveMafs[mafs] });
                    }
                }
                if (firstKill && this.players[firstKill].role == 'sherif') {
                    if (this.dynamicOrder) {
                        player.points += this.config.points.sherifFirstDynamicKill;
                        player.pointsLog.push({ 'FirstKillSherifDynamic': this.config.points.sherifFirstDynamicKill });
                    }
                    else {
                        player.points += this.config.points.sherifFirstStaticKill;
                        player.pointsLog.push({ 'FirstKillSherifStatic': this.config.points.sherifFirstStaticKill });
                    }
                }
            }
        })
    }
    theEnd(winner) {
        if (this.winners)
            return true;
        let message = '',
            red = this.getActivePlayersCount('peace'),
            mafs = this.getActivePlayersCount('mafia');
        if (mafs === 0 || winner === '1') {
            this.winners = 1;
            message = 'Мирне місто!\nВідтепер Ваші діти можуть спати спокійно!';
        }
        else if (mafs >= red || winner === '2') {
            this.winners = 2;
            message = "Мафію!\nВідтепер Ваші діти можуть спати сито й спокійно!";
        }
        if (this.winners) {
            this.stageDescr = `Вітаємо з перемогою: ${message}`;
            this.stage = 'Finish';
            this.subStage = 'Finish';
            return true;
        }
        return false;
    }
}