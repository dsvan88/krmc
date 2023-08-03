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
        success: self.commonResponse,
    });
}

/* actionHandler.accountRegisterSubmit(event, modal, formData){
    request({
        url: 'account/register',
        data: formData,
        success: (result) => {
            if (result['wrong'] === 'code') {
                new Alert({ text: 'Wrong verification code!' });
            }
        },
    });
}
 */
/* success: (result) => {
    if (!result) return self.commonResponse.call(self, result);
    if (result['result'] === 'free') return self.commonSubmitFormHandler.call(self, event, modal, args);
    new Prompt({
        title: 'Verification',
        text: message,
        success: (result) => {
            formData.append('code', result);
            self.accountRegisterSubmit(event, modal, formData)
        },
    });
}, */