let tg = window.Telegram.WebApp;
tg.expand();

const tgDataField = document.querySelector('#userdata');
let tgData = tg.initDataUnsafe();
try {
    tgDataField.innerHTML = JSON.stringify(tgData);
} catch (throwed) {
    tgDataField.innerText = JSON.stringify(throwed);
}
tgDataField.innerHTML += '<br>Unsafe is done!';

tgData = tg.initData();
tgDataField.innerHTML += `<br>${tgData}`;

tgDataField.innerHTML += '<br>Safe is done!';