actionHandler.accountProfileAvatarFormReady = function ({ modal, data }) {
	
	let input = createNewElement({
		tag: 'input',
		type: 'file',
		name: 'avatarImage',
		style: 'display:none',
		accept: 'image/*'

	});
	modal.modal.appendChild(input);
	input.click();
	input.onchange = (event) => actionHandler.accountProfileAvatarChange(event, modal);
}
actionHandler.accountProfileAvatarChange = function (event, modal) {
	
	let form = createNewElement({
		tag: 'form',
		className: 'common-form',
		method: 'POST',
		action: '',
	});
	
	let divRow = createNewElement({
		tag: 'div',
		className: 'common-form__row'
	});
	let divImagePlace = createNewElement({
		tag: 'div',
		className: 'cropped-image-place',
		style: 'max-height:60vh;max-width:50vw;',
	});
	let img = createNewElement({
		tag: 'img',
		className: 'avatar-image',
		style: 'height:100%;width: auto',
		src: URL.createObjectURL(event.target.files[0])
	});
	divImagePlace.appendChild(img);
	divRow.appendChild(divImagePlace);
	form.appendChild(divRow);

	let divButtonsRow = createNewElement({
		tag: 'div',
		className: 'modal-buttons'
	});
	let buttonAgree = createNewElement({
		tag: 'button',
		type: 'submit',
		className: 'positive',
		innerText: 'Затвердити'
	});
	let buttonCancel = createNewElement({
		tag: 'button',
		type: 'button',
		className: 'modal__close negative',
		innerText: 'Відміна'
	});
	divButtonsRow.appendChild(buttonAgree);
	divButtonsRow.appendChild(buttonCancel);
	form.appendChild(divButtonsRow);

	modal.clearModalContent();

	modal.modal.querySelector('.modal-container').appendChild(form);
	$(img).cropper({
		aspectRatio: 3.5 / 4,
		minContainerWidth: 325,
		minContainerHeight: 220,
		checkOrientation: false,
	});
	form.addEventListener('submit', (event) => actionHandler.profileCropImageSubmit(event, modal, img))
}

actionHandler.profileCropImageSubmit = function (event, modal, img) {
	event.preventDefault();
	let canvasData = $(img).cropper("getData", true);
	let canvasUrl = $(img).cropper("getCroppedCanvas", { maxWidth: 4096, maxHeight: 4096 }).toDataURL('image/jpeg', 1.0);
	modal.modal.querySelector('.modal__close').click();
	let image = new Image();
	image.src = canvasUrl;
	let hiddenInput = createNewElement({
		tag: 'input',
		type: 'hidden',
		name: 'image',
		value: canvasUrl,
	});
	let profileAvatarPlace = document.body.querySelector('.modal-container .profile__avatar-place');
	profileAvatarPlace.innerHTML = '';
	profileAvatarPlace.appendChild(image);
	profileAvatarPlace.appendChild(hiddenInput);
}
