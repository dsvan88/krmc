let debug = false;
actionHandler = {
	inputCommonHandler: function (event) {
		let action = event.target.dataset.actionInput;
		if (action.startsWith('autocomplete-')) {
			if (event.target.value.length > 2) {
				const type = action.replace(/autocomplete-/, '');
				let formData = new FormData();
				formData.append('command', type);
				formData.append('term', event.target.value);
				postAjax({
					url: 'autocomplete/'+type,
					data: formData,
					successFunc: function (result) {
						if (result) {
							let options = [];
							const deleteOptions = [];
							for (let i = 0; i < event.target.list.options.length; i++) {
								options.push(event.target.list.options[i].value);
								if (!result['result'].includes(event.target.list.options[i].value)){
									deleteOptions.push(i);
								}
							}
							result['result'].map(item => {
								if (options.includes(item)) return;
								const option = document.createElement('option');
								option.value = item;
								event.target.list.appendChild(option);
							});
							deleteOptions.map(index => event.target.list.options[index].remove());
						}
					},
				});
			}
		}
	},
	changeCommonHandler: function (event) {
		const type = camelize(event.target.dataset.actionChange);
		if (debug) console.log(type);
		try {
			actionHandler[type](event);
		} catch (error) {
			alert(`Не существует метода для этого action-type: ${type}... или возникла ошибка. Сообщите администратору!\r\n${error.name}: ${error.message}`);
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
				// actionHandler.clickFunc(target, event);
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
		else{
			actionHandler.clickFunc(target, event);
		}
	},
/* 	dblClickFunc: function (target, event) {
		event.preventDefault();
		const action = camelize(target.dataset.actionDblclick);
		if (debug) console.log(action);
		try {
			actionHandler[action]({ event });
		} catch (error) {
			alert(`Не существует метода для этого dblclick-action: ${action}... или возникла ошибка. Сообщите администратору!\r\n${error.name}: ${error.message}`);
			console.log(error);
		}
	}, */
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
			postAjax({
				url: action,
				data: formData,
				successFunc: function (result) {
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
						if (result["location"]){
							window.location =  result["location"];
						} else if (result["modal"]) {
							let actionModified = camelize(action.replace(/\//g, '-'));
							actionHandler.commonFormEventEnd({ modal, data: result, formSubmitAction: actionModified + 'Submit' })

							setTimeout(() => {
								if (actionHandler[actionModified + "Ready"]) {
									actionHandler[actionModified + "Ready"]({ modal, data: result });
								}
							}, 10);
							
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
		if (data['error'] === 0){
			modalWindow = modal.fillModalContent(data);
		}else{
			modalWindow = modal.fillModalContent({ html: data['html'], title: 'Error!', buttons: [{ 'text': 'Okay', 'className': 'modal-close positive' }] });
		};
		
		if (data["jsFile"]) {
			addScriptFile(data["jsFile"]);
		};
		
		if (data["cssFile"]) {
			addCssFile(data["cssFile"]);
		};		
		
		if (data['html']) {
			const form = modalWindow.querySelector('form');
			if (form !== null){
				if (actionHandler[formSubmitAction]) {
					form.addEventListener('submit', (event) => actionHandler[formSubmitAction](event, modal, args))
				}
				else {
					form.addEventListener('submit', (event) => this.commonSubmitFormHandler({ event, modal, args }))
				}
			}
		}
		return true;
	},
	commonFormEventReady: function ({ modal = null, result = {}, type = null}) {
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
	commonSubmitFormHandler: function ({ event, modal, args }) {
		event.preventDefault();
		let formData = new FormData(event.target);
		postAjax({
			url: event.target.action.replace(window.location.origin+'/', ''),
			data: formData,
			successFunc: function (result) {
				if (result["error"]) {
					alert(result["message"]);
					return false;
				}
				if (result["message"]) {
					alert(result["message"]);
				}
				if (result["location"]){
					window.location =  result["location"];
				}
			},
		});
		return false;
	}
};
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

async function postAjax({ url, data, formData, successFunc, errorFunc, method = 'json', ...options }) {
	if (successFunc == undefined) {
		successFunc = function (result) {
			console.log("Not set `successFunc`. Ajax result: " + result);
			alert("Успешно!");
		};
	}
	if (errorFunc == undefined) {
		errorFunc = function (result) {
			console.log(`Error: Ошибка связи с сервером ${result}`);
			alert("Error: Ошибка связи с сервером");
		};
	}

	if (debug) {
		successFunc = catchResult(successFunc);
		errorFunc = catchResult(errorFunc);
	}
	try {
		// console.log(typeof data == 'string' ? 'application/json' : 'multipart/form-data');
		$options = {
			method: 'POST', // или 'PUT'
			body: data, // данные могут быть 'строкой' или {объектом}!
		};
		if (typeof data == 'string'){
			$options['headers'] = {
				'Content-Type': 'application/json'
			}
		};
		if (url[0] === '/') {
			url = url.substr(1);
		}
		if (!url.includes('://'))
			url = '/api/' + url;
		const response = await fetch(url, $options);
		// 	headers: {
		// 		'Content-Type': typeof data == 'string' ? 'application/json' : 'multipart/form-data'
		// 	}
		// });
		if (response.ok) {
			let description = response.headers.get('content-description');
			if (description && description  === "File Transfer"){
				let filename = response.headers.get("content-disposition").replace(/^.*?=/, '').slice(1,-1);
				let blob = await response.blob();
				let dataUrl = URL.createObjectURL(blob)
				download(dataUrl, filename);
				return true;
			}
			successFunc(await response[method]());
		}
		else {
			errorFunc(response.status);
		}
	} catch (error) {
		console.error('Ошибка:', error);
	}
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


function download(dataurl, filename='backup.txt') {
	let a = document.createElement("a");
	a.href = dataurl;
	a.setAttribute("download", filename);
	a.click();
	return true;
}