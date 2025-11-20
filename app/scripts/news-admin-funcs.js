actionHandler.newsFormSubmit = function (event) {
    event.preventDefault();
    let url = event.target.action.slice(window.location.length);
	let formData = new FormData(event.target);
	let newHTML = CKEDITOR.instances[event.target.querySelector("div.editor-block").id].getData();
	formData.append('html', newHTML);
    request({
        url: url,
        data: formData,
        success: actionHandler.commonResponse,
    });
}

actionHandler.newsMainImageChange = function (event) {
	let file = event.target.files[0];
	let img = createNewElement({
		tag: 'img',
		style: 'height:40vh;width:auto',
		src: URL.createObjectURL(file)
	});
	let imgPlace = document.body.querySelector('#news-main-image');
	imgPlace.innerHTML ='';
	imgPlace.append(img)

	let reader = new FileReader();
    reader.onload = applyNewImage(img);
	reader.readAsDataURL(file);
	
	function applyNewImage(img) {
		return function (e) {
			img.src = e.target.result;
			document.body.querySelector('input[name="main-image"]').value = e.target.result;
		};
	}
}

let formNews = document.body.querySelector('.news form.news__form');
if (formNews) {
    formNews.onsubmit = actionHandler.newsFormSubmit;
}

let textareas = document.body.querySelectorAll('div.editor-block');
if (textareas.length > 0) {
    CKEditorApply(textareas);
}

