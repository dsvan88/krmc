actionHandler.accountDelete = async function (target) {
    const verification = await this.verification(null, 'verification/root', {type:'password'});
    const formData = new FormData();
    formData.append('root', verification);
    const url = `${target.dataset['actionClick']}/${target.dataset['userId']}`;
    return await this.request({ url: url, data: formData });
}