class MafiaEngine extends GameEngine {

    stage = 'firstNight';
    _stageDescr = '';
    daysCount = -1;
    prevStage = null;
    timer = null;

    debate = false;
    needFix = false;
    speakers = [];
    shooting = [];
    killed = [];
    bestMove = [];
    lastWill = [];
    debaters = [];
    courtRoom = [];
    courtLog = [];
    voted = [];
    leaveThisRound = [];

    reasons = ['', 'Вбитий', 'Засуджений', '4 Фола', 'Дісквал.'];

    prevSpeaker = null;
    activeSpeaker = null;
    lastWillReason = null;
    playerVotedId = null;

    config = {
        getOutHalfPlayersMin: 4,
        killsPerNight: 1,
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
            dis: -0.3,
            sherifFirstKill: 0.3,
        }
    };

    #courtRoomList = null;
    #prompt = null;
    #noticer = null;

    constructor(data) {
        super(data);

        document.addEventListener("keyup", (event) => this.keyUpHandler.call(this, event));
        this.gameTable.addEventListener("next", (event) => this.next.call(this, event));
        this.stageDescr = 'Нульова ніч.\nПрокидаються Дон та гравці мафії.\nУ вас є хвилина на узгодження дій.';
        
        try {
            this.noticer = new Noticer();
        } catch (error) {
            this.noticer = null;
        }
    }

    get logKey() {
        return `${this.stage === 'shootingNight' ? 'Night' : 'Day'} №${this.daysCount}`;
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
        if (this.config.voteType === 'enum'){
            data.block = this.getOutPlayers();
            for(const voted of this.voted){
                data.block = [...data.block, ...voted.voted];
            }
            this.#prompt = new MafiaVoteNumpad(data);
        }
        else this.#prompt = new Prompt(data);
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

        if (this.playerVotedId !== null) {
            for (let x = this.voted.length - 1; x >= 0; --x) {
                if (this.voted[x]['id'] !== this.playerVotedId) continue;
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
    };
    getNextStage() {
        if (this.theEnd() && this.stage === 'finish')
            return 'finish';
        if (this.stage === 'shootingNight') {
            this.shootingCheck();
        }
        if (this.stage === 'firstNight')
            return 'wakeUpRoles';
        else if (this.stage === 'wakeUpRoles' || this.stage === 'shootingNight' && this.lastWill.length === 0 || this.stage === 'actionLastWill' && this.lastWillReason === 1)
            return 'morning';
        else if (this.stage === 'morning' || (this.stage === 'daySpeaker' && this.speakers.length > 0))
            return 'daySpeaker';
        else if ((['daySpeaker', 'actionFixCourtroom'].includes(this.stage) && this.speakers.length === 0) || this.stage === 'actionDebate' && this.debaters.length === 0 && this.courtRoom.length > 0)
            return 'actionCourtStart';
        else if (this.stage === 'actionCourtStart') {
            if (this.needFix) {
                return 'actionFixCourtroom';
            }
            return 'actionCourt';
        }
        else if (this.stage === 'actionCourt') {
            if (this.courtRoom.length > 0) {
                return 'actionCourt';
            }
            return 'actionCourtEnd';
        }
        else if (['actionCourtEnd', 'actionDebate'].includes(this.stage) && this.debaters.length > 0)
            return 'actionDebate';
        else if (this.stage === 'actionDebate' && this.debaters.length === 0)
            return 'actionCourt';
        else if ((this.courtBlock && ['actionCourtStart', 'actionCourt', 'actionDebate', 'actionCourtEnd'].includes(this.stage)) ||
            (this.stage === 'actionCourtEnd' || this.stage === 'actionLastWill' && this.prevStage !== 'shootingNight') && this.courtRoom.length === 0 && this.lastWill.length === 0)
            return 'shootingNight';
        else if (['actionCourtEnd', 'actionLastWill', 'shootingNight'].includes(this.stage) && this.lastWill.length > 0)
            return 'actionLastWill';
    }
    next() {
        if (this.prompt) return false;

        this.save();

        this.prevStage = this.stage;
        this.stage = this.getNextStage();

        if (this[this.stage]) {
            this[this.stage]();
        }
        else
            throw new Error('Something went wrong:(');

        this.resetView()
    };
    dispatchNext() {
        this.gameTable.dispatchEvent(new Event("next"));
    }
    keyUpHandler(event){
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
    resetLog() {
        let _log = this._log;
        this._log = {};
        this.log = _log;
    }
    resetView() {
        this.clearView();
        this.applyView();
    }
    clearView() {
        this.players.forEach(player => {
            player.row.classList.remove('speaker', 'shooted', 'out', 'best-moved', 'fixing');

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
                // player.primField.innerText = this.reasons[player.out];
                // player.primField.innerText = player.prim;
            }
            if (player.prim) {
                player.prim = player.prim;
            }
            if (this.shooting.includes(player.id)) {
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
            if (player.points > 0 || player.adds > 0) {
                const points = player.points + player.adds
                player.putedCell.innerText = points > 0 ? `+${points}` : points;
                player.putedCell.classList.add(points >= 0 ? 'positive' : 'negative')
            }
        })
        if (this.activeSpeaker) {
            this.activeSpeaker.row.classList.add('speaker');
        }
        if (this.courtRoom.length > 0)
            this.openCourtroom();
    };
    putPlayer(playerId) {
        if (this.stage === 'finish') {
            this.players[playerId].addPoints();
        }
        else if (this.stage === 'actionLastWill' && this.activeSpeaker.bestMove) {
            this.actionBestMove(playerId)
        }
        else if (this.stage === 'daySpeaker') {
            this.putPlayerOnVote(playerId);
        }
        else if (this.stage === 'actionFixCourtroom') {
            this.fixPlayerVote(playerId);
        }
        else if (this.stage === 'shootingNight') {
            this.shootPlayer(playerId);
        }
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
        if (this.config.killsPerNight === 1) {
            if (this.shooting.length === 1) {
                let killed = this.shooting.pop();
                this.killed[this.daysCount].push(killed);
                if (this.checkFirstKill()) {
                    this.players[killed].bestMove = true;
                    const message = `Гравець №${this.players[killed].num} - вбит першим!\nВ нього є право залишити по собі кращій хід`;
                    this.addLog(message, true);
                }
                return this.outPlayer(killed, 1);
            }
            else {
                this.shooting.length = 0;
                const message = 'Промах! Ніхто не був вбитий цією ніччю.';
                this.addLog(message, true);
            }
        }
        return false;
    }
    morning() {
        ++this.daysCount;
        this.killed.push([]);
        this.players.forEach(player => player.puted[this.daysCount] = -1);

        this.prevSpeaker = null;
        this.speakers = this.getSpeakers();
        this.courtBlock = false;
        this.debate = false;

        this.next();
    }
    getOutPlayers(){
        const players = [];
        this.players.forEach((player) => (player.out > 0) ? players.push(player.id) : false);
        return players;
    }
    getNonVotedPlayers(){
        let votedPlayers = [];
        for(const voted of this.voted){
            votedPlayers = [...votedPlayers, ...voted.voted];
        }

        const players = [];
        this.players.forEach((player) => (player.out > 0 || votedPlayers.includes(`${player.id}`)) ? false : players.push(player.id));
        return players;
    }
    getActivePlayersCount(role = 0) {
        return this.players.reduce((playersCount, player) => {
            if (player.out > 0) return playersCount;
            if (role === 2 && (player.role == 0 || player.role == 4)) return playersCount; // Если ищем мафов - отсекаем миров
            if (role === 1 && (player.role == 1 || player.role == 2)) return playersCount; // Если ищем миров - отсекаем мафов
            return ++playersCount;
        }, 0);
    }
    getSpeakers() {
        let speakers = [];
        let shifted = [];
        let speakerOffset = this.daysCount >= this.maxPlayers ? this.daysCount - this.maxPlayers : this.daysCount;

        this.players.forEach((player, index) => {
            if (player.out > 0) return;
            if (index < speakerOffset)
                shifted.push(player.id);
            else
                speakers.push(player.id);
        })
        if (shifted.length > 0) {
            shifted.forEach(playerId => speakers.push(playerId));
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

            if (this.getActivePlayersCount() < 5) {
                player.unmute();
                this.timer.left = this.config.mutedSpeakTime;
                return player;
            }
            let put = parseInt(prompt(`Гравець №${player.num} мовчить, але може виставити кандидатуру: `, '0'));
            if (put > 0) {
                this.prevSpeaker = player.id;
                this.putPlayerOnVote(put - 1);
            };
            player.unmute();
        }
    };
    checkLeaveThisRound() {
        if (!this.config.courtAfterFouls) return true;
        if (this.leaveThisRound.length === 0) return true;

        const message = `Сьогодні нас ${(this.leaveThisRound.length > 1 ? 'покинули гравці №' + this.courtList(this.leaveThisRound) : 'покинув гравець №' + this.players[this.leaveThisRound[0]].num)}.\nГолосування не проводится.`;
        this.courtRoom.length = 0;
        this.debaters.length = 0;
        this.defendantCount = 0;
        this.courtBlock = true;

        this.addLog(message, true);
        return false;
    }
    actionCourtStart() {
        this.activeSpeaker = null;
        if (!this.courtLog[this.daysCount]){
            if (this.daysCount > 0)
                this.courtLog.fill([], this.courtLog.length, this.daysCount);
            else 
                this.courtLog.push([]);
        }
            
        if (!this.checkLeaveThisRound()) {
            return this.dispatchNext();
        }

        if (!this.debate && !confirm((this.courtRoom.length > 0 ? `На голосування обрані гравці з номерами: ${this.courtList(this.courtRoom)}.` : 'Ніхто не був виставлений.') + `\nУсе вірно?`)) {
            this.needFix = true;
            return this.dispatchNext();
        }

        this.stageDescr = 'Зал суда.\nПрохання до гравців припинити будь-яку комунікацію та прибрати руки від стола';
        let message = `Шановні гравці, ми переходимо до зали суду!\nНа ${(this.debate ? 'перестрільці' : 'голосуванні')} знаходяться гравці з номерами: ${this.courtList(this.courtRoom)}\n`;

        this.needFix = false;

        this.voted = [],
            this.maxVotes = 0;
        this.votesAll = this.playersCount = this.getActivePlayersCount();
        this.defendantCount = this.courtRoom.length;



        if (this.defendantCount === 0) {
            message += '\nНа голосование никто не выставлен. Голосование не проводится.'
            this.addLog(message, true);
            return this.dispatchNext();
        }

        alert(message);

        if (this.defendantCount === 1) {
            message = 'На голосование был выставлен лишь 1 игрок\n';
            let playerId = this.courtRoom.pop();
            if (this.daysCount > 0) {
                message += `Наш город покидает игрок №${this.players[playerId].num}}!`;
                alert(message + '\nУ вас есть 1 минута для последней речи');
                this.outPlayer(playerId, 2);
            }
            else {
                message += `Этого недостаточно для проведения голосования.`;
                alert(message + '\n\nНаступает фаза ночи!')
            }
            this.addLog(message);
            return this.dispatchNext();
        }

        return this.dispatchNext();
    }
    actionCourt() {
        if (!this.checkLeaveThisRound()) {
            return this.dispatchNext();
        }
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
            value: '',
            action: voted => this.processVotes.call(this, voted),
        };
    }
    processVotes(vote) {

        let voted = [], votes = 0;
        if (this.config.voteType === 'enum'){
            if (vote !== false && vote !== ''){
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
        if (this.config.voteType === 'enum'){
            if (vote !== false && vote !== ''){
                voted = vote.split(',');
                votes = voted.length;
                this.courtLog[this.daysCount].push([...voted]);
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
            while (this.debaters.length > 0)
                this.outPlayer(this.debaters.shift(), 2);
        }
        else
            message = `Більшість (${this.playersCount - votes}) з ${this.playersCount}) - проти!\nНіхто не покидає стол.`;

        this.prompt = null;
        this.debaters.length = 0;
        this.addLog(message, true);
        return this.dispatchNext();
    }
    actionCourtEnd() {

        if (!this.checkLeaveThisRound()) {
            return this.dispatchNext();
        }

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
            return this.dispatchNext();
        }

        const _debaters = this.courtList(this.debaters);
        message += 'В нашем городе перестрелка. Между игроками под номерами: ' + _debaters;
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
                    value: '',
                    action: voted => this.processVotesMassGetOut.call(this, voted),
                };
            }
            message = 'При кількості гравців менш 5 не можна підняти 2 та більше гравців.\nНихто не покидає наше місто.';
            this.debaters.length = 0;
        }
        if (this.debaters.length > 0) {
            this.debate = true;
            this.courtRoom = this.debaters.slice(0);
        }
        this.addLog(message);
        return this.dispatchNext();
    }
    wakeUpRoles() {
        if (!this.config.wakeUpRoles) return this.dispatchNext();

        this.timer.left = this.config.wakeUpRoles;
        this.stageDescr = `Прокидається шериф.\nВи маєте 20 секунд, аби подивитись на місто.`;
    };
    daySpeaker() {
        this.prevSpeaker = this.activeSpeaker ? this.activeSpeaker.id : null;
        this.activeSpeaker = this.nextSpeaker();
        this.stageDescr = `День №${this.daysCount}.\nПромова гравця №${this.activeSpeaker.num}`;
    };
    actionDebate() {
        this.timer.left = this.config.debateTime;
        this.activeSpeaker = this.defendant;
        this.stageDescr = `Перестрілка.\nПромова гравця №${this.activeSpeaker.num}`;
    };
    actionLastWill() {
        this.timer.left = this.config.lastWillTime;
        this.activeSpeaker = this.lastWiller;
        this.stageDescr = `Заповіт.\nПромова гравця №${this.activeSpeaker.num}`;
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
        let count = 0;
        for (let playerId of this.bestMove) {
            if (this.players[playerId].role === 1 || this.players[playerId].role === 2)
                count++;
        }
        return count;
    }
    actionFixCourtroom() {
        const message = 'Ведучій помилився із виставленими гравцями...\nВиправляємо!';
        this.stageDescr = `День №${this.daysCount}.\n${message}`;
        this.addLog(message);
    }
    rebuildCourtroom() {
        const speakers = this.getSpeakers();
        const courtroom = [];
        speakers.forEach(playerId => {
            if (this.players[playerId].puted[this.daysCount] < 0) return false;
            courtroom.push(this.players[playerId].puted[this.daysCount]);
        })
        this.courtRoom = courtroom;
        this.openCourtroom();
    }
    courtList(list) {
        let courtList = '';
        list.forEach(defendant => courtList += `${defendant + 1}, `);
        courtList = courtList.slice(0, -2);
        return courtList;
    }
    shootingNight() {
        this.activeSpeaker = null;
        const message = 'Мафія підіймає свою зброю та стріляє по гравцям.\nЗробіть Ваш вибір!'
        this.stageDescr = `Ніч №${this.daysCount}.\n${message}`;
        this.addLog(message);
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
    checkFirstKill() {
        let check = this.killed.reduce((killedCount, killedAtDay) => killedCount + killedAtDay.length, 0);
        return check === 1;
    }
    addLog(message, show = false) {
        let logEntity = {};
        logEntity[this.logKey] = message;
        this.log = logEntity;

        if (show) {
            alert(message);
        }
    };
    finish() {
        this.assignPoints();
        this.resetView();
        alert(this.stageDescr.replace(/BR/g, '\n'));
    }
    assignPoints() {
        let red = this.getActivePlayersCount(1),
            mafs = this.getActivePlayersCount(2),
            bestMove = this.compareBestMove();

        if (red > 3) red = 3;

        this.players.forEach(player => {
            if (player.out === 4) {
                player.points += this.config.points.dis;
                return true;
            }
            if (this.winners == 1 && (player.role == 0 || player.role == 4)) {
                player.points += this.config.points.winner + this.config.points.aliveReds[red];
                if (player.bestMoveAuthor && bestMove > 0) {
                    player.points += this.config.points.bestMove[bestMove];
                }
            }
            else if (this.winners == 2 && (player.role == 1 || player.role == 2)) {
                player.points += this.config.points.winner + this.config.points.aliveMafs[mafs];
                if (player.role === 2 && this.players[this.killed[0]].role === 4) {
                    player.points += this.config.points.sherifFirstKill;
                }
            }
        })
    }
    theEnd(winner) {
        let message = '',
            red = this.getActivePlayersCount(1),
            mafs = this.getActivePlayersCount(2);
        if (mafs === 0 || winner === 1) {
            this.winners = 1;
            message = 'Мирне місто!\nВідтепер Ваші діти можуть спати спокійно!';
        }
        else if (mafs >= red || winner === 2) {
            this.winners = 2;
            message = "Мафію!\nВідтепер Ваші діти можуть спати сито й спокійно!";
        }
        if (this.winners) {
            this.stageDescr = `Вітаємо з перемогою: ${message}`;
            this.stage = 'finish';
            return true;
        }
        return false;
    }
}