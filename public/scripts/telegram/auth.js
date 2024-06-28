async function telegramAuth(string) {
    try {
        const formData = new FormData();
        formData.append('data', string)
        result = await request({
            url: 'verification/telegram/hmac',
            data: formData,
        });

        if (!result['result'])
            return this.noticer.add({ type: 'error', message: 'Auth error!' });

        this.noticer.add({ message: 'Auth complete!' });
    } catch (error) {
        this.noticer.add({ type: 'error', message: error.message });
    }
}

telegramAuth(window.Telegram.WebApp.initData);