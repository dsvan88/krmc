async function telegramAuth(string) {
    try {
        const formData = new FormData();
        formData.append('data', string)
        result = await request({
            url: 'verification/telegram/hmac',
            data: formData,
        });

        if (!result['result'])
            return actionHandler.noticer.add({ type: 'error', message: 'Auth error!' });

        actionHandler.noticer.add({ message: 'Auth complete!' });
    } catch (error) {
        actionHandler.noticer.add({ type: 'error', message: error.message });
    }
}

actionHandler.noticer.add({ message: 'Loaded.' });

telegramAuth(window.Telegram.WebApp.initData);
