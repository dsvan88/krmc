let fields = document.body.querySelectorAll('form.modal-form input[data-action-input]');
fields.forEach(field => {
    field.addEventListener('input', (event) => actionHandler.inputCommonHandler.call(actionHandler, event))
});