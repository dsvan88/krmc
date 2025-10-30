class CustomImagesPad extends Prompt {
    checkboxs = [];
    values = [];
    addNewInput = null;
    nextPageButton = null;

    constructor({ 
        title = "Images list", 
        text = "Choose images:", 
        value = '', 
        action = null, 
        cancel = null, 
        data = {}, 
        urlGet = 'forms/images/list',
        urlAdd = 'image/add' } = {}
    ) {
        super({ title, text, value, action, cancel })

        this.nextPageToken = data['nextPageToken'];
        this.images = data['images'];
        this.urlGet = urlGet;
        this.urlAdd = urlAdd;
        this.modifyForImagesPad().modifyEventsImagesPad();
        this.dialog.focus();
    }

    modifyForImagesPad() {
        this.input.classList.add('hidden');
        const inputWrapper = this.input.closest('.popup__input-wrapper');

        inputWrapper.classList.add('images');

        const imagesPad = document.createElement('div');
        imagesPad.classList.add('images__list');

        imagesPad.append(this.getNewImageForm());
        imagesPad.append(this.getMoreImagesButton());

        this.showImagesPad();       

        inputWrapper.append(imagesPad);
        return this;
    }
    showImagesPad(){
        const y = this.checkboxs.length;
        for (let x = y; x < this.images.length; x++) {
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
            this.nextPageButton.before(checkboxWrapper);
        }
    }
    modifyEventsImagesPad() {
        const s = this;
        this.checkboxs.forEach(checkbox => checkbox.addEventListener('change', () => s.updateInput.call(s, checkbox)));
        this.addNewInput.addEventListener('change', (e) => s.addNewImage.call(s, e));
        this.nextPageButton.addEventListener('click', (e) => s.getMoreImages.call(s, e));
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
    getNewImageForm() {
        const newImageForm = document.createElement('form');
        newImageForm.classList.add('images__form', 'new');

        const input = document.createElement('input');
        input.type = 'file';
        input.id = 'new_image_' + Math.ceil(Math.random() * 100);
        input.accept = '.png,.jpg,.jpeg,.webp';
        this.addNewInput = input;

        const label = document.createElement('label');
        label.classList.add('label', 'fa', 'fa-plus-circle');
        label.htmlFor = input.id;

        newImageForm.append(label);
        newImageForm.append(input);
        return newImageForm;
    }
    getMoreImagesButton() {
        this.nextPageButton = document.createElement('span');
        this.nextPageButton.classList.add('get-more', 'fa', 'fa-refresh');
        
        if (!this.nextPageToken)
            this.nextPageButton.classList.add('hidden');

        return this.nextPageButton;
    }
    async addNewImage(event) {
        const self = this;
        const file = event.target.files[0];

        const checkboxWrapper = document.createElement('span');
        checkboxWrapper.classList.add('images__item');
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.id = `checkbox[${this.checkboxs.length}]`;
        checkbox.value = URL.createObjectURL(file);
        checkbox.disabled = true;

        const img = document.createElement('img');
        img.src = checkbox.value;
        img.classList.add('images__image');

        checkboxWrapper.append(checkbox);
        checkboxWrapper.append(img);
        this.checkboxs.push(checkbox);

        this.addNewInput.closest('.images__form').after(checkboxWrapper);
        checkbox.addEventListener('change', () => self.updateInput.call(self, checkbox))

        const reader = new FileReader();
        reader.readAsDataURL(file);

        reader.onloadend = async function () {
            const formData = new FormData();
            formData.append('filename', file.name);
            formData.append('image', reader.result);
            formData.append('prompt', 1);
            const result = await request({url: self.urlAdd, data: formData});
            checkbox.value = result.realLink;
            img.src = result.realLink;
            checkbox.disabled = false;
        }
    }

    async getMoreImages() {

        if (!this.nextPageToken) return false;

        const formData = new FormData();
        formData.append('pageToken', this.nextPageToken);
        const data = await request({url: this.urlGet, data: formData});
        
        this.nextPageToken = data.nextPageToken;
        this.images = this.images.concat(data.images);
        this.showImagesPad();

        if (!this.nextPageToken)
            this.nextPageButton.classList.add('hidden');

        return true;
    }
}

async function imagesPad(options = {}) {

	options.data = await request({url: options.urlGet});

    const promise = new Promise((r) => {
        options.action = (v) => r(v);
        new CustomImagesPad(options);
    })
    return await promise.then();
}