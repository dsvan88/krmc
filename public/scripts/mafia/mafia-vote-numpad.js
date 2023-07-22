class MafiaVoteNumpad extends Prompt {
    checkboxs = [];
    values = [];
    allButton = null;
    constructor({ title = "Mafia Vote Numpad", text = "Enter player’s numbers:", value='', action = null, cancel = null, block=[] } = {}) {
        value = '';
        super({ title, text, value, action, cancel })
        this.modifyForNumpad(block).modifyEventsNumpad();
        this.dialog.focus();
    }

    modifyForNumpad(block = []) {
        this.input.classList.add('hidden');
        const inputWrapper = this.input.closest('.popup__input-wrapper');
        inputWrapper.classList.add('numpad');

        const numpad = document.createElement('div');
        numpad.classList.add('popup__numpad');

        this.checkboxs = [];
        for (let x = 0; x < 10; x++) {
            let checkboxWrapper = document.createElement('span');
            checkboxWrapper.classList.add('popup__checkbox-wrapper');

            this.checkboxs[x] = document.createElement('input');
            this.checkboxs[x].type = 'checkbox';
            this.checkboxs[x].id = `checkbox[${x}]`;
            this.checkboxs[x].value = x;
            this.checkboxs[x].classList.add('popup__checkbox', 'hidden');
            if (block.includes(`${x}`)){
                this.checkboxs[x].disabled = true;
            }

            checkboxWrapper.append(this.checkboxs[x]);

            let label = document.createElement('label');
            label.classList.add('popup__checkbox-label');
            label.innerText = x + 1;
            label.htmlFor = this.checkboxs[x].id;

            checkboxWrapper.append(label);
            numpad.append(checkboxWrapper);
        }
        inputWrapper.after(numpad);

        const buttonWrapper = this.dialog.querySelector('.popup__button-wrapper');

        this.allButton = document.createElement('button');
        this.allButton.innerText = 'All';
        this.allButton.classList.add('popup__button', 'positive');
        buttonWrapper.append(this.allButton);
        return this;
    }
    modifyEventsNumpad() {
        const self = this;
        this.checkboxs.forEach(checkbox => checkbox.addEventListener('change', () => self.updateInput.call(self, checkbox)));
        this.allButton.addEventListener('click', (event) => self.sendAll.call(self, event));
    }
    keyUpHandler(event) {
        super.keyUpHandler(event);
        const self = this;
        let num = null;
        if (event.keyCode >= 48 && event.keyCode <= 57) {
            num = event.keyCode - 48;
        }
        else if (event.keyCode >= 96 && event.keyCode <= 105) {
            num = event.keyCode - 96;
        }
        if (num === null) return true;

        if (--num === -1) num = 9;

        if (self.checkboxs[num].disabled) return true;

        self.checkboxs[num].checked = !self.checkboxs[num].checked;
        self.updateInput.call(self, self.checkboxs[num]);
    }
    updateInput(checkbox) {
        const values = this.input.value ? this.input.value.split(',') : [];
        if (checkbox.checked) {
            values.push(checkbox.value);
        }
        else {
            const index = values.indexOf(checkbox.value);
            values.splice(index, 1);
        }
        values.sort((a, b) => a - b);
        this.input.value = values.join(',');
    }
    sendAll() {
        const values = [];
        for (const checkbox of this.checkboxs) {
            if (checkbox.disabled) continue;
            values.push(checkbox.value);
        }
        this.input.value = values.join(',');
    }
}