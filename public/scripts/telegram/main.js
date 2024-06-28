let tg = window.Telegram.WebApp;
tg.expand();

const tgDataField = document.querySelector('#userdata');
const tgData = tg.initDataUnsafe();

let html = '';
for (const [key, value] of Object.entries(tgData)) {
    string += `<div>${key}:</div><p>${value}</p>`;
}
tgDataField.innerHTML = html;