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

function getFormDataFromDataset(t) {
	const data = new FormData();
	for (const [key, value] of Object.entries(t.dataset)) {
		if (key.startsWith('action')) continue;
		data.append(key, value);
	}
	return data;
}
function simpleObjectToFormData(obj) {
	let formData = new FormData();
	for (let item in obj) {
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
			else {
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

function addScriptFile(src, callback = '') {
	if (Array.isArray(src)) {
		let result = false;
		for (let index = 0; index < src.length; index++) {
			result = addScriptFile(src[index], callback)
		}
		return result;
	}

	if (document.head.querySelector(`script[src="${src}"]`)) {
		return false;
	}
	const script = document.createElement('script');
	script.src = src;
	script.async = true;
	script.type = 'module';
	document.head.appendChild(script);
	if (callback !== '')
		script.onload = callback;
	return true;
}
function addCssFile(src) {
	if (Array.isArray(src)) {
		for (let index = 0; index < src.length; index++) {
			addCssFile(src[index])
		}
	}
	else {
		if (document.head.querySelector(`link[href="${src}"]`)) {
			return false;
		}
		const link = document.createElement('link');
		link.rel = 'stylesheet';
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
			if (!object[key]) {
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
						'instances': {}
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

async function blobToBase64(b) {
	return await new Promise(r => {
		const reader = new FileReader();
		reader.onload = function () {
			r(reader.result);
		};
		reader.readAsDataURL(b);
	}).then();
}