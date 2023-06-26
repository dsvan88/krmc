actionHandler.participantFieldGet = function (target, event) {
    const form = target.closest('form.booking');
    const newID = form.querySelectorAll(".booking__participant").length;
    const participantsFields = form.querySelector(".booking__participants");
    let data = new FormData();
    data.append('id', newID);
    request({
        url: 'participant-field-get',
        data: data,
        success: function (result) {
            participantsFields.insertAdjacentHTML("beforeend", result['html']);
            let fields = participantsFields.querySelectorAll('input[data-action-change]');
            fields[fields.length-1].addEventListener('change', (event) => actionHandler.changeCommonHandler.call(actionHandler, event));
            fields = participantsFields.querySelectorAll('input[data-action-input]')
            fields[fields.length-1].addEventListener('input', (event) => actionHandler.inputCommonHandler.call(actionHandler, event))
        },
    });
}
actionHandler.participantFieldClear = function (target, event) {
    const parent = target.closest('div');
    const nameInput = parent.querySelector('input[name="participant[]"]');
    const arriveInput = parent.querySelector('input[name="arrive[]"]');
    if (nameInput.value !== ''){
        nameInput.value = '';
    }
    if (arriveInput.value !== ''){
        arriveInput.value = '';
    }
}
actionHandler.participantCheckChange = function (event) {
    const newName = event.target.value.trim();
    if (newName === '') {
        return false;
    }

    let participantsList = [];
    event.target.closest('form').querySelectorAll("input[name='participant[]']").forEach(item => item.value !== '' && item !== event.target ? participantsList.push(item.value) : false);
    if (newName !== '+1' && participantsList.includes(newName)) {
        alert('Гравець з таким іменем - вже зареєстрований на поточний вечір!');
        event.target.value = '';
    }
}
actionHandler.bookingFormSubmit = function (event) {
    event.preventDefault();
    let url = event.target.action.slice(window.location.length);
    let formData = new FormData(event.target);
    request({
        url: url,
        data: formData,
        success: actionHandler.commonResponse,
    });
}

let formBooking = document.body.querySelector('form.booking');
if (formBooking) {
    formBooking.onsubmit = actionHandler.bookingFormSubmit;
}