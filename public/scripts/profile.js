actionHandler.accountProfileSection = function (target, event) {
    if (target.classList.contains('active'))
        return false;
    let activeItem = target.closest('.profile__sections').querySelector('.active');
    activeItem.classList.remove('active');
    target.classList.add('active');
    
    this.changeCardContent(target.dataset.actionClick, target);
}
actionHandler.accountProfileSectionEdit = function (target, event) {
    let parent = target.closest('*[data-section]');
    if (!parent || !parent.classList.contains('active'))
        return false;
    
    this.changeCardContent(target.dataset.actionClick, parent);
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
actionHandler.changeCardContent = function (url, target){
    let data = new FormData();
    data.append('uid', target.dataset.uid);
    data.append('section', target.dataset.section);
    let cardContent = document.querySelector('.profile__card-content');
    request({
        url: url,
        data: data,
        success: function ({html, error, message}) {
            if (error) {
                alert(message);
                return false;
            }
            if (message) {
                alert(message);
            }
            cardContent.innerHTML = html;
            let form = cardContent.querySelector('form');
            if (form)
                form.addEventListener('submit', (event) => actionHandler.commonSubmitFormHandler.call(actionHandler, event));
        },
    });
}