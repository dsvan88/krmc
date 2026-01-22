actionHandler.participantFieldGet = function (target, event) {
    const participantsFields = target.closest(".booking__participants");
    const newID = participantsFields.querySelectorAll(".participant").length;
    let data = new FormData();
    data.append('id', newID);
    request({
        url: 'participant-field-get',
        data: data,
        success: function (result) {
            target.insertAdjacentHTML("beforebegin", result['html']);
            let fields = participantsFields.querySelectorAll('input[data-action-change]');
            fields[fields.length - 1].addEventListener('change', (event) => actionHandler.changeCommonHandler.call(actionHandler, event));
            fields = participantsFields.querySelectorAll('input[data-action-input]')
            fields[fields.length - 1].addEventListener('input', (event) => actionHandler.inputCommonHandler.call(actionHandler, event))
        },
    });
}
actionHandler.participantFieldClear = function (target, event) {
    const parent = target.closest('div');
    const nameInput = parent.querySelector('input[name="participant[]"]');
    const arriveInput = parent.querySelector('input[name="arrive[]"]');
    if (nameInput.value !== '') {
        nameInput.value = '';
    }
    if (arriveInput.value !== '') {
        arriveInput.value = '';
    }
}
actionHandler.participantCheckChange = async function (e) {
    const name = e.target.value.trim();
    if (name === '') return false;
    if (name === '+1') return true;

    const participantsList = [];
    e.target.closest('form').querySelectorAll("input[name='participant[]']").forEach(i => i.value !== '' && i !== e.target ? participantsList.push(i.value) : false);
    if (participantsList.includes(name)) {
        alert('Гравець з таким іменем - вже зареєстрований на поточний вечір!');
        e.target.value = '';
        return false;
    }

    if (!name.startsWith('@')) {
        const fd = new FormData;
        fd.append('name', name);
        const r = await this.request({
            url: '/day/account/is_exists',
            data: fd,
        });

        if (r['confirm']) {
            if (await customConfirm(r['confirm']))
                return true;

            e.target.value = '';
            return false;
        }
    }
    return true;
}
actionHandler.bookingFormSubmit = function (event) {
    event.preventDefault();
    const url = event.target.action.slice(window.location.length);
    const formData = new FormData(event.target);
    request({
        url: url,
        data: formData,
        success: actionHandler.commonResponse,
    });
}

const formBooking = document.body.querySelector('form.booking__form');
if (formBooking) {
    formBooking.onsubmit = actionHandler.bookingFormSubmit;
}