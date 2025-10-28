class ImagesPad extends Prompt {
    checkboxs = [];
    values = [];
    constructor({ title = "Images list", text = "Choose images:", value = '', action = null, cancel = null, images = [] } = {}) {
        value = '';
        super({ title, text, value, action, cancel })
        this.images = images;
        this.modifyForImagesPad().modifyEventsImagesPad();
        this.dialog.focus();
    }

    modifyForImagesPad() {
        // this.input.classList.add('hidden');
        const inputWrapper = this.input.closest('.popup__input-wrapper');

        inputWrapper.classList.add('images');

        const imagesPad = document.createElement('div');
        imagesPad.classList.add('images__list');

        this.checkboxs = [];

        for (let x = 0; x < this.images.length; x++) {
            const checkboxWrapper = document.createElement('span');
            checkboxWrapper.classList.add('images__item');
            this.checkboxs[x] = document.createElement('input');
            this.checkboxs[x].type = 'checkbox';
            this.checkboxs[x].id = `checkbox[${x}]`;
            this.checkboxs[x].value = this.images[x].thumbnailLink;

            const img = document.createElement('img');
            img.src = this.images[x].thumbnailLink;
            img.classList.add('images__image');
            checkboxWrapper.append(this.checkboxs[x]);
            checkboxWrapper.append(img);
            imagesPad.append(checkboxWrapper);
        }
        inputWrapper.append(imagesPad);
        // for (let x = 0; x < 10; x++) {
        //     

        //     this.checkboxs[x] = document.createElement('input');
        //     this.checkboxs[x].type = 'checkbox';
        //     this.checkboxs[x].id = `checkbox[${x}]`;
        //     this.checkboxs[x].value = x;
        //     this.checkboxs[x].classList.add('popup__checkbox', 'hidden');
        //     if (block.includes(`${x}`)) {
        //         this.checkboxs[x].disabled = true;
        //     }

        //     checkboxWrapper.append(this.checkboxs[x]);

        //     let label = document.createElement('label');
        //     label.classList.add('popup__checkbox-label');
        //     label.innerText = x + 1;
        //     label.htmlFor = this.checkboxs[x].id;

        //     checkboxWrapper.append(label);
        //     numpad.append(checkboxWrapper);
        // }
        // inputWrapper.after(numpad);

        // const buttonWrapper = this.dialog.querySelector('.popup__button-wrapper');

        // this.allButton = document.createElement('button');
        // this.allButton.innerText = 'All';
        // this.allButton.classList.add('popup__button', 'positive');
        // buttonWrapper.append(this.allButton);
        return this;
    }
    modifyEventsImagesPad() {
        const self = this;
        this.checkboxs.forEach(checkbox => checkbox.addEventListener('change', () => self.updateInput.call(self, checkbox)));
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
        this.input.value = values.join(',');
    }
}