class CustomImagesPad extends Prompt {
    checkboxes = [];
    images = [];
    addNewInput = null;
    nextPageButton = null;
    mouseOver = null;
    infoTile = null;

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
        this.images = data['files'];
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
        this.dialog.after(this.createInfoTile());
        return this;
    }
    showImagesPad() {
        const y = this.checkboxes.length;
        for (let x = y; x < this.images.length; x++) {
            const checkboxWrapper = document.createElement('label');
            checkboxWrapper.classList.add('images__item');
            this.checkboxes[x] = document.createElement('input');
            this.checkboxes[x].type = 'checkbox';
            this.checkboxes[x].id = `checkbox[${x}]`;
            this.checkboxes[x].value = x;
            checkboxWrapper.htmlFor = this.checkboxes[x].id;

            const img = document.createElement('img');
            img.src = this.images[x].thumbnailLink;
            img.classList.add('images__image');
            checkboxWrapper.append(this.checkboxes[x]);
            checkboxWrapper.append(img);
            this.images[x].node = checkboxWrapper;
            this.nextPageButton.before(checkboxWrapper);
        }
    }
    modifyEventsImagesPad() {
        const s = this;
        this.addNewInput.addEventListener('change', (e) => s.addNewImage.call(s, e));
        this.nextPageButton.addEventListener('click', (e) => s.getMoreImages.call(s, e));
        for (const image of this.images) {
            image.node.addEventListener('mouseenter', (e) => s.mouseEnterEvent.call(s, e));
            image.node.addEventListener('mouseleave', (e) => s.mouseLeaveEvent.call(s));
            image.node.addEventListener('mousemove', (e) => s.mouseMoveEvent.call(s, e));
        }
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
    createInfoTile() {
        this.infoTile = document.createElement('div');
        this.infoTile.classList.add('image__info', 'info', 'hidden');
        this.infoTile.rows = [];

        for (let x = 0; x < 3; x++) {
            const row = document.createElement('div');
            row.classList.add('info__row');
            this.infoTile.append(row);
            this.infoTile.rows.push(row);
        }
        return this.infoTile;
    }
    mouseEnterEvent(e) {
        this.mouseOver = e.target;
        this.infoTile.classList.remove('hidden');
        this.infoTile.rows[0].innerText = this.images[this.mouseOver.htmlFor.slice(-2, -1)].name;
        this.infoTile.rows[1].innerText = this.images[this.mouseOver.htmlFor.slice(-2, -1)].size + ' Кб';
        this.infoTile.rows[2].innerText = this.images[this.mouseOver.htmlFor.slice(-2, -1)].resol;
    }
    mouseLeaveEvent() {
        this.mouseOver = null;
        this.infoTile.classList.add('hidden');
    }
    mouseMoveEvent(e) {
        if (!this.mouseOver) return false;
        this.infoTile.style.left = (e.clientX + 20) + 'px';
        this.infoTile.style.top = (e.clientY + 20) + 'px';
    }
    async addNewImage(event) {
        const self = this;
        const file = event.target.files[0];

        const checkboxWrapper = document.createElement('span');
        checkboxWrapper.classList.add('images__item');
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.id = `checkbox[${this.checkboxes.length}]`;
        checkbox.value = URL.createObjectURL(file);
        checkbox.disabled = true;

        const img = document.createElement('img');
        img.src = checkbox.value;
        img.classList.add('images__image');

        checkboxWrapper.append(checkbox);
        checkboxWrapper.append(img);
        this.checkboxes.push(checkbox);

        this.addNewInput.closest('.images__form').after(checkboxWrapper);
        checkbox.addEventListener('change', () => self.updateInput.call(self, checkbox))

        const reader = new FileReader();
        reader.readAsDataURL(file);

        reader.onloadend = async function () {
            const formData = new FormData();
            formData.append('filename', file.name);
            formData.append('image', reader.result);
            formData.append('prompt', 1);
            const result = await request({ url: self.urlAdd, data: formData });
            checkbox.value = result.realLink;
            img.src = result.realLink;
            checkbox.disabled = false;
        }
    }

    async getMoreImages() {

        if (!this.nextPageToken) return false;

        const formData = new FormData();
        formData.append('pageToken', this.nextPageToken);
        const data = await request({ url: this.urlGet, data: formData });

        this.nextPageToken = data.nextPageToken;
        this.images = this.images.concat(data.files);
        this.showImagesPad();

        if (!this.nextPageToken)
            this.nextPageButton.classList.add('hidden');

        return true;
    }
    submit() {
        const result = [];
        for (const checkbox of this.checkboxes) {
            if (!checkbox.checked) continue;
            result.push(this.images[checkbox.value]);
        }
        this.input.value = JSON.stringify(result);
        super.submit();
    }
}

async function imagesPad(options = {}) {

    options.data = await request({ url: options.urlGet });
    const promise = new Promise((r) => {
        options.action = (v) => r(v);
        new CustomImagesPad(options);
    })
    return await promise.then();
}