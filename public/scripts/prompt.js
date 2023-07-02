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

    constructor({title = "Prompt", text="Need help?", value="No", action=null, cancel=null, input={type:'text'}}={}){
        let dialogExample = document.querySelector('dialog.prompt');
        if (!dialogExample){
            dialogExample = this.build();
        }

        this.dialog = dialogExample.cloneNode(true);
        document.body.append(this.dialog);
        this.fill({title, text, value, input});
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

        this.dialog.ondragstart = function() {
            return false;
        };

        this.title.addEventListener('mousedown', (event)=> {
            if (this.dragged) return;
            this.dragged = true;
            this.dragnDrop.call(this, event);
        })
    }

    build(){
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
    fill({title = "Prompt", text="Need help?", value="No", input = {type:"text"}}){
        this.form = this.dialog.querySelector('form');

        this.title = this.dialog.querySelector('h4.prompt__title');
        this.title.innerText = title;

        this.text = this.dialog.querySelector('p.prompt__text');
        this.text.innerText = text;
        
        this.input = this.dialog.querySelector('input.prompt__input');
        this.input.value = value;

        for (const attr in input){
            this.input[attr] = input[attr];
        }

        this.agreeButton = this.dialog.querySelector('button.prompt__button.agree');
        this.agreeButton.innerText = 'Ok';
        
        this.cancelButton = this.dialog.querySelector('button.prompt__button.cancel');
        this.cancelButton.innerText = 'Cancel';
    }

    dragnDrop(event){
        
        const self = this;

        let shiftX = event.clientX - self.dialog.getBoundingClientRect().left;
        let shiftY = event.clientY - self.dialog.getBoundingClientRect().top;

        self.dialog.style.position = 'absolute';
        self.dialog.style.zIndex = 1000;
        self.dialog.style.margin = 0;
        
        document.body.append(self.dialog);

        moveAt(event.pageX, event.pageY);

        function moveAt(pageX, pageY) {
            self.dialog.style.left = pageX - shiftX + 'px';
            self.dialog.style.top = pageY - shiftY + 'px';
        }

        function onMouseMove(event) {
            moveAt(event.pageX, event.pageY);
        }

        document.addEventListener('mousemove', onMouseMove);

        self.dialog.onmouseup = function() {
            document.removeEventListener('mousemove', onMouseMove);
            self.dialog.onmouseup = null;
            self.dragged = false;
        };
    }
}