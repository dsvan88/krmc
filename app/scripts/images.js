const enumBgImages = [];

actionHandler.imageAdd = async function (event) {
    const self = this;
    const target = event.target.closest('form');
    const formData = new FormData();
    const readers = [];

    for (let x = 0; x < event.target.files.length; x++) {
        readers.push(new FileReader());
        readers[x].onloadend = async function (e) {
            formData.append('filename[]', event.target.files[x].name);
            formData.append('type', event.target.dataset.type);
            formData.append('image[]', readers[x].result);
            for (let y = 0; y < event.target.files.length; y++) {
                if (!readers[y] || readers[y].readyState !== FileReader.DONE) return true;
            }
            const result = await self.apiTalk(event.target, event, 'actionChange', formData);
            target.insertAdjacentHTML('afterend', result.html);
            self.addChangeListeners();
        }
        readers[x].readAsDataURL(event.target.files[x]);
    }
}
actionHandler.imageBackgroundGroup = async function (target, event) {
    if (enumBgImages.length === 0) {
        return new Alert({ title: 'Empty list', text: 'List of files is empty.' });
    }
    const formData = new FormData();
    formData.append('file_ids', JSON.stringify(enumBgImages));
    return await this.apiTalk(target, event, 'actionClick', formData);
}
actionHandler.imageDeleteGroup = async function (target, event) {
    if (enumBgImages.length === 0) {
        return new Alert({ title: 'Empty list', text: 'List of files is empty.' });
    }
    const formData = new FormData();
    formData.append('file_ids', JSON.stringify(enumBgImages));
    return await this.apiTalk(target, event, 'actionClick', formData);
}
actionHandler.imageToogle = function (event) {
    const value = event.target.value;
    const index = enumBgImages.indexOf(value);
    if (index === -1) {
        enumBgImages.push(value)
    }
    else
        enumBgImages.splice(index, 1);
    return true;
}
actionHandler.imagesGetMore = async function (target, event) {
    const formData = new FormData();
    formData.append('pageToken', target.dataset.pageToken);
    formData.append('type', target.dataset.type);
    const result = await this.apiTalk(target, event, 'actionClick', formData);
    target.insertAdjacentHTML('beforebegin', result.html);
    target.dataset.pageToken = result.nextPageToken ? result.nextPageToken : '';
    if (!result.nextPageToken) {
        target.classList.add('hidden');
    }
    this.addChangeListeners();
}
actionHandler.addChangeListeners = function () {
    const self = this;
    const inputs = document.querySelectorAll('input[data-action-change]');
    for (const i of inputs) {
        if (i.changeListener) continue;

        i.addEventListener('change', (e) => self.changeCommonHandler.call(self, e));
        i.changeListener = true;
    }
}
actionHandler.showImageInfo = function (event) {
    info_value_name.innerText = event.target.dataset.name;
    info_value_bytes.innerText = Math.ceil(event.target.dataset.size / 1024) + ' Kb';
    info_value_resol.innerText = event.target.dataset.resol;
}
actionHandler.getLink = function (target) {
    try {
        navigator.clipboard.writeText(target.dataset.link);
        if (confirm('Скопійовано до буферу обміну.\nБажаєте відкрити в новому вікні?'))
            return window.open(target.dataset.link, '_blank');
    }
    catch (error) {
        if (confirm(`Не вдалось скопіювати до будеру обміну.\nПерейти за посиланням у новому вікні?`)) {
            return window.open(target.dataset.link, '_blank');
        }
        return new Alert({ title: "Your link", text: `Your link to this image is:<br><a href="${target.dataset.link}" target="_blank">${target.dataset.link}</a>` });
    }
}
actionHandler.imageShow = function (t) {
    const alert = alertImage({ title: t.title, image: t.src })
    alert.dialog.style.marginTop = '1vh';
}
actionHandler.imageGetLink = function (target) {

    const radio = document.querySelector('.image__radio:checked');

    if (!radio) return false;

    try {
        navigator.clipboard.writeText(radio.dataset.link);
        if (confirm('Скопійовано до буферу обміну.\nБажаєте відркити в новому вікні?'))
            return window.open(radio.dataset.link, '_blank');
    }
    catch (error) {
        if (confirm(`Не вдалось скопіювати до буферу обміну.\nПерейти за посиланням у новому вікні?`)) {
            return window.open(radio.dataset.link, '_blank');
        }
        return new Alert({ title: "Your link", text: `Your link to this image is:<br><a href="${radio.dataset.link}" target="_blank">${radio.dataset.link}</a>` });
    }
}