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


const tgDataField = document.querySelector('#userdata');
try {

    let tg = window.Telegram.WebApp;
    tg.expand();

    let tgData = tg.initDataUnsafe;

    tgDataField.innerHTML = JSON.stringify(tgData);

    tgDataField.innerHTML += '<br>Unsafe is done!';

    tgData = tg.initData;
    tgDataField.innerHTML += `<br>${tgData}`;

    tgDataField.innerHTML += '<br>Safe is done!';

} catch (error) {
    tgDataField.innerHTML += `
    <div>
        <h3>Error:</h3>
        <p>
            <span>${error.name}:</span>
            <p>${error.message}</p>
            <p>${error.stack}</p>
        </p>
    </div>`;
}