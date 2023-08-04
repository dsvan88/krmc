let actionHandler = {
	noticer: null,
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
			alert(`Не существует метода для этого action-type: ${type}... или возникла ошибка. Сообщите администратору!\n${error.name}: ${error.message}`);
			console.log(error);
		}
	},
	clickCommonHandler: function (event) {
		let target = event.target;
		if (!("actionClick" in target.dataset) || !("actionDblclick" in target.dataset)) {
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
	clickFunc: function (target, event, method = 'actionClick') {
		if (!(method in target.dataset)) return false;
		event.preventDefault();

		if ("mode" in target.dataset) {
			if (target.dataset['mode'] === 'location') {
				window.location = target.dataset[method]
				return true;
			}
		}
		const type = camelize(target.dataset[method].replace(/\//g, '-'));
		if (debug) console.log(type);
		if (actionHandler[type] != undefined) {
			try {
				actionHandler[type](target, event);
			} catch (error) {
				alert(`Не существует метода для этого action-click: ${type}... или возникла ошибка. Сообщите администратору!\r\n${error.name}: ${error.message}`);
				console.log(error);
			}
		}
		else {
			let action = target.dataset[method];
			let modal = false;
			if (action.endsWith('/form')) {
				modal = this.commonFormEventStart();
			}

			const formData = new FormData;
			for (let [key, value] of Object.entries(target.dataset)) {
				if (key !== method)
					formData.append(key, value);
			}
			request({
				url: action,
				data: formData,
				success: function (result) {
					if (result["error"] != 0) {
						alert(result["message"]);
						if (modal) {
							actionHandler.commonFormEventEnd({ modal, result });
						}
						return false;
					}
					else {
						if (result["message"]) {
							alert(result["message"]);
						}
						if (result["location"]) {
							window.location = result["location"];
						} else if (result["modal"]) {
							let actionModified = camelize(action.replace(/\//g, '-'));
							actionHandler.commonFormEventEnd({ modal, data: result, formSubmitAction: actionModified + 'Submit' })
							if (actionHandler[actionModified + "Ready"]) {
								setTimeout(() => actionHandler[actionModified + "Ready"]({ modal, data: result }), 10);
							}
						}
					}
				},
			});
		}
	},
	commonFormEventStart: function (event) {
		return new ModalWindow();
	},
	commonFormEventEnd: function ({ modal, data, formSubmitAction, ...args }) {
		let modalWindow;

		if (data['error']) {
			return modalWindow = modal.fill({ html: data['html'], title: 'Error!', buttons: [{ 'text': 'Okay', 'className': 'modal__close positive' }] });
		}
		modalWindow = modal.fill(data);

		if (data["jsFile"]) {
			addScriptFile(data["jsFile"]);
		};

		if (data["cssFile"]) {
			addCssFile(data["cssFile"]);
		};
		if (data['html']) {
			const form = modalWindow.querySelector('form');
			if (form) {
				form.addEventListener('submit',
					(event) =>
						actionHandler[formSubmitAction] ?
							actionHandler[formSubmitAction](event, modal, args)
							:
							this.commonSubmitFormHandler(event, modal, args)
				);
			}
		}

		let fields = modalWindow.querySelectorAll('input[data-action-input]');
		fields.forEach(field => {
			field.addEventListener('input', (event) => actionHandler.inputCommonHandler.call(actionHandler, event))
		});

		return true;
	},
	commonFormEventReady: function ({ modal = null, result = {}, type = null }) {
		/* $(".modal-body input.input_name").autocomplete({
			source: "switcher.php?need=autocomplete_names",
			minLength: 2,
		});
		$(".modal-body .datepick").datetimepicker({ timepicker: false, format: "d.m.Y", dayOfWeekStart: 1 });
		$(".modal-body .timepicker").datetimepicker({ datepicker: false, format: "H:i" }); */
		let firstInput = modal.querySelector("input");
		if (firstInput !== null) {
			firstInput.focus();
		}
		let form = modal.querySelector("form");
		if (form !== null) {
			form.addEventListener("submit", (submitEvent) => {
				submitEvent.preventDefault();
				actionHandler[type](modal);
			});
		}
		if (result["javascript"]) {
			window.eval(result["javascript"]);
		}
	},
	commonSubmitFormHandler: function (event, modal = null, args = null) {
		event.preventDefault();
		let formData = new FormData(event.target);
		const self = this;
		request({
			url: event.target.action.replace(window.location.origin + '/', ''),
			data: formData,
			success: (result) => self.commonResponse.call(self, result),
			error: (result) => self.commonResponse.call(self, result),
		});
		return false;
	},
	commonResponse: function (response) {
		if (response["error"]) {
			new Alert({ title: 'Error!', text: response["message"] });
			return false;
		}
		if (response["message"]) {
			new Alert({ text: response["message"] });
		}
		if (response["notice"] && this.noticer) {
			this.noticer.add(response["notice"]);
			if (response["notice"]["location"]) {
				setTimeout(() => window.location = response["notice"]["location"], 1000);
			}
		}
		if (response["location"]) {
			window.location = response["location"];
		}
	},
	autocompleteInput: function (target) {
		const action = target.dataset.actionInput;
		const type = action.replace(/autocomplete-/, '');
		let formData = new FormData();
		formData.append('command', type);
		formData.append('term', target.value);
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
				deleteOptions.forEach(index => target.list.options[index].remove());
			},
		});
	},
	verification: async function (form, url) {
		const formData = new FormData(form.tagName === 'FORM' ? form : undefined);
		const verification = await request({ url: url, data: formData });

		if (verification['result'] === false) {
			if (verification['message']) new Alert({ text: verification['message'] });
			return false;
		}

		const promise = new Promise((resolve) => {
			new Prompt({
				title: "Verification",
				text: verification['message'],
				value: '',
				action: resolve,
			});
		});

		return promise.then();
	}
};