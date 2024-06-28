const tgDataField = document.querySelector('#userdata');
try {

    let tg = window.Telegram.WebApp;
    tg.expand();

    let tgData = tg.initDataUnsafe();

    tgDataField.innerHTML = JSON.stringify(tgData);

    tgDataField.innerHTML += '<br>Unsafe is done!';

    tgData = tg.initData();
    tgDataField.innerHTML += `<br>${tgData}`;

    tgDataField.innerHTML += '<br>Safe is done!';

} catch (throwed) {
    tgDataField.innerText = `Error: ${JSON.stringify(throwed)}`;
}