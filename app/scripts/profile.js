actionHandler.accountProfileSection = async function (target) {
    if (target.classList.contains('active'))
        return false;
    const activeItem = target.closest('.profile__sections').querySelector('.active');
    activeItem.classList.remove('active');
    target.classList.add('active');

    const result = await this.apiTalk(target, null, 'actionClick');

    this.changeCardContent(result);
}
actionHandler.accountAvatarShow = function (target) {
    const image = target.querySelector('img');
    alertImage({ title: 'User’s avatar', image: image.src });
}
actionHandler.accountProfileSectionEdit = async function (target) {
    const parent = target.closest('*[data-section]');
    if (!parent || !parent.classList.contains('active'))
        return false;

    const data = getFormDataFromDataset(parent);

    const result = await this.apiTalk(target, null, 'actionClick', data);

    this.changeCardContent(result);
}
actionHandler.changeCardContent = async function (result = {}) {

    if (!result.html) return false;

    const self = this;
    const cardContent = document.querySelector('.profile__card-content');
    cardContent.innerHTML = result.html;
    const form = cardContent.querySelector('form');
    if (form)
        form.addEventListener('submit', (e) => self.commonSubmitFormHandler.call(self, e));

    return true;
}
actionHandler.verificationEmail = async function (target, event) {
    const self = this;
    event.preventDefault();
    const verification = await self.verification(event.target, target.dataset.actionClick);

    if (!verification) return false;

    const formData = new FormData();
    formData.append('approval_code', verification)

    const result = await this.request({
        url: target.dataset.actionClick,
        data: formData,
    });
}
actionHandler.accountPersonalEdit = async function (target) {
    let value = target.innerText;
    let type = target.dataset.type;
    let select = null;

    if (type === 'date') {
        const date = value.split('.');
        value = `${date[2]}-${date[1]}-${date[0]}`;
    }
    else if (type === 'tel') {
        value = value === 'No data' ? this.phoneMask : value;
    }
    else if (target.dataset.field === 'personal.gender') {
        select = {
            options: {
                '': '',
                'male': 'Пан',
                'female': 'Пані',
                'secret': 'Секрет',
            }
        }
    }
    const newValue = await customPrompt({ title: 'Введіть нове значення', value: value, input: { type: type }, select: select });

    if (newValue === false) return false;

    const formData = new FormData();
    formData.append('userId', target.dataset.userId);
    formData.append('field', target.dataset.field);
    formData.append('value', newValue);

    return await this.request({
        url: target.dataset.actionDblclick,
        data: formData,
    });

}