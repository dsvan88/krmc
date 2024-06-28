let tg = window.Telegram.WebApp;
tg.expand();

const tgDataField = document.querySelector('#userdata');
try {

    const tgData = tg.initDataUnsafe();
    tgDataField.innerHTML = JSON.stringify(tgData);
} catch (throwed) {
    tgDataField.innerText = JSON.stringify(throwed);
}
tgDataField.innerText += 'Done!';