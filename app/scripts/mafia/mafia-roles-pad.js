class MafiaRolesPad extends Prompt {
    radios = {};
    values = [];
    allButton = null;
    pause = false;
    roles = ['peace', 'mafia', 'sherif', 'don'];
    constructor({ title = "Mafia Roles Numpad", text = "Choose player’s role:", value = '', action = null, cancel = null, roles = [], choosed='peace', pause = true } = {}) {
        value = '';
        super({ title, text, value, action, cancel })
        this.pause = pause;
        this.modifyForRolepad(roles, choosed).modifyEventsRolepad();
        this.dialog.focus();
    }

    modifyForRolepad(roles = [], choosed) {

        if (roles.length === 0) return this;

        this.input.classList.add('hidden');
        const inputWrapper = this.input.closest('.popup__input-wrapper');
        inputWrapper.classList.add('numpad');

        const rolepad = document.createElement('fieldset');
        rolepad.classList.add('popup__rolepad');
        const legend = document.createElement('legend');
        legend.innerText = 'Roles:';
        rolepad.append(legend);
        this.radios = {};
        for (let role of roles) {
            rolepad.append(this.createRoleRadio(role));
        }

        if (this.radios[choosed]){
            this.radios[choosed].checked = true;
        }

        inputWrapper.after(rolepad);
        return this;
    }
    modifyEventsRolepad() {
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

        self.radios[self.roles[num]].checked = !self.radios[self.roles[num]].checked;
        self.updateInput.call(self, self.radios[self.roles[num]]);
    }
    updateInput(radio) {
        this.input.value = radio.value;
    }
    createRoleRadio(role){
        const radioWrapper = document.createElement('span');
        radioWrapper.classList.add('popup__radio-wrapper');

        this.radios[role] = document.createElement('input');
        this.radios[role].name = 'role';
        this.radios[role].type = 'radio';
        this.radios[role].id = `radio[${role}]`;
        this.radios[role].value = role;
        this.radios[role].classList.add('popup__radio', 'hidden');

        radioWrapper.append(this.radios[role]);

        const label = document.createElement('label');
        label.classList.add('popup__radio-label');
        label.innerText = `${this.roles.indexOf(role)} - ${role}`;
        label.htmlFor = this.radios[role].id;

        radioWrapper.append(label);
        return radioWrapper;
    }
}