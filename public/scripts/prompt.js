class Prompt {
    dialog = null;
    form = null;
    title = null;
    text = null;
    input = null;
    agreeButton = null;
    cancelButton = null;
    state = true;
    dragged = false;

    constructor({ title = "Prompt", text = "Need help?", value = "No", action = null, cancel = null, input = { type: 'text' } } = {}) {
        let dialogExample = document.querySelector('dialog.prompt');
        if (!dialogExample) {
            dialogExample = this.build();
        }

        this.dialog = dialogExample.cloneNode(true);
        document.body.append(this.dialog);
        this.fill({ title, text, value, input });
        this.dialog.show();

        this.form.addEventListener('submit', (event) => {
            if (this.state)
                return action(this.input.value);
            else
                return cancel ? cancel(this.input.value) : action(false);
        });

        this.cancelButton.addEventListener('click', (event) => {
            this.state = false;
        });

        this.dialog.addEventListener('keyup', (event) => {
            if (event.isComposing || event.keyCode !== 27)
                return false;
            this.dialog.close();
            return cancel ? cancel(this.input.value) : action(false);
        });

        this.dialog.addEventListener('close', (event) => {
            this.dialog.remove();
        });

        this.dialog.ondragstart = function () {
            return false;
        };

        this.title.addEventListener('mousedown', (event) => this.dragDialogStart.call(this, event));

        this.dialog.addEventListener('touchstart', (event) => this.dragDialogStart.call(this, event));
    }

    build() {
        const dialog = document.createElement('dialog');
        dialog.classList.add('prompt');
        dialog.draggable

        const form = document.createElement('form');
        form.method = 'dialog';

        const title = document.createElement('h4');
        title.classList.add('prompt__title');
        form.append(title);

        const text = document.createElement('p');
        text.classList.add('prompt__text');
        form.append(text);

        const inputWrapper = document.createElement('div');
        inputWrapper.classList.add('prompt__input-wrapper');

        const input = document.createElement('input');
        input.classList.add('prompt__input');
        inputWrapper.append(input);
        form.append(inputWrapper);

        const buttonWrapper = document.createElement('div');
        buttonWrapper.classList.add('prompt__button-wrapper');

        const agreeButton = document.createElement('button');
        agreeButton.innerText = 'Agree';
        agreeButton.classList.add('prompt__button', 'agree');
        buttonWrapper.append(agreeButton);

        const cancelButton = document.createElement('button');
        cancelButton.value = "cancel";
        cancelButton.classList.add('prompt__button', 'cancel');
        buttonWrapper.append(cancelButton);
        form.append(buttonWrapper);

        dialog.append(form);
        document.body.append(dialog);
        return dialog;
    }
    fill({ title = "Prompt", text = "Need help?", value = "No", input = { type: "text" } }) {
        this.form = this.dialog.querySelector('form');

        this.title = this.dialog.querySelector('h4.prompt__title');
        this.title.innerText = title;

        this.text = this.dialog.querySelector('p.prompt__text');
        this.text.innerText = text;

        this.input = this.dialog.querySelector('input.prompt__input');
        this.input.value = value;

        for (const attr in input) {
            this.input[attr] = input[attr];
        }

        this.agreeButton = this.dialog.querySelector('button.prompt__button.agree');
        this.agreeButton.innerText = 'Ok';

        this.cancelButton = this.dialog.querySelector('button.prompt__button.cancel');
        this.cancelButton.innerText = 'Cancel';
    }

    close(){
        this.dialog.close();
    }
    dragDialogStart(event){
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
        
        let pageX = event.pageX || event.targetTouches[0].pageX;
        let pageY = event.pageY || event.targetTouches[0].pageY;

        self.moveAt(pageX, pageY);

        document.context = self;
        document.addEventListener('mousemove', self.onMouseMove);
        document.addEventListener('touchmove', self.onMouseMove);

        self.dialog.onmouseup = (event) => this.moveEnd(event, 'mousemove');
        self.dialog.ontouchend = (event) => this.moveEnd(event, 'touchmove');

    }
    moveEnd(event, eventName){
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