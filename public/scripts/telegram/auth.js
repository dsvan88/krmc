actionHandler.telegramAuth = async function () {
    const formData = new FormData();
    formData.append('data', window.Telegram.WebApp.initData)
    result = await request({
        url: 'verification/telegram/hmac',
        data: formData,
    });

    if (!result['result'])
        return this.noticer.add({ type: 'error', message: 'Auth error!' });

    this.noticer.add({ message: 'Auth complete!' });
}
