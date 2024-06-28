let tg = window.Telegram.WebApp;
tg.expand();

document.querySelector('#userdata').innerText = tg.initData;