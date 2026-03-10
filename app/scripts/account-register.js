actionHandler.accountRegisterFormSubmit = async function (event, formData, modal) {
    event.preventDefault();
    const verification = await this.verification(event.target, 'verification/register/name');

    if (!formData) formData = new FormData(event.target);

    if (verification) {
        formData.append('code', verification);
    }
    await this.request({
        url: 'account/register',
        data: formData,
    });
    modal.unpause();
}