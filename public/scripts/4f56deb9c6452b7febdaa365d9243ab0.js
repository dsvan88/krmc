async function request({ url, data, method = 'POST', responseType = 'json', success, error, ...args }) {

	if (error == undefined) {
		error = function (result) {
			console.log(result);
			console.log(`Error: Ошибка связи с сервером.`);
		};
	}

	if (debug) {
		if (success) {
			success = catchResult(success);
		}
		error = catchResult(error);
	}

	if (!data) method = 'GET';

	let options = {
		method: method.toUpperCase(),
		headers: {
			"X-Requested-With": "XMLHttpRequest"
		},
	};

	if (options['method'] === 'GET') {
		if (data) {
			url += '?' + btoa(new URLSearchParams(data).toString());
			data = undefined;
		}
	}
	else if (typeof data === 'string' && data[0] === '{') {
		options['headers']["Content-Type"] = 'application/json';
	}

	if (data) {
		options['body'] = data;
	}

	if (url[0] === '/') {
		url = url.substr(1);
	}
	if (!url.includes('://')) {
		url = '/api/' + url;
	}

	try {
		const response = await fetch(url, options);

		if (response.ok) {
			let description = response.headers.get('content-description');
			if (description && description === "File Transfer") {
				let filename = response.headers.get("content-disposition").replace(/^.*?=/, '').slice(1, -1);
				let blob = await response.blob();
				let dataUrl = URL.createObjectURL(blob)
				download(dataUrl, filename);
				return true;
			}

			let result;
			if (responseType === "base64") {
				result = await response.text();
				result = JSON.parse(atob(result.trim()));
			}
			else {
				result = await response[responseType]();
			}
			if (success) {
				success(result);
			}
			return result;
		}
		const status = response.status;
		const result = await response.json();
		error(result);
	} catch (throwed) {
		error(throwed);
	}
}

function download(dataurl, filename = 'backup.txt') {
	let a = document.createElement("a");
	a.href = dataurl;
	a.setAttribute("download", filename);
	a.click();
	return true;
}
let actionHandler = {
	noticer: null,
	phoneMask: "+38 (0__) ___-__-__",
	inputCommonHandler: function (event) {
		let action = event.target.dataset.actionInput;
		if (!action.startsWith('autocomplete-') || event.target.value.length <= 2) return false;
		this.autocompleteInput(event.target);
	},
	changeCommonHandler: function (event) {
		const type = camelize(event.target.dataset.actionChange);
		if (debug) console.log(type);
		try {
			actionHandler[type](event);
		} catch (error) {
			console.log(error);
			alert(`Не существует метода для этого action-type: ${type}... или возникла ошибка. Сообщите администратору!\n${error.name}: ${error.message}`);
		}
	},
	commonToggleHandler: function (event) {
		let method = '';
		if (event.target.open && event.target.dataset.actionOpen) {
			method = camelize(event.target.dataset.actionOpen);
		} else if (!event.target.open && event.target.dataset.actionClose) {
			method = camelize(event.target.dataset.actionClose);
		}

		if (!method) return false;

		if (debug) console.log(method);
		try {
			actionHandler[method](event);
		} catch (error) {
			console.log(error);
			alert(`Не существует метода для этого action-type: ${method}... или возникла ошибка. Сообщите администратору!\n${error.name}: ${error.message}`);
		}
	},
	clickCommonHandler: function (event) {
		let target = event.target;
		if (!("actionClick" in target.dataset) && !("actionDblclick" in target.dataset)) {
			target = target.closest(['*[data-action-click],*[data-action-dblclick]']);
		}
		if (!target) return false;

		if ("actionDblclick" in target.dataset) {
			if (dblclick_func !== false) {
				clearTimeout(dblclick_func);
				dblclick_func = false;
				actionHandler.clickFunc(target, event, 'actionDblclick');
			}
			else {
				dblclick_func = setTimeout(() => {
					if (dblclick_func !== false) {
						clearTimeout(dblclick_func);
						dblclick_func = false;
						actionHandler.clickFunc(target, event);
					};
				}, 200)
			}
		}
		else {
			actionHandler.clickFunc(target, event);
		}
	},
	clickFunc: async function (target, event, method = 'actionClick') {
		if (!(method in target.dataset)) return false;
		event.preventDefault();
		const self = this;

		if ("mode" in target.dataset) {
			if (target.dataset['mode'] === 'location') {
				window.location = target.dataset[method]
				return true;
			}
		}
		let type = camelize(target.dataset[method]);
		if (debug) console.log(type);

		type = self[type] ? type : 'apiTalk';

		try {
			self[type](target, event, method);
		} catch (error) {
			alert(`Не существует метода для этого action-click: ${type}... или возникла ошибка. Сообщите администратору!\r\n${error.name}: ${error.message}`);
			console.log(error);
		}
	},
	apiTalk: async function (target, event, method, formData = null) {
		let action = target.dataset[method];
		let modal = false;
		const self = this;
		if (action.endsWith('/form')) {
			modal = new ModalWindow();
		}

		if (!formData)
			formData = new FormData;

		if (target.value) {
			formData.append('value', target.value);
		}
		for (const [key, value] of Object.entries(target.dataset)) {
			if (key !== method)
				formData.append(key, value);
		}

		if (target.dataset.verification) {
			if (target.dataset.verification === 'confirm') {
				if (!confirm('Are you sure?')) {
					if (modal) modal.close();
					return false;
				}
			}
			else {
				let input = { type: 'text' };
				if (/(root|pass)/.test(target.dataset.verification))
					input = { type: 'password' };
				const verification = await self.verification(null, target.dataset.verification, input);
				formData.append('verification', verification);
			}
		}

		const result = await request({
			url: action,
			data: formData,
		});

		if (modal) self.commonModalEvent(modal, action, result);

		self.commonResponse(result);

		return result;
	},
	commonFormEventEnd: function ({ modal, data, submit = null, ...args } = {}) {
		let modalWindow;
		const self = this;

		if (data['error']) {
			return modalWindow = modal.fill({ html: data['html'], title: 'Error!', buttons: [{ 'text': 'Okay', 'className': 'modal__close positive' }] });
		}

		if (!submit || !self[submit])
			submit = 'commonSubmitFormHandler';

		data.context = self;
		data.submit = self[submit];

		modalWindow = modal.fill(data);

		self.handleEvents(modalWindow);

		return modalWindow;
	},
	commonFormEventReady: function ({ modal = null, result = {}, type = null }) {
		let firstInput = modal.querySelector("input");
		if (firstInput !== null) {
			firstInput.focus();
		}
		if (result["javascript"]) {
			window.eval(result["javascript"]);
		}
	},
	commonSubmitFormHandler: async function (event, formData = null, modal = null, args = null) {
		event.preventDefault();
		if (!formData) formData = new FormData(event.target);
		const self = this;
		let submitResult = false;
		await request({
			url: event.target.action.replace(window.location.origin + '/', ''),
			data: formData,
			success: (result) => {
				submitResult = result;
				self.commonResponse.call(self, result, modal)
			},
			error: (result) => self.commonResponse.call(self, result, modal),
		});
		return submitResult;
	},
	commonModalEvent: function (modal, action, data) {
		const self = this;
		if (data["error"]) {
			self.commonFormEventEnd({ modal, data });
			new Alert({ title: 'Error!', text: data["message"] });
			return false;
		}

		if (!data["modal"]) return false;

		let actionModified = camelize(action.replace(/\//g, '-'));

		if (data["jsFile"]) {
			addScriptFile(data["jsFile"]);
		};

		if (data["cssFile"]) {
			addCssFile(data["cssFile"]);
		};

		if (self[actionModified + "Ready"])
			modal.content.onload = self[actionModified + "Ready"]({ modal, data: data });

		setTimeout(() => self.commonFormEventEnd.call(self, { modal, data, submit: actionModified + 'Submit' }), 50);
	},
	commonResponse: function (response, modal = null) {
		const self = this;
		if (response["error"]) {
			new Alert({ title: 'Error!', text: response["message"] });
			return false;
		}
		if (response["message"]) {
			// new Alert({ text: response["message"] });
			alert(response["message"]);
		}
		if (response["notice"] && this.noticer) {
			this.noticer.add(response["notice"]);
		}
		if (response["location"]) {
			setTimeout(() => window.location = response["location"], response["time"] ? response["time"] : 100);
		}
		if (modal && modal.paused)
			setTimeout(() => modal.unpause(), 500);
	},
	autocompleteInput: function (target) {
		const action = target.dataset.actionInput;
		const type = action.replace(/autocomplete-/, '');

		const formData = new FormData();
		formData.append('term', target.value);
		if (target.dataset.dependence) {
			const form = target.closest('form');
			if (target.dataset.dependence.includes(',')) {
				const names = target.dataset.dependence.split(',');
				const values = {};
				for (const name of names) {
					const dependenceInput = form.querySelector(`*[name="${name}"]`);
					if (dependenceInput)
						values[dependenceInput.name] = dependenceInput.value;
				}
				formData.append('dependence', JSON.stringify(values));
			}
			else {
				const dependenceInput = form.querySelector(`*[name="${target.dataset.dependence}"]`);
				if (dependenceInput)
					formData.append('dependence', dependenceInput.value);
			}

		}
		request({
			url: 'autocomplete/' + type,
			data: formData,
			success: function (result) {
				if (!result) return false;
				let options = [];
				const deleteOptions = [];
				for (let i = 0; i < target.list.options.length; i++) {
					options.push(target.list.options[i].value);
					if (!result['result'].includes(target.list.options[i].value)) {
						deleteOptions.push(i);
					}
				}
				result['result'].forEach(item => {
					if (options.includes(item)) return;
					const option = document.createElement('option');
					option.value = item;
					target.list.appendChild(option);
				});
				if (!deleteOptions.length) return false;
				deleteOptions.forEach(index => {
					if (!target.list.options[index]) return;
					target.list.options[index].remove()
				});
			},
		});
	},
	verification: async function (form, url, input) {
		const formData = form ? new FormData(form.tagName === 'FORM' ? form : undefined) : undefined;
		const verification = await request({ url: url, data: formData });

		if (!verification) return false;

		if (verification['result'] === false) {
			if (verification['message']) new Alert({ text: verification['message'] });
			return false;
		}

		const promise = new Promise((resolve) => {
			new Prompt({
				title: "Verification",
				text: verification['message'],
				value: '',
				input: input,
				action: resolve,
			});
		});

		return promise.then();
	},
	phoneInputFocus: function (event) {
		const input = event.target,
			inputNumbersValue = input.value.replace(/\D/g, '');

		if (!inputNumbersValue)
			input.value = this.phoneMask;

		const pos = input.value.indexOf('_');
		if (pos) {
			input.setSelectionRange(pos, pos);
		}
	},
	phoneInputFormat: function (event) {
		const input = event.target;
		let inputNumbersValue = input.value.replace(/\D/g, '');

		if (!inputNumbersValue) {
			return input.value = "";
		}
		let maskLength = this.phoneMask.replace(/[^0-9_]/g, '').length;

		if (inputNumbersValue.length < maskLength)
			inputNumbersValue = inputNumbersValue.padEnd(maskLength, '_');

		let result = '';
		let index = -1;
		for (let char of inputNumbersValue) {
			++index;
			if (index >= maskLength)
				break;
			if (index === 0)
				char = `+${char}`;
			else if (index === 2)
				char = ` (${char}`;
			else if (index === 4)
				char = `${char}) `;
			else if (index === 8 || index === 10)
				char = `-${char}`;
			result += char;
		}
		input.value = result;

		let pos = input.value.indexOf('_');
		if (pos) {
			if (!event.data) {
				if (input.value[pos - 1] === '-') {
					--pos;
				}
				else if ([' ', '('].indexOf(input.value[pos - 1]) !== -1) {
					pos -= 2;
				}
			}
			input.setSelectionRange(pos, pos);
		}
	},
	setLocale: function (event) {
		window.location = '?lang=' + event.target.value;
	},
	handleEvents: function (target) {
		const self = this;
		target.querySelectorAll('input[data-action-input]').forEach(element =>
			element.addEventListener('input', (event) => self.inputCommonHandler.call(self, event))
		);
		target.querySelectorAll('input[data-action-change]').forEach(element =>
			element.addEventListener('change', (event) => self.changeCommonHandler.call(self, event))
		);
		target.querySelectorAll('form[data-action-submit]').forEach(element =>
			element.addEventListener('submit', (event) => self.commonSubmitFormHandler.call(self, event))
		);
		target.querySelectorAll('input[type="tel"]').forEach(element => {
			element.addEventListener('focus', (event) => self.phoneInputFocus.call(self, event));
			element.addEventListener('input', (event) => self.phoneInputFormat.call(self, event), false);
		});
		target.querySelectorAll('details[data-action-open],details[data-action-close]').forEach(element =>
			element.addEventListener('toggle', (event) => self.commonToggleHandler.call(self, event), false)
		);
	}
};

class Alert {
    dialog = null;
    form = null;
    title = null;
    text = null;
    buttonWrapper = null;
    agreeButton = null;
    dragged = false;
    keyDown = false;

    constructor({ title = "Alert", text = "There is no information, yet!", close = null } = {}) {

        this.dialog = this.build();
        this.fill({ title, text });
        this.dialog.show();
        this.customClose = close;

        this.attachEvents();
        return this;
    }

    build() {

        this.overlay = document.createElement('div');
        this.overlay.classList.add('popup__overlay');

        this.dialog = document.createElement('dialog');
        this.dialog.classList.add('popup');
        this.dialog.draggable

        this.form = document.createElement('form');
        this.form.method = 'dialog';

        this.title = document.createElement('h4');
        this.title.classList.add('popup__title');
        this.form.append(this.title);

        this.text = document.createElement('p');
        this.text.classList.add('popup__text');
        this.form.append(this.text);

        this.buttonWrapper = document.createElement('div');
        this.buttonWrapper.classList.add('popup__button-wrapper');

        this.agreeButton = document.createElement('button');
        this.agreeButton.innerText = 'Ok';
        this.agreeButton.classList.add('popup__button', 'positive');
        this.buttonWrapper.append(this.agreeButton);
        this.form.append(this.buttonWrapper);

        this.dialog.append(this.form);
        this.overlay.append(this.dialog);

        document.body.append(this.overlay);
        this.dialog.tabIndex = -1;
        return this.dialog;
    }
    fill({ title = "PopUp", text = "There is no information, yet!" }) {
        this.title.innerText = title;
        if (/\<[a-s]/.test(text))
            this.text.innerHTML = '<p>' + text.replace(/\n/g, '</p><p>') + '</p>';
        else
            this.text.innerText = text;
    }
    close() {
        this.dialog.close();
        this.dialog.remove();
        this.overlay.remove();
    }
    attachEvents() {
        const self = this;

        self.dialog.addEventListener('keydown', (event) => self.keyDownHandler.call(self, event));
        self.dialog.addEventListener('keyup', (event) => self.keyUpHandler.call(self, event));

        self.dialog.addEventListener('close', () => self.close.call(self));

        if (this.customClose)
            self.dialog.addEventListener('close', () => self.customClose.call(self));

        self.dialog.ondragstart = () => false;

        self.title.addEventListener('mousedown', (event) => self.dragStart.call(self, event));

        self.dialog.addEventListener('touchstart', (event) => self.dragStart.call(self, event));
    }
    keyDownHandler(event) {
        if (event.isComposing) return false;
        if (event.keyCode === 13 || event.keyCode === 27) this.keyDown = true;
    }
    keyUpHandler(event) {
        if (event.isComposing || !this.keyDown) return false;
        if (event.keyCode === 13 || event.keyCode === 27) this.agreeButton.click();
    }
    dragStart(event) {
        if (this.dragged) return;
        this.dragged = true;

        const clientX = event.clientX || event.targetTouches[0].clientX;
        const clientY = event.clientY || event.targetTouches[0].clientY;

        this.shiftX = clientX - this.dialog.getBoundingClientRect().left;
        this.shiftY = clientY - this.dialog.getBoundingClientRect().top;

        this.dragnDrop(event);
    }
    dragnDrop(event) {

        const self = this;

        self.dialog.style.position = 'absolute';
        self.dialog.style.zIndex = 1000;
        self.dialog.style.margin = 0;

        document.body.append(self.dialog);

        const pageX = event.pageX || event.targetTouches[0].pageX;
        const pageY = event.pageY || event.targetTouches[0].pageY;

        self.moveAt(pageX, pageY);

        document.context = self;
        document.addEventListener('mousemove', self.onMouseMove);
        document.addEventListener('touchmove', self.onMouseMove);

        self.dialog.onmouseup = (event) => this.moveEnd(event, 'mousemove');
        self.dialog.ontouchend = (event) => this.moveEnd(event, 'touchmove');

    }
    moveEnd(event, eventName) {
        document.removeEventListener(eventName, this.onMouseMove);
        this.dragged = false;
        this.dialog.ontouchend = null;
        document.context = null;
    }
    moveAt(pageX, pageY) {
        this.dialog.style.left = pageX - this.shiftX + 'px';
        this.dialog.style.top = pageY - this.shiftY + 'px';
    }
    onMouseMove(event) {
        const self = this.context;

        if (!self) return false;

        const pageX = event.pageX || event.targetTouches[0].pageX;
        const pageY = event.pageY || event.targetTouches[0].pageY;

        self.moveAt(pageX, pageY);
    }
}

class Confirm extends Alert {
    cancelButton = null;
    state = true;
    constructor({ title = "Confirmation", text = "Are you sure?", action = null, cancel = null } = {}) {

        super({ title, text });

        this.action = action || ((data) => console.log(data));
        this.cancel = cancel;

        this.modifyForm().modifyEvents();
        return this;
    }
    modifyForm() {

        this.agreeButton.innerText = 'Yes';

        this.cancelButton = document.createElement('button');
        this.cancelButton.classList.add('popup__button', 'negative');
        this.cancelButton.value = "cancel";
        this.cancelButton.innerText = "No";
        this.buttonWrapper.append(this.cancelButton);

        return this;
    }
    modifyEvents() {
        const self = this;
        self.form.addEventListener('submit', (event) => self.submit.call(self, event), { once: true });

        self.cancelButton.addEventListener('click', () => self.state = false);
    }
    submit() {
        if (this.state)
            return this.action(true);
        else
            return this.cancel ? this.cancel(false) : this.action(false);
    }
    keyUpHandler(event) {
        if (event.isComposing || !this.keyDown) return false;
        if (event.keyCode === 13) return this.agreeButton.click();
        else if (event.keyCode === 27) return this.cancelButton.click();
    }
}


class Prompt extends Confirm {
    input = null;
    state = true;
    inputWrapper = null;

    constructor({ title = "Prompt", text = "Enter value:", value = "No", action = null, cancel = null, input = { type: 'text' } } = {}) {
        super({ title, text, action, cancel });
        this.modifyPrompt({ value, input });
        return this;
    }
    modifyPrompt({ value = "No", input = { type: 'text' } } = {}) {
        this.agreeButton.innerText = 'Ok';
        this.cancelButton.innerText = 'Cancel';

        this.inputWrapper = document.createElement('div');
        this.inputWrapper.classList.add('popup__input-wrapper');

        this.input = document.createElement('input');
        this.input.classList.add('popup__input');
        this.input.value = value;

        for (const attr in input) {
            this.input[attr] = input[attr];
        }

        this.inputWrapper.append(this.input);

        this.buttonWrapper.before(this.inputWrapper);
        this.input.focus();
        return this;
    }
    submit() {
        if (this.state)
            return this.action(this.input.value);
        else
            return this.cancel ? cancel(this.input.value) : this.action(false);
    }
}
class Noticer {

	noticesPlace = null;
	constructor() {
		const self = this;
		this.noticesPlace = document.querySelector('.notices')
		const notices = this.noticesPlace.querySelectorAll('.notice');
		notices.forEach(notice => {
			notice.querySelector('.notice__close').addEventListener('click', (event) => self.close.call(self, notice));
		});
	};
	add(notice) {
		if (typeof notice === 'string') {
			notice = { message: notice };
		}
		const noticeBlock = document.createElement('div');
		noticeBlock.classList.add('notice');
		const noticeIcon = document.createElement('div');
		noticeIcon.classList.add('notice__icon', 'fa');
		noticeBlock.append(noticeIcon);
		const noticeMessageBlock = document.createElement('div');
		noticeMessageBlock.classList.add('notice__message');
		noticeMessageBlock.innerHTML = '<p> ' + notice['message'].replace(/\n/g, '</p><p>') + '</p>';

		if (notice['type']) {
			noticeBlock.classList.add(notice['type']);
			const noticeTitle = document.createElement('h3');
			noticeTitle.innerText = notice['type'].toUpperCase() + ':';
			noticeMessageBlock.prepend(noticeTitle);

			noticeIcon.classList.add(notice['type'] === 'error' ? 'fa-times-circle' : 'fa-exclamation-triangle');
		}
		else {
			noticeIcon.classList.add('fa-check-circle-o');
		}

		const noticeCloseBlock = document.createElement('span');
		noticeCloseBlock.className = 'notice__close fa fa-window-close';

		noticeBlock.append(noticeMessageBlock);
		noticeBlock.append(noticeCloseBlock);

		this.noticesPlace.append(noticeBlock);

		const self = this;
		noticeCloseBlock.addEventListener('click', (event) => self.close.call(self, noticeBlock))
		if (notice['time']) {
			noticeCloseBlock.timeOut = setTimeout((event) => self.close.call(self, noticeBlock), notice['time']);
		}
		if (notice["location"]) {
			setTimeout(() => notice["location"] === 'reload' ? window.location.reload() : window.location = notice["location"], notice['time'] ? notice['time'] + 100 : 1000);
		}
	}
	close(notice) {
		notice.style.color = '#00000000';
		notice.style.opacity = '0.0';
		notice.style.height = '0px';
		notice.style.paddingTop = '0px';
		notice.style.paddingBottom = '0px';
		notice.style.marginBottom = '0px';
		if (notice.timeOut) {
			clearTimeout(noticeCloseBlock.timeOut);
		}
		setTimeout(() => notice.remove(), 300);
	}
}
let debug = false;

function inttotime(t) {
	m = Math.floor(t / 6000);
	s = Math.floor((t % 6000) / 100);
	ms = t % 100;
	return "0" + m + ":" + (s > 9 ? s : "0" + s) + ":" + (ms > 9 ? ms : "0" + ms);
}
function redirectPost(url, data) {
	let form = createNewElement({
		tag: "form",
		method: "POST",
		action: url,
	});
	for (let name in data) {
		let input = createNewElement({
			tag: "input",
			type: "hidden",
			name: name,
			value: data[name],
		});
		form.appendChild(input);
	}
	document.body.appendChild(form);
	form.submit();
}

function simpleObjectToFormData(obj) {
	let formData = new FormData();
	for (let item in obj){
		formData.append(item, obj[item]);
	}
	return formData;

}
function simpleObjectToGetString(obj) {
	let strData = "";
	for (let item in obj) {
		strData += `${item}=${obj[item]}&`;
	}
	return strData.slice(0, -1);
}
function serializeForm(target) {
	
	const elements = target.querySelectorAll("input,select,textarea");
	let result = {};
	elements.forEach((element) => {
		if (element.tagName === "INPUT" && element.type === "checkbox" && !element.checked) {
			return;
		};
		/* if (element.tagName === "TEXTAREA" && element.name === "html" && element.id !== undefined && CKEDITOR.instances[element.id]){
			result[element.name] = CKEDITOR.instances[element.id].getData().replace(/\&/g, "%26");
			return;
		}; */
		if (element.value == '') {
			return;
		};
		if (result.hasOwnProperty(element.name)) {
			if (typeof result[element.name] === "string") {
				result[element.name] = [
					result[element.name],
					element.value.replace(/\&/g, "%26")
				]
			}
			else{
				result[element.name][result[element.name].length] = element.value.replace(/\&/g, "%26");
			}
			return;
		}
		result[element.name] = element.value.replace(/\&/g, "%26");
	});
	return result;
}
function camelize(str) {
	return str
		.replace(/\//g, '-')
		.split("-") // разбивает 'my-long-word' на массив ['my', 'long', 'word']
		.map((word, index) => (index == 0 ? word : word[0].toUpperCase() + word.slice(1)))
		.join(""); // соединяет ['my', 'Long', 'Word'] в 'myLongWord'
}

function addScriptFile(src,callback = '') {
	if (Array.isArray(src)){
		for (let index = 0; index < src.length; index++) {
			addScriptFile(src[index], callback)
		}
	}
	else{
		if (document.head.querySelector(`script[src="${src}"]`)){
			return false;
		}
		let script = document.createElement('script');
		script.src = src;
		script.async = true;
		document.head.appendChild(script);
		if (callback !== '')
			script.onload = callback;
		return true;
	}
}
function addCssFile(src) {
	if (Array.isArray(src)){
		for (let index = 0; index < src.length; index++) {
			addCssFile(src[index])
		}
	}
	else{
		if (document.head.querySelector(`link[href="${src}"]`)){
			return false;
		}
		let link  = document.createElement('link');
		link.rel  = 'stylesheet';
		link.type = 'text/css';
		link.href = src;
		link.media = 'all';
		document.head.appendChild(link);
	}
}

function formDataToJson(data) {
    const object = {};
    data.forEach((value, key) => {
        value = value.replace("'", '’');
        if (key.includes('[')) {
			key = key.substr(0, key.indexOf('['));
			if (!object[key]){
				object[key] = [];
			}
			object[key][object[key].length] = value;
			return;
        }
        else {
            object[key] = value;
        }
    });
    return JSON.stringify(object);
}

function catchResult(func) {
	return function (args) {
		console.log(args);
		return func.call(this, args);
	};
}

function clearBlock(block) {
	while (block.firstChild && block.removeChild(block.firstChild));
}
function createNewElement({ tag: tagName = "div", ...attributes }) {
	if (debug) {
		console.log(attributes);
	}
	let element = document.createElement(tagName);
	applyAttributes(element, attributes);
	return element;
}
function applyAttributes(element, attributes) {
	for (let [attName, attrValue] of Object.entries(attributes)) {
		if (typeof attrValue !== "object") {
			element[attName] = attrValue;
		}
		else {
			applyAttributes(element[attName], attrValue);
		}
	}
}
function CKEditorApply(editors) {
	for (let index = 0; index < editors.length; index++) {
		let randomIndex = Math.random(321123);
		editors[index].id = randomIndex;
		DecoupledEditor.create(
			editors[index].querySelector('.editor')
		).then(
			editor => {
				const toolbarContainer = editors[index].querySelector('.toolbar-container');
				toolbarContainer.prepend(editor.ui.view.toolbar.element);
				if (!window.CKEDITOR) {
					window.CKEDITOR = {
						'instances' : {}
					};
				}
				window.CKEDITOR.instances[randomIndex] = editor;
			}
		)
	}
}
Array.prototype.shuffle = function () {
	let j;
	for (let i = this.length - 1; i > 0; i--) {
		j = Math.floor(Math.random() * (i + 1)); // случайный индекс от 0 до i
		[this[i], this[j]] = [this[j], this[i]];
	}
	return this;
};
class ModalWindow {

	modal = null;
	modalIndex = null;
	modalClose = null;
	commonOverlay = null;
	currentOverlay = null;
	title = null;
	formSubmitHandler = null;
	pauseLayout = null;
	// context = null;
	content = null;
	dragged = false;

	get paused() {
		return this.pauseLayout ? true : false;
	}

	constructor({ divId = "modalWindow", html = "", title = "", buttons = [], submit = null, context = null } = {}) {
		this.commonOverlay = document.body.querySelector("#overlay");
		if (this.commonOverlay === null) {
			this.commonOverlay = document.createElement("div");
			this.commonOverlay.id = "overlay";
			document.body.append(this.commonOverlay);
			setTimeout(() => this.commonOverlay.style.opacity = 0.3, 100);
		}

		this.prepeare(divId);

		if (html) {
			this.fill({ html, title, buttons, submit, context });
		}
		this.attachEvents();
	};
	fill({ html = "", title = "", buttons = [], submit = null, context = null }) {
		let modalContainer = null;
		if (html) {
			this.content.innerHTML = html;
		}
		
		if (title){
			if (/<\w+?>/.test(title)){
				this.title.innerHTML = title;
			} else {
				this.title.innerText = title;
			}
		}
		if (buttons.length !== 0) {
			modalContainer = this.content || this.modal.querySelector('.modal__container');
			const modalButtons = document.createElement('div');
			modalButtons.className = 'modal__buttons';
			buttons.forEach(button => {
				const element = document.createElement('button');
				element.innerText = button.text;
				element.className = button.className;
				element.type = button.type ? button.type : 'button';
				modalButtons.append(element);
			})
			modalContainer.append(modalButtons)
			this.content = modalContainer;
		}
		
		if (submit && this.content) {
			this.formSubmitHandler = submit;
			this.context = context;
			let form = this.content.querySelector('form');
			if (!form) {
				form = document.createElement('form');
				form.action = "/";
				form.method = "POST";
				form.innerHTML = this.content.innerHTML;
				this.content.innerHTML = form.outerHTML;
				form = this.content.querySelector('form');
			}
			form.addEventListener('submit', event => this.submit(event))
		}
		this.attachEvents();

		// this.content.dispatchEvent(new Event('load'));

		return this.content;
	};
	clear() {
		this.modal.querySelector('.modal__container').innerHTML = '';
	};
	prepeare(divId = "modalWindow") {
		let modalHeader = document.createElement("div");
		modalHeader.className = "modal__header";

		this.title = document.createElement("h3");
		this.title.className = 'modal__title';
		this.title.innerHTML = 'Завантаження...';

		this.modalClose = document.createElement("i");
		this.modalClose.className = "fa fa-window-close modal__close";
		modalHeader.append(this.title);
		modalHeader.append(this.modalClose);

		this.modal = document.createElement("div");
		this.modal.className = "modal";
		this.modal.append(modalHeader);
		
		this.content = document.createElement("div");
		this.content.classList.add('modal__container');

		const modalRow = document.createElement("div");
		modalRow.classList.add('modal__buttons');
		const icon = document.createElement("i");
		icon.classList.add('fa', 'fa-cog', 'fa-spin', 'fa-3x', 'fa-fw')
		const iconLabel = document.createElement("span");
		iconLabel.classList.add('sr-only');
		iconLabel.innerText = 'Завантаження...';
		modalRow.append(icon, iconLabel);
		this.content.append(modalRow);
		this.modal.append(this.content);

		this.currentOverlay = document.createElement("div");
		this.currentOverlay.className = "modal__overlay modal__close";
		this.currentOverlay.id = divId;
		this.currentOverlay.append(this.modal);

		document.body.append(this.currentOverlay);

		this.currentOverlay.addEventListener("click",  (event) => this.close(event));

		this.popUp();
	};
	popUp() {

		this.modalIndex = document.body.querySelectorAll(".modal").length;

		this.currentOverlay.style.zIndex = 7 + this.modalIndex;
		this.modal.style.opacity = 0;
		this.modal.style.display = 'block';
		setTimeout(() => {
			this.modal.style.opacity = 1
			this.modal.style.transform = 'translateY(-2%)';
		}, 100);

	};
	pause() {
		this.pauseLayout = document.createElement('div');
		this.pauseLayout.classList.add('modal__pause');
		const pauseIcon = document.createElement('i');
		pauseIcon.classList.add('fa', 'fa-cog', 'fa-spin', 'fa-3x', 'fa-fw');
		this.pauseLayout.append(pauseIcon);
		this.content.append(this.pauseLayout);
	}
	unpause() {
		this.pauseLayout.remove();
	}
	close(event) {
		if (event && event.target){
			if (!event.target.classList.contains("modal__close"))
				return;

			if (this.currentOverlay === event.target && !confirm('Ви впевнені, що бажаєте закрити поточне вікно?'))
				return;
		}
		if (this.modalIndex === 1) {
			this.commonOverlay.style.opacity = 0;
			setTimeout(() => this.commonOverlay.remove(), 300);
		}
		const _self = this;

		this.modal.style.opacity = 0;
		this.modal.style.transform = 'translateY(2%)';
		setTimeout(() => this.currentOverlay.remove(), 300);
	}
	submit(event) {
		event.preventDefault();
		this.pause();
		let formData = new FormData(event.target);
		if (this.context) {
			this.formSubmitHandler.call(this.context, event, formData, this);
		}
		else{
			this.formSubmitHandler(event, formData, this);
		}
	}
	attachEvents() {
        const self = this;

		self.currentOverlay.querySelectorAll('.modal__close').forEach(element => element.addEventListener("click",  (event) => self.close(event)) );

        // self.modal.ondragstart = () => false;

        // self.title.addEventListener('mousedown', (event) => self.dragStart.call(self, event));

        // self.modal.addEventListener('touchstart', (event) => self.dragStart.call(self, event));
    }
	dragStart(event) {

        if (this.dragged) return;
        this.dragged = true;

        const clientX = event.clientX || event.targetTouches[0].clientX;
        const clientY = event.clientY || event.targetTouches[0].clientY;

        this.shiftX = clientX - this.modal.getBoundingClientRect().left;
        this.shiftY = clientY - this.modal.getBoundingClientRect().top;

        this.dragnDrop(event);
    }
    dragnDrop(event) {

        const self = this;

        self.modal.style.position = 'absolute';
        self.modal.style.zIndex = 1000;
        self.modal.style.margin = 0;

        document.body.append(self.modal);

        const pageX = event.pageX || event.targetTouches[0].pageX;
        const pageY = event.pageY || event.targetTouches[0].pageY;

        self.moveAt(pageX, pageY);

        document.context = self;
        document.addEventListener('mousemove', self.onMouseMove);
        document.addEventListener('touchmove', self.onMouseMove);

        self.modal.onmouseup = (event) => this.moveEnd(event);
        self.modal.ontouchend = (event) => this.moveEnd(event);
    }
    moveEnd(event) {
		// console.log(event);
        document.removeEventListener(event.type, this.onMouseMove);
        this.dragged = false;
        this.modal.ontouchend = null;
        document.context = null;
    }
    moveAt(pageX, pageY) {
        this.modal.style.left = pageX - this.shiftX + 'px';
        this.modal.style.top = pageY - this.shiftY + 'px';
    }
    onMouseMove(event) {
        const self = document.context;

        if (!self) return false;

        const pageX = event.pageX || event.targetTouches[0].pageX;
        const pageY = event.pageY || event.targetTouches[0].pageY;

        self.moveAt(pageX, pageY);
    }
}
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
document.body.querySelectorAll('input[type="tel"]').forEach(element => {
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
const enumBgImages = [];

actionHandler.imageAdd = async function (event) {
    const self = this;
    const target = event.target.closest('form');
    const reader = new FileReader();
    const file = event.target.files[0];
    reader.readAsDataURL(file);

    reader.onloadend = async function () {
        const formData = new FormData();
        formData.append('filename', file.name);
        formData.append('image', reader.result);
        const result = await self.apiTalk(event.target, event, 'actionChange', formData);
        target.insertAdjacentHTML('afterend', result.html);
    }
}
actionHandler.imageBackgroundGroup = async function (target, event) {
    if (enumBgImages.length === 0){
        return new Alert({title:'Empty list', text: 'List of files is empty.'});
    }
    const formData = new FormData();
    formData.append('file_ids', JSON.stringify(enumBgImages));
    const result = await this.apiTalk(target, event, 'actionClick', formData);
}
actionHandler.imageDeleteGroup = async function (target, event) {
    if (enumBgImages.length === 0){
        return new Alert({title:'Empty list', text: 'List of files is empty.'});
    }
    const formData = new FormData();
    formData.append('file_ids', JSON.stringify(enumBgImages));
    const result = await this.apiTalk(target, event, 'actionClick', formData);
}
actionHandler.imageToogle = function (event) {
    const value = event.target.value;
    const index = enumBgImages.indexOf(value);
    if (index === -1){
        enumBgImages.push(value)
    }
    else
        enumBgImages.splice(index, 1);
    return true;
}
actionHandler.getLink = function (target) {
    try {
        navigator.clipboard.writeText(target.dataset.link);
        alert('Скопійовано до буферу обміну');
    }
    catch(error){
        if (confirm(`Не вдалось скопіювати до будеру обміну.\nПерейти за посиланням у новому вікні?`)){
            return window.open(target.dataset.link, '_blank');
        }
        return new Alert({ title: "Your link", text: `Your link to this image is:<br><a href="${target.dataset.link}" target="_blank">${target.dataset.link}</a>`});
    }   
}
