let dblclick_func = false;
document.body.addEventListener('click', actionHandler.clickCommonHandler);
document.body.querySelectorAll('input[data-action-input]').forEach(element =>
	element.addEventListener('input', (event) => actionHandler.inputCommonHandler.call(actionHandler, event))
);
document.body.querySelectorAll('input[data-action-change]').forEach(element =>
	element.addEventListener('change', (event) => actionHandler.changeCommonHandler.call(actionHandler, event))
);
document.body.querySelectorAll('form[data-action-submit]').forEach(element =>
	element.addEventListener('submit', (event) => actionHandler.commonSubmitFormHandler.call(actionHandler, event))
);
document.body.querySelectorAll('input[type="tel"]').forEach(element =>{
	element.addEventListener('focus', (event) => actionHandler.phoneInputFocus.call(actionHandler, event));
    element.addEventListener('input', (event) => actionHandler.phoneInputFormat.call(actionHandler, event), false);
});
document.querySelectorAll('details[data-action-open],details[data-action-close]').forEach(element => 
	element.addEventListener('toggle', (event) => actionHandler.commonToggleHandler.call(actionHandler, event), false)
);

let menuCheckbox = document.body.querySelector('#profile-menu-checkbox');
if (menuCheckbox) {
	let menu = document.body.querySelector('div.header__profile-options');
	document.body.addEventListener('click', (event) => {
		if (!menuCheckbox.checked) {
			return false;
		};

		if (!(event.target == menu || menu.contains(event.target))) {
			menuCheckbox.checked = false;
		}
	});
};

pageCheckbox = document.body.querySelector('#header__dropdown-menu-checkbox');
if (pageCheckbox) {
	let menu = document.body.querySelector('li.header__navigation-item.dropdown');
	document.body.addEventListener('click', (event) => {
		if (!pageCheckbox.checked) {
			return false;
		};

		if (!(event.target == menu || menu.contains(event.target))) {
			pageCheckbox.checked = false;
		}
	});
};
actionHandler.noticer = new Noticer();