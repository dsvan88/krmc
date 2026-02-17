actionHandler.accountDelete = async function (target, event) {
    const verification = await this.verification(null, 'verification/root', {type:'password'});
    const formData = new FormData();
    formData.append('root', verification);
    const url = `${target.dataset['actionClick']}/${target.dataset['userId']}`;
    const result = await request({ url: url, data: formData });
    this.commonResponse(result);
}