// actionHandler.commonFormSubmit = async function (event) {
// 	event.preventDefault();
// 	const url = event.target.action.slice(window.location.length);
// 	const formData = new FormData(event.target);
// 	const self = actionHandler;
// 	if (window.CKEDITOR) {
// 		const EditorsBlocks = event.target.querySelectorAll("div.editor-block");
// 		EditorsBlocks.forEach(block => {
// 			formData.append(block.dataset.field, CKEDITOR.instances[block.id].getData());
// 		})
// 	}
// 	return await this.request({
// 		url: url,
// 		data: formData,
// 	});
// }

// const commonForm = document.body.querySelector('.form .form__form');
// if (commonForm) {
// 	commonForm.onsubmit = actionHandler.commonFormSubmit;
// }

const editorsBlocks = document.body.querySelectorAll('div.editor-block');
if (editorsBlocks.length > 0) {
	CKEditorApply(editorsBlocks);
}

actionHandler.mainImageChange = function (event) {
	const file = event.target.files[0];
	const img = createNewElement({
		tag: 'img',
		style: 'height:10vh;width:auto',
		src: URL.createObjectURL(file)
	});
	const imgPlace = document.body.querySelector('#main-image-place');
	imgPlace.innerHTML = '';
	imgPlace.append(img)

	const reader = new FileReader();
	reader.onload = applyNewImage(img);
	reader.readAsDataURL(file);

	function applyNewImage(img) {
		return function (e) {
			img.src = e.target.result;
			document.body.querySelector('input[name="main-image"]').value = e.target.result;
		};
	}
}

actionHandler.formImageChange = function (event) {
	const file = event.target.files[0];
	const parent = event.target.closest('.image__container');
	const img = parent.querySelector('.image__img');
	img.src = URL.createObjectURL(file);

	const reader = new FileReader();
	reader.onload = (e) => {
		parent.querySelector('input[name="image"]').value = e.target.result;
		parent.querySelector('input[name="filename"]').value = file.name;
	};
	reader.readAsDataURL(file);
}

actionHandler.formsImageUpdate = function (target, image = []) {
	const parent = target.closest('.image__container');
	const img = parent.querySelector('.image__img');
	img.src = image[0]['thumbnailLink'];

	parent.querySelector('input[name^="image_link"]').value = image[0]['thumbnailLink'];
	parent.querySelector('input[name^="image_id"]').value = image[0]['id'];
}
actionHandler.formsImagesList = async function (target) {

	if (target.classList.contains('blocked')) return false;

	target.classList.add('blocked');

	const image = await imagesPad({ urlGet: target.dataset.actionClick });

	target.classList.remove('blocked');

	if (!image) return false;

	this.formsImageUpdate(target, JSON.parse(image));

	return true;
}
actionHandler.settingsEdit = async function (target, event) {
	const value = target.innerText;
	const newValue = await customPrompt({
		title: 'Set a value',
		text: `Setting's name:\n<u>${target.dataset.name}</u>`,
		value: value
	});

	if (newValue === false || newValue === value) return false;

	const formData = new FormData();
	formData.append('type', target.dataset.type);
	formData.append('slug', target.dataset.slug);
	formData.append('value', newValue);

	return await this.apiTalk(target, event, 'actionDblclick', formData);
}

actionHandler.pagesAddBlock = async function (target, event){
	const parent = target.closest('div.form__add-block');

	if (!parent) return false;

	const result = await this.request({url: target.dataset.actionClick});
	
	parent.insertAdjacentHTML('beforebegin', result.html);

	const editorsBlocks = document.body.querySelectorAll('div.editor-block:not([id])');
	if (editorsBlocks.length > 0) {
		CKEditorApply(editorsBlocks);
	}
}

actionHandler.pagesSetBlockType = async function (target){
	const parent = target.closest('div[data-block-type]');
	
	if (!parent) return false;
	
	const block = {};
	if (window.CKEDITOR.instances[parent.id?.substring(6)]){
		block.html = window.CKEDITOR.instances[parent.id.substring(6)].getData();
	}
	const imageContainer = parent.querySelector('div.image__container');
	if (imageContainer){
		block.image = {
			'link': imageContainer.querySelector('img').src,
			'imageId': imageContainer.querySelector('input[name^=image_id]').value,
		}
	}
	
	const fd = new FormData()
	fd.append('blockType',  target.dataset.blockType);
	const response = await this.request({url: target.dataset.actionClick, data: fd });
	
	CKEditorRemove(parent.id.substring(6));

	const parser = new DOMParser();
	const doc = parser.parseFromString(response.html, 'text/html');
	const newBlock = doc.querySelector('div[data-block-type]');

	if (newBlock) {
		parent.replaceWith(newBlock);
	} else {
		console.error('Новый блок не найден в ответе сервера');
		return false;
	}
	
	const editor = newBlock.querySelector('div.editor');
	console.log(block);
	if (editor && block.html){
		editor.innerHTML = block.html;
	}
	const imageContainerNew = newBlock.querySelector('div.image__container');
	if (imageContainerNew && block.image){
		imageContainerNew.querySelector('img.image__img').src = block.image.link;
		imageContainerNew.querySelector('input[name^=image_id]').value = block.image.imageId;
		imageContainerNew.querySelector('input[name^=image_link]').value = block.image.link;
	}

	const editors = [...newBlock.querySelectorAll('.editor-block')]
        .filter(el => el.classList?.contains('editor-block') && !el.id);

    if (editors.length > 0) {
        CKEditorApply(editors);
    }
}