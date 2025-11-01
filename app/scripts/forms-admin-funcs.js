actionHandler.commonFormSubmit = function (event) {
	event.preventDefault();
	const url = event.target.action.slice(window.location.length);
	const formData = new FormData(event.target);
	const self = actionHandler;
	if (window.CKEDITOR) {
		const EditorsBlocks = event.target.querySelectorAll("div.editor-block");
		EditorsBlocks.forEach(block => {
			formData.append(block.dataset.field, CKEDITOR.instances[block.id].getData());
		})
	}
	request({
		url: url,
		data: formData,
		success: (response) => self.commonResponse.call(self, response),
	});
}

let commonForm = document.body.querySelector('.form .form__form');
if (commonForm) {
	commonForm.onsubmit = actionHandler.commonFormSubmit;
}

let editorsBlocks = document.body.querySelectorAll('div.editor-block');
if (editorsBlocks.length > 0) {
	CKEditorApply(editorsBlocks);
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

	parent.querySelector('input[name="image_link"]').value = image[0]['thumbnailLink'];
	parent.querySelector('input[name="image_id"]').value = image[0]['id'];
}
actionHandler.formsImagesList = async function (target, event) {

	if (target.classList.contains('blocked')) return false;

	target.classList.add('blocked');

	const image = await imagesPad({ urlGet: target.dataset.actionClick });

	target.classList.remove('blocked');

	if (!image) return false;

	this.formsImageUpdate(target, JSON.parse(image));

	return true;
}