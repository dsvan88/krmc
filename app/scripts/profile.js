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
    request({
        url: target.dataset.actionClick,
        data: formData,
        success: (result) => self.commonResponse.call(self, result),
    });
}