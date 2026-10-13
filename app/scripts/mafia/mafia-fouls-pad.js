class MafiaFoulsPad extends Prompt {
    radios = {};
    values = [];
    allButton = null;
    pause = false;
    fouls = ['+Foul', '-Foul', 'All four', 'disqual'];
    tFouls = ['+TechFoul', '-TechFoul'];
    constructor({ title = "Mafia Fouls Numpad", text = "Choose player’s foul:", value = '', action = null, cancel = null, pause = false } = {}) {
        value = '';
        super({ title, text, value, action, cancel })
        this.pause = pause;
        this.modifyForFoulsPad().modifyEventsFoulsPad();
        this.dialog.focus();
    }

    modifyForFoulsPad() {
        this.input.classList.add('hidden');
        const inputWrapper = this.input.closest('.popup__input-wrapper');
        inputWrapper.classList.add('numpad');

        const foulsPad = document.createElement('fieldset');
        foulsPad.classList.add('popup__foulpad');
        const foulsLegend = document.createElement('legend');
        foulsLegend.innerText = 'Fouls:';
        foulsPad.append(foulsLegend);
        this.radios = {};
        for (let foul of this.fouls) {
            foulsPad.append(this.createFoulRadio(foul));
        }
        
        const tFoulsPad = document.createElement('fieldset');
        tFoulsPad.classList.add('popup__foulpad');
        const tFoulsLegend = document.createElement('legend');
        tFoulsLegend.innerText = 'Tech Fouls:';
        tFoulsPad.append(tFoulsLegend);tFoulsLegend
        for (let foul of this.tFouls) {
            tFoulsPad.append(this.createFoulRadio(foul));
        }

        inputWrapper.after(tFoulsPad);
        inputWrapper.after(foulsPad);
        return this;
    }
    modifyEventsFoulsPad() {
        const self = this;
        for(let index in self.radios){
            self.radios[index].addEventListener('change', () => { self.updateInput.call(self, self.radios[index]) });
        }
    }
    keyUpHandler(event) {
        super.keyUpHandler(event);
        const self = this;
        let num = null;
        if (event.keyCode >= 48 && event.keyCode <= 51) {
            num = event.keyCode - 48;
        }
        else if (event.keyCode >= 96 && event.keyCode <= 99) {
            num = event.keyCode - 96;
        }
        if (num === null) return true;

        self.radios[self.fouls[num]].checked = !self.radios[self.fouls[num]].checked;
        self.updateInput.call(self, self.radios[self.fouls[num]]);
    }
    updateInput(radio) {
        this.input.value = radio.value;
    }
    createFoulRadio(foul){

        const radioWrapper = document.createElement('span');
        radioWrapper.classList.add('popup__radio-wrapper');

        this.radios[foul] = document.createElement('input');
        this.radios[foul].name = 'foul';
        this.radios[foul].type = 'radio';
        this.radios[foul].id = `radio[${foul}]`;
        this.radios[foul].value = foul;
        this.radios[foul].classList.add('popup__radio', 'hidden');
        
        radioWrapper.append(this.radios[foul]);
        
        const label = document.createElement('label');
        label.classList.add('popup__radio-label');
        label.innerText = foul;
        label.htmlFor = this.radios[foul].id;
        
        radioWrapper.append(label);
        return radioWrapper;
    }
}