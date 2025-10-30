class Alert {
    dialog = null;
    form = null;
    title = null;
    text = null;
    buttonWrapper = null;
    agreeButton = null;
    dragged = false;
    keyDown = false;

    constructor({ title = "Alert", text = "There is no information, yet!", close = null } = {}) {

        this.dialog = this.build();
        this.fill({ title, text });
        this.dialog.show();
        this.customClose = close;

        this.attachEvents();
        return this;
    }

    build() {

        this.overlay = document.createElement('div');
        this.overlay.classList.add('popup__overlay');

        this.dialog = document.createElement('dialog');
        this.dialog.classList.add('popup');
        this.dialog.draggable

        this.form = document.createElement('form');
        this.form.method = 'dialog';

        this.title = document.createElement('h4');
        this.title.classList.add('popup__title');
        this.form.append(this.title);

        this.text = document.createElement('p');
        this.text.classList.add('popup__text');
        this.form.append(this.text);

        this.buttonWrapper = document.createElement('div');
        this.buttonWrapper.classList.add('popup__button-wrapper');

        this.agreeButton = document.createElement('button');
        this.agreeButton.innerText = 'Ok';
        this.agreeButton.classList.add('popup__button', 'positive');
        this.buttonWrapper.append(this.agreeButton);
        this.form.append(this.buttonWrapper);

        this.dialog.append(this.form);
        this.overlay.append(this.dialog);

        document.body.append(this.overlay);
        this.dialog.tabIndex = -1;
        return this.dialog;
    }
    fill({ title = "PopUp", text = "There is no information, yet!" }) {
        this.title.innerText = title;
        if (/\<[a-s]/.test(text))
            this.text.innerHTML = '<p>' + text.replace(/\n/g, '</p><p>') + '</p>';
        else
            this.text.innerText = text;
    }
    close() {
        this.dialog.close();
        this.dialog.remove();
        this.overlay.remove();
    }
    attachEvents() {
        const self = this;

        self.dialog.addEventListener('keydown', (event) => self.keyDownHandler.call(self, event));
        self.dialog.addEventListener('keyup', (event) => self.keyUpHandler.call(self, event));

        self.dialog.addEventListener('close', () => self.close.call(self));

        if (this.customClose)
            self.dialog.addEventListener('close', () => self.customClose.call(self));

        self.dialog.ondragstart = () => false;

        self.title.addEventListener('mousedown', (event) => self.dragStart.call(self, event));

        self.dialog.addEventListener('touchstart', (event) => self.dragStart.call(self, event));
    }
    keyDownHandler(event) {
        if (event.isComposing) return false;
        if (event.keyCode === 13 || event.keyCode === 27) this.keyDown = true;
    }
    keyUpHandler(event) {
        if (event.isComposing || !this.keyDown) return false;
        if (event.keyCode === 13 || event.keyCode === 27) this.agreeButton.click();
    }
    dragStart(event) {
        if (this.dragged) return;
        this.dragged = true;

        const clientX = event.clientX || event.targetTouches[0].clientX;
        const clientY = event.clientY || event.targetTouches[0].clientY;

        this.shiftX = clientX - this.dialog.getBoundingClientRect().left;
        this.shiftY = clientY - this.dialog.getBoundingClientRect().top;

        this.dragnDrop(event);
    }
    dragnDrop(event) {

        const self = this;

        self.dialog.style.position = 'absolute';
        self.dialog.style.zIndex = 1000;
        self.dialog.style.margin = 0;

        document.body.append(self.dialog);

        const pageX = event.pageX || event.targetTouches[0].pageX;
        const pageY = event.pageY || event.targetTouches[0].pageY;

        self.moveAt(pageX, pageY);

        document.context = self;
        document.addEventListener('mousemove', self.onMouseMove);
        document.addEventListener('touchmove', self.onMouseMove);

        self.dialog.onmouseup = (event) => this.moveEnd(event, 'mousemove');
        self.dialog.ontouchend = (event) => this.moveEnd(event, 'touchmove');

    }
    moveEnd(event, eventName) {
        document.removeEventListener(eventName, this.onMouseMove);
        this.dragged = false;
        this.dialog.ontouchend = null;
        document.context = null;
    }
    moveAt(pageX, pageY) {
        this.dialog.style.left = pageX - this.shiftX + 'px';
        this.dialog.style.top = pageY - this.shiftY + 'px';
    }
    onMouseMove(event) {
        const self = this.context;

        if (!self) return false;

        const pageX = event.pageX || event.targetTouches[0].pageX;
        const pageY = event.pageY || event.targetTouches[0].pageY;

        self.moveAt(pageX, pageY);
    }
}

class Confirm extends Alert {
    cancelButton = null;
    state = true;
    constructor({ title = "Confirmation", text = "Are you sure?", action = null, cancel = null } = {}) {

        super({ title, text });

        this.action = action || ((data) => console.log('Here is no action for this data: ' + data));
        this.cancel = cancel;

        this.modifyForm().modifyEvents();
        return this;
    }
    modifyForm() {

        this.agreeButton.innerText = 'Yes';

        this.cancelButton = document.createElement('button');
        this.cancelButton.classList.add('popup__button', 'negative');
        this.cancelButton.value = "cancel";
        this.cancelButton.innerText = "No";
        this.buttonWrapper.append(this.cancelButton);

        return this;
    }
    modifyEvents() {
        const self = this;
        self.form.addEventListener('submit', (event) => self.submit.call(self, event), { once: true });

        self.cancelButton.addEventListener('click', () => self.state = false);
    }
    submit() {
        if (this.state)
            return this.action(true);
        else
            return this.cancel ? this.cancel(false) : this.action(false);
    }
    keyUpHandler(event) {
        if (event.isComposing || !this.keyDown) return false;
        if (event.keyCode === 13) return this.agreeButton.click();
        else if (event.keyCode === 27) return this.cancelButton.click();
    }
}


class Prompt extends Confirm {
    input = null;
    state = true;
    inputWrapper = null;

    constructor({ title = "Prompt", text = "Enter value:", value = "No", action = null, cancel = null, input = { type: 'text' } } = {}) {
        super({ title, text, action, cancel });
        this.modifyPrompt({ value, input });
        return this;
    }
    modifyPrompt({ value = "No", input = { type: 'text' } } = {}) {
        this.agreeButton.innerText = 'Ok';
        this.cancelButton.innerText = 'Cancel';

        this.inputWrapper = document.createElement('div');
        this.inputWrapper.classList.add('popup__input-wrapper');

        this.input = document.createElement('input');
        this.input.classList.add('popup__input');
        this.input.value = value;

        for (const attr in input) {
            this.input[attr] = input[attr];
        }

        this.inputWrapper.append(this.input);

        this.buttonWrapper.before(this.inputWrapper);
        this.input.focus();
        return this;
    }
    submit() {
        if (this.state)
            return this.action(this.input.value);
        else
            return this.cancel ? cancel(this.input.value) : this.action(false);
    }
}