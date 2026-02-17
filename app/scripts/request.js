"use strict"

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