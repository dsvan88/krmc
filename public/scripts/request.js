async function request({ url, data, method = 'POST', responseType = 'json', success, error, ...args }) {

	if (error == undefined) {
		error = function (result) {
			console.log(`Error: Ошибка связи с сервером ${result}`);
		};
	}

    if (debug) {
        if (success){
            success = catchResult(success);
        }
		error = catchResult(error);
    }

	if (!data) method = 'GET';

	headers = {};

	if (method === 'GET') {
        if (data){
            url += '?' + btoa(new URLSearchParams(data).toString());
            data = undefined;
        }
		headers['Content-Type'] = 'application/x-www-form-urlencoded';
    }
    else if (typeof data === 'string' && data[0] === '{'){
        headers['Content-Type'] = 'application/json';
    }
    else if (typeof data === 'string' && data[0] === '{'){
        headers['Content-Type'] = 'multipart/form-data';
    }

    let options = {
        method: method,
        body: data,
        headers: headers,
    }
    if (url[0] === '/') {
        url = url.substr(1);
    }
    if (!url.includes('://')){
        url = '/api/' + url;
    }

    try {
		const response = await fetch(url, options);

		if (response.ok) {
			
			let description = response.headers.get('content-description');
			if (description && description  === "File Transfer"){
				let filename = response.headers.get("content-disposition").replace(/^.*?=/, '').slice(1,-1);
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
            if (success){
                success(result);
            }
            return result;
		}
		error(response.status);
    } catch (throwed) {
        error(throwed);
	}
}

function download(dataurl, filename='backup.txt') {
	let a = document.createElement("a");
	a.href = dataurl;
	a.setAttribute("download", filename);
	a.click();
	return true;
}