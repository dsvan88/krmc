let dblclick_func = false;
const body = document.body;


body.addEventListener('click', actionHandler.clickCommonHandler.bind(actionHandler));
body.querySelectorAll('input[data-action-input]').forEach(element =>
	element.addEventListener('input', actionHandler.inputCommonHandler.bind(actionHandler))
);
body.querySelectorAll('input[data-action-change]').forEach(element => {
	element.addEventListener('change', actionHandler.changeCommonHandler.bind(actionHandler));
	element.changeListener = true;
});
body.querySelectorAll('form').forEach(element =>
	element.addEventListener('submit', actionHandler.commonSubmitFormHandler.bind(actionHandler))
);
body.querySelectorAll('input[type="tel"]').forEach(element => {
	element.addEventListener('focus', actionHandler.phoneInputFocus.bind(actionHandler));
	element.addEventListener('input', actionHandler.phoneInputFormat.bind(actionHandler), false);
});
document.querySelectorAll('details[data-action-open],details[data-action-close]').forEach(element =>
	element.addEventListener('toggle', actionHandler.commonToggleHandler.bind(actionHandler), false)
);

const menuCheckbox = body.querySelector('#profile-menu-checkbox');
if (menuCheckbox) {
	const menu = body.querySelector('div.header__profile-options');
	body.addEventListener('click', (event) => {
		if (!menuCheckbox.checked) {
			return false;
		};

		if (!(event.target == menu || menu.contains(event.target))) {
			menuCheckbox.checked = false;
		}
	});
};

const pageCheckbox = body.querySelector('#header__dropdown-menu-checkbox');
if (pageCheckbox) {
	let menu = body.querySelector('li.header__navigation-item.dropdown');
	body.addEventListener('click', (event) => {
		if (!pageCheckbox.checked) {
			return false;
		};

		if (!(event.target == menu || menu.contains(event.target))) {
			pageCheckbox.checked = false;
		}
	});
};
actionHandler.noticer = new Noticer();