const actionHandler = {
	noticer: null,
	phoneMask: "+38 (0__) ___-__-__",
	dblClickTimer: null,
	inputCommonHandler: function (event) {
		let action = event.target.dataset.actionInput;
		if (!action.startsWith('autocomplete-') || event.target.value.length <= 2) return false;
		this.autocompleteInput(event.target);
	},
	changeCommonHandler: function (event) {
		const type = camelize(event.target.dataset.actionChange);

		if (debug) console.log(type);

		this[type](event);
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
		this[method](event);
	},
	clickCommonHandler: function (e) {
		const target = e.target.closest('[data-action-click],[data-action-dblclick]');
		if (!target) return;

		const { actionClick, actionDblclick } = target.dataset;

		if (actionDblclick) {
			if (this.dblClickTimer) {
				clearTimeout(this.dblClickTimer);
				this.dblClickTimer = null;
				return this.clickFunc(target, e, 'actionDblclick');
			}
			return this.dblClickTimer = setTimeout(() => {
				this.dblClickTimer = null;
				if (actionClick) this.clickFunc(target, e);
			}, 250)
		}
		this.clickFunc(target, e);
	},
	clickFunc: async function (target, event, method = 'actionClick') {

		if (!(method in target.dataset)) return false;
		event.preventDefault();

		if ("mode" in target.dataset) {
			if (target.dataset['mode'] === 'location') {
				window.location = target.dataset[method]
				return true;
			}
		}
		let type = camelize(target.dataset[method]);
		if (debug) console.log(type);

		if (this[type])
			return this[type];
		return this.apiTalk(target, method);

	},
	apiTalk: async function (target, method, formData = null) {
		const action = target.dataset[method];
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
			if (target.dataset.verification === 'confirm' && !confirm('Are you sure?'))
				return false;
			else {
				const input = { type: 'text' };
				if (/(root|pass)/.test(target.dataset.verification))
					input.type = 'password';
				const verification = await this.verification(null, target.dataset.verification, input);

				if (!verification) return false;

				formData.append('verification', verification);
			}
		}

		if (action.endsWith('/form')) {
			const _action = camelize(action.replace(/\//g, '-'));
			const submit = this[_action + "Submit"] ?? this.commonSubmitFormHandler;
			const ready = this[_action + "Ready"] ?? this.commonFormEventReady;

			const r = await ModalWindow.create({
				url: action,
				data: formData,
				submit: submit.bind(this),
				error: this.commonResponse.bind(this),
				ready: ready.bind(this),
			});

			if (!r.modal) return this.commonResponse(r);

			this.handleEvents(r.content);
			return true;
		}

		const r = await this.request({
			url: action,
			data: formData,
		});

		return r;
	},
	request: async function (options) {
		if (!options.url) return false;

		const r = await request(options)

		this.commonResponse(r);
		return r;
	},
	commonFormEventReady: async function (modal = null) {
		if (!modal)
			throw Error("Modal is empty");
		const input = modal.content.querySelector("input");
		if (input) {
			input.focus();
		}
	},
	commonSubmitFormHandler: async function (event, formData = null) {
		event.preventDefault();
		if (!formData) formData = new FormData(event.target);

		const blocks = event.target.querySelectorAll('div.block');
		const len = blocks.length;
		for (let i = 0; i < len; i++) {
			const blockId = blocks[i].id.substring(6);
			const block = { type: blocks[i].dataset.blockType };
			if (blocks[i].dataset.field) {
				formData.append(blocks[i].dataset.field, window.CKEDITOR.instances[blockId].getData());
				continue;
			}

			block.title = blocks[i].querySelector('div.block__title input.form__input').value;
			if (window.CKEDITOR.instances[blockId])
				block.html = window.CKEDITOR.instances[blockId].getData();
			const image = blocks[i].querySelector('input[name^="image_id"]');
			if (image)
				block.image = image.value;
			formData.append('blocks[]', JSON.stringify(block));
		}

		let url = event.target.dataset.actionSubmit ?? event.target.action ?? '/'
		// console.log(url);
		// let url = event.target.dataset.actionSubmit ?? event.target.action.replace(window.location.origin + '/', '')
		// if (!event.target.dataset.actionSubmit){
		// 	url = window.location.origin+'/'+url;
		// }

		return await this.request({
			url: url,
			data: formData
		});
	},
	commonResponse: function (response) {
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

		let els = t.querySelectorAll('input[data-action-input]');
		let len = els.length;
		if (len > 0) {
			for (let x = 0; x < len; x++) {
				els[x].addEventListener('input', this.inputCommonHandler.bind(this));
			}
		}
		els = t.querySelectorAll('input[data-action-change]');
		len = els.length;
		if (len > 0) {
			for (let x = 0; x < len; x++) {
				els[x].addEventListener('change', this.changeCommonHandler.bind(this));
			}
		}
		els = t.querySelectorAll('input[type="tel"]');
		len = els.length;
		if (len > 0) {
			for (let x = 0; x < len; x++) {
				els[x].addEventListener('focus', this.phoneInputFocus.bind(this));
				els[x].addEventListener('input', this.phoneInputFormat.bind(this));
			}
		}
		els = t.querySelectorAll('details[data-action-open],details[data-action-close]');
		len = els.length;
		if (len > 0) {
			for (let x = 0; x < len; x++) {
				els[x].addEventListener('toggle', this.commonToggleHandler.bind(this));
			}
		}
	}
};