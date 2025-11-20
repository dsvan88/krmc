'use strict'

import { Cropper } from "./plugins/cropper.js";

actionHandler.accountAvatarEditFormReady = async function ({ modal }) {
    const cropper = new Cropper(modal.content.querySelector('img[id^=image_cropper_]'), {
        aspectRatio: 4 / 3,
        viewMode: 1,
        autoCropArea: 0.8,
        dragMode: 'move',
        crop(event) {
            console.log(event.detail.x);
            console.log(event.detail.y);
            console.log(event.detail.width);
            console.log(event.detail.height);
        }
    });
    const cropperSelection = modal.content.querySelector('cropper-selection');
    // cropperSelection.height = "40vh";
    // cropperSelection.width = "30vh";
    // console.log(cropper.crop);
    // console.log(cropperSelection);
    const cropperImage = modal.content.querySelector('cropper-image');
    cropperImage.$zoom(3);
    cropperImage.style.width = "auto";
    // console.log(cropperImage);

}
actionHandler.newAvatarInputChange = async function (e) {
    const modal = e.target.closest('.modal');
    const file = (e.target.files || e.dataTransfer.files)[0];

    const cropperImage = modal.querySelector('cropper-image');
    cropperImage.src = URL.createObjectURL(file);
}