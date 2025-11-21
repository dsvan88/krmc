'use strict'

import { Cropper } from "./plugins/cropper.js";

actionHandler.accountAvatarEditFormReady = async function ({ modal }) {
    const image = modal.content.querySelector('img[id^=image_cropper_]');
    image.crossOrigin = 'anonymous';
    image.style.height = '20vh';
    image.style.width = 'auto';
    new Cropper(modal.content.querySelector('img[id^=image_cropper_]'));
    const cropperCanvas = modal.content.querySelector('cropper-canvas');
    cropperCanvas.style.minHeight = '40vh';
    cropperCanvas.style.minWidth = '40vw';
    modal.content.querySelector('cropper-selection').aspectRatio = 1;
}
actionHandler.newAvatarInputChange = async function (e) {
    const modal = e.target.closest('.modal');
    const file = (e.target.files || e.dataTransfer.files)[0];

    const cropperImage = modal.querySelector('cropper-image');
    cropperImage.src = URL.createObjectURL(file);
}

actionHandler.accountAvatarEditFormSubmit = async function (e) {
    const s = this;
    const cropperSelection = e.target.querySelector('cropper-selection');
    const result = await cropperSelection.$toCanvas();
    result.toBlob(
        (blob) => s.avatarNew.call(s, e.target, blob),
        'image/jpeg',
        1
    );
    return false;    
}
actionHandler.avatarNew = async function(f, b){
    const b64 =  await blobToBase64(b);
    const fd = new FormData(f);
    fd.append('image', b64);
    const r = this.request({url: "account/avatar/new", data: fd});
}