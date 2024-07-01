actionHandler.telegramAuth = async function (string) {
    try {
        const formData = new FormData();
        formData.append('data', string)
        const result = await request({
            url: 'verification/telegram/hmac',
            data: formData,
        });

        if (result['notice'])
            return this.noticer.add(result['notice']);

        window.location = result["location"];

    } catch (error) {
        this.noticer.add({ type: 'error', message: error.message });
    }
}

actionHandler.telegramAuth(window.Telegram.WebApp.initData);
