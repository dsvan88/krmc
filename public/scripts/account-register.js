actionHandler.accountRegisterFormSubmit = async function (event, formData, modal) {
    const self = this;
    event.preventDefault();
    const verification = await self.verification(event.target, 'verification/register/name');
    
    if (!formData) formData = new FormData(event.target);
    
    if (verification){
        formData.append('code', verification);
    }
    await request({
        url: 'account/register',
        data: formData,
        success: (result) => {
            self.commonResponse.call(self, result);
            modal.unpause();
        },
    });
}