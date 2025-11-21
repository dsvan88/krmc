const actionHandler = {
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
			return false;
		}
		return true;
	},
	apiTalk: async function (target, event, method, formData = null) {
		const action = target.dataset[method];
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
		const r = await this.request({
			url: action,
			data: formData,
		}, modal);

		return r;
	},
	request: async function (options, modal) {
		if (!options.url) return false;

		const r = await request(options)
		if (modal) this.commonModalEvent(modal, options.url, r);

		this.commonResponse(r);
		return r;
	},
	commonFormEventEnd: function ({ modal, data, action = null, ...args } = {}) {
		let modalWindow;
		const self = this;

		if (data['error']) {
			return modalWindow = modal.fill({ html: data['html'], title: 'Error!', buttons: [{ 'text': 'Okay', 'className': 'modal__close positive' }] });
		}

		const _action = camelize(action.replace(/\//g, '-'));
		let submit = _action + "Submit";

		if (!self[submit])
			submit = 'commonSubmitFormHandler';

		data.context = self;
		data.submit = self[submit];

		modalWindow = modal.fill(data);

		if (self[_action + "Ready"])
			self[_action + "Ready"]({ modal, data: data });

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

		if (data["jsFile"]) {
			addScriptFile(data["jsFile"]);
		};

		if (data["cssFile"]) {
			addCssFile(data["cssFile"]);
		};

		setTimeout(() => self.commonFormEventEnd({ modal, data, action }), 100);
	},
	commonResponse: function (response, modal = null) {
		const self = this;
		if (response["error"]) {
			new Alert({ title: 'Error!', text: response["message"] });
			return false;
		}
		if (response["message"]) {
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

		return await promise.then();
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
	handleEvents: function (t) {
		const s = this;
		t.querySelectorAll('input[data-action-input]').forEach(i =>
			i.addEventListener('input', (e) => s.inputCommonHandler.call(s, e))
		);
		t.querySelectorAll('input[data-action-change]').forEach(i =>
			i.addEventListener('change', (e) => s.changeCommonHandler.call(s, e))
		);
		t.querySelectorAll('form[data-action-submit]').forEach(i =>
			i.addEventListener('submit', (e) => s.commonSubmitFormHandler.call(s, e))
		);
		t.querySelectorAll('input[type="tel"]').forEach(i => {
			i.addEventListener('focus', (e) => s.phoneInputFocus.call(s, e));
			i.addEventListener('input', (e) => s.phoneInputFormat.call(s, e), false);
		});
		t.querySelectorAll('details[data-action-open],details[data-action-close]').forEach(i =>
			i.addEventListener('toggle', (e) => s.commonToggleHandler.call(s, e), false)
		);
	}
};
