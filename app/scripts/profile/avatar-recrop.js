'use strict'

import { Cropper } from "./plugins/cropper.js";

actionHandler.accountAvatarEditFormReady = async function ({ modal }) {
    const image = modal.content.querySelector('img[id^=image_cropper_]');
    image.crossOrigin = 'anonymous';
    image.style.height = '20vh';
    image.style.width = 'auto';

    new Cropper(modal.content.querySelector('img[id^=image_cropper_]'));

    const cropperCanvas = modal.content.querySelector('cropper-canvas');
    cropperCanvas.style.minHeight = '70vh';
    cropperCanvas.style.minWidth = '70vw';
    modal.content.querySelector('cropper-selection').aspectRatio = 3 / 4;
    modal.content.querySelector('cropper-selection').height = '70vh';
}
actionHandler.newAvatarInputChange = async function (e) {
    const modal = e.target.closest('.modal');
    const file = (e.target.files || e.dataTransfer.files)[0];

    const cropperImage = modal.querySelector('cropper-image');
    cropperImage.src = URL.createObjectURL(file);
}

actionHandler.accountAvatarEditFormSubmit = async function (e, fd, m) {
    const s = this;
    const cropperSelection = e.target.querySelector('cropper-selection');
    const result = await cropperSelection.$toCanvas();
    result.toBlob(
        async (blob) => s.avatarNew.call(s, { f: e.target, fd: fd, b: blob, m: m }), //f - form, fd - FormData, b - blob, m - Modal Window;
        'image/jpeg',
        1
    );
    return false;
}
actionHandler.avatarNew = async function ({ f = null, fd = null, b = null, m = null } = {}) {
    const b64 = await blobToBase64(b);
    fd.append('image', b64);
    const r = await this.request({ url: "account/avatar/new", data: fd });

    if (r.notice.type)
        return m.unpause();

    m.close();
}