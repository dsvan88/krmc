const enumBgImages = [];

actionHandler.imageAdd = async function (event) {
    const self = this;
    const target = event.target.closest('form');
    const reader = new FileReader();
    const file = event.target.files[0];
    reader.readAsDataURL(file);

    reader.onloadend = async function () {
        const formData = new FormData();
        formData.append('filename', file.name);
        formData.append('image', reader.result);
        const result = await self.apiTalk(event.target, event, 'actionChange', formData);
        target.insertAdjacentHTML('afterend', result.html);
    }
}
actionHandler.imageBackgroundGroup = async function (target, event) {
    if (enumBgImages.length === 0) {
        return new Alert({ title: 'Empty list', text: 'List of files is empty.' });
    }
    const formData = new FormData();
    formData.append('file_ids', JSON.stringify(enumBgImages));
    const result = await this.apiTalk(target, event, 'actionClick', formData);
}
actionHandler.imageDeleteGroup = async function (target, event) {
    if (enumBgImages.length === 0) {
        return new Alert({ title: 'Empty list', text: 'List of files is empty.' });
    }
    const formData = new FormData();
    formData.append('file_ids', JSON.stringify(enumBgImages));
    const result = await this.apiTalk(target, event, 'actionClick', formData);
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
    const result = await this.apiTalk(target, event, 'actionClick', formData);
    target.insertAdjacentHTML('beforebegin', result.html);
    target.dataset.pageToken = result.nextPageToken ? result.nextPageToken : '';
    if (!result.nextPageToken){
        target.classList.add('hidden');
    }
}

actionHandler.getLink = function (target) {
    try {
        navigator.clipboard.writeText(target.dataset.link);
        alert('Скопійовано до буферу обміну');
    }
    catch (error) {
        if (confirm(`Не вдалось скопіювати до будеру обміну.\nПерейти за посиланням у новому вікні?`)) {
            return window.open(target.dataset.link, '_blank');
        }
        return new Alert({ title: "Your link", text: `Your link to this image is:<br><a href="${target.dataset.link}" target="_blank">${target.dataset.link}</a>` });
    }
}