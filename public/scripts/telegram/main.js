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