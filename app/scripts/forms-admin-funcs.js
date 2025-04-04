actionHandler.commonFormSubmit = function (event) {
    event.preventDefault();
    const url = event.target.action.slice(window.location.length);
	const formData = new FormData(event.target);
	const self = actionHandler;
	if (window.CKEDITOR){
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

let commonForm = document.body.querySelector('.common-form .common-form__form');
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
						'instances' : {}
					};
				}
				window.CKEDITOR.instances[randomIndex] = editor;
			}
		)
	}
}

actionHandler.mainImageChange = function (event) {
	let file = event.target.files[0];
	let img = createNewElement({
		tag: 'img',
		style: 'height:10vh;width:auto',
		src: URL.createObjectURL(file)
	});
	let imgPlace = document.body.querySelector('#main-image-place');
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
