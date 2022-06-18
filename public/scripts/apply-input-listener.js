let fields = document.body.querySelectorAll('form.modal-form input[data-action-input]');
console.log(fields);
fields.forEach(field => {
    console.log(field);
    field.addEventListener('input', (event) => actionHandler.inputCommonHandler.call(actionHandler, event))
});