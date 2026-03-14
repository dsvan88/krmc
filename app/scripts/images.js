const enumBgImages = [];

actionHandler.imageAdd = async function (event) {
    const target = event.target.closest('form');
    const formData = new FormData();
    const readers = [];

    for (let x = 0; x < event.target.files.length; x++) {
        readers.push(new FileReader());
        readers[x].onloadend = async () => {
            formData.append('filename[]', event.target.files[x].name);
            formData.append('type', event.target.dataset.type);
            formData.append('image[]', readers[x].result);
            for (let y = 0; y < event.target.files.length; y++) {
                if (!readers[y] || readers[y].readyState !== FileReader.DONE) return true;
            }
            const result = await this.apiTalk(event.target, 'actionChange', formData);
            target.insertAdjacentHTML('afterend', result.html);
            this.addChangeListeners();
        }
        readers[x].readAsDataURL(event.target.files[x]);
    }
}
actionHandler.imageBackgroundGroup = async function (target) {
    if (enumBgImages.length === 0) {
        return new Alert({ title: __('Empty list'), text: __('List of files is empty.') });
    }
    const formData = new FormData();
    formData.append('file_ids', JSON.stringify(enumBgImages));
    return await this.apiTalk(target, 'actionClick', formData);
}
actionHandler.imageDeleteGroup = async function (target) {
    if (enumBgImages.length === 0) {
        return new Alert({ title: __('Empty list'), text: __('List of files is empty.') });
    }
    const formData = new FormData();
    formData.append('file_ids', JSON.stringify(enumBgImages));
    return await this.apiTalk(target, 'actionClick', formData);
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
actionHandler.imagesGetMore = async function (target) {
    const result = await this.apiTalk(target, 'actionClick');
    target.insertAdjacentHTML('beforebegin', result.html);
    target.dataset.pageToken = result.nextPageToken ? result.nextPageToken : '';
    if (!result.nextPageToken) {
        target.classList.add('hidden');
    }
    this.addChangeListeners();
}
actionHandler.addChangeListeners = function () {
    const inputs = document.querySelectorAll('input[data-action-change]');
    for (const i of inputs) {
        if (i.changeListener) continue;

        i.addEventListener('change', this.changeCommonHandler.bind(this));
        i.changeListener = true;
    }
}
actionHandler.showImageInfo = function (event) {
    info_value_name.innerText = event.target.dataset.name;
    info_value_bytes.innerText = event.target.dataset.size + ' Kb';
    info_value_resol.innerText = event.target.dataset.resol;
}
actionHandler.showFolderInfo = function (event) {
    info_value_name.innerText = event.target.dataset.name;
    info_value_bytes.innerText = 'Folder';
    info_value_resol.innerText = '-';
}
actionHandler.openImagesFolder = function (target) {
    return window.location = '/images/' + target.dataset.name;
}
actionHandler.getLink = function (target) {
    try {
        navigator.clipboard.writeText(target.dataset.link);
        if (confirm(__(`Copied to the buffer.\nAre you wanna to open it in new window`)))
            return window.open(target.dataset.link, '_blank');
    }
    catch (error) {
        if (confirm(__(`Could not copy to clipboard.\nClick link in new window?`))) {
            return window.open(target.dataset.link, '_blank');
        }
        return new Alert({ title: __("Your link"), text: __spf(`Your link to this image is:<br><a href="%s" target="_blank">%s</a>`, [target.dataset.link, __('Link')]) });
    }
}
actionHandler.imageShow = function (t) {
    const alert = alertImage({ title: t.title, image: t.src });
    alert.dialog.style.marginTop = '1vh';
}
actionHandler.imageGetLink = function (target) {

    const radio = document.querySelector('.image__radio:checked');

    if (!radio) return false;

    try {
        navigator.clipboard.writeText(radio.dataset.link);
        if (confirm(__(`Copied to the buffer.\nAre you wanna to open it in new window`)))
            return window.open(radio.dataset.link, '_blank');
    }
    catch (error) {
        if (confirm(__(`Could not copy to clipboard.\nClick link in new window?`))) {
            return window.open(radio.dataset.link, '_blank');
        }
        return new Alert({  title: __("Your link"), text: __spf(`Your link to this image is:<br><a href="%s" target="_blank">%s</a>`, [target.dataset.link, __('Link')]) });
    }
}