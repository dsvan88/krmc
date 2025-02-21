
actionHandler.imageAdd = async function (event) {
    const self = this;
    const target = event.target.closest('form');
    const reader = new FileReader();
    const file = event.target.files[0];
    reader.readAsDataURL(file);
    console.log(target);

    reader.onloadend = async function () {
        const formData = new FormData();
        formData.append('filename', file.name);
        formData.append('image', reader.result);
        const result = await self.apiTalk(event.target, event, 'actionChange', formData);
        target.insertAdjacentHTML('beforestart', result.html);
    }
}