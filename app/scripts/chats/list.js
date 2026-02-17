actionHandler.accountSetNickname = async function (target, event) {
    const self = this;

    const nickname = await customPrompt({
        title: 'Set a new nickname',
        text: `For "${target.dataset.title}"`,
        value: target.dataset.value === ' - ' ? '' : target.dataset.value,
    });
    
    if (nickname === false) return false;

    const formData = new FormData();
    formData.append('cid', target.dataset.cid);
    formData.append('name', nickname);
    const result = await self.apiTalk(target, event, 'actionDblclick', formData);
   
    // console.log(formData)
}