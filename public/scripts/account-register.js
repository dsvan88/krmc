actionHandler.accountRegisterFormSubmit = async function (event, modal, args) {
    const self = this;
    event.preventDefault();
    const verification = await self.verification(event.target, 'verification/register/name');

    const formData = new FormData(event.target);
    if (verification){
        formData.append('code', verification);
    }
    request({
        url: 'account/register',
        data: formData,
        success: (result) => self.commonResponse.call(self, result),
    });
}