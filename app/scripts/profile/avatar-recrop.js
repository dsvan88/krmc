'use strict'

import { Cropper } from "./plugins/cropper.js";

actionHandler.accountAvatarEditFormReady = async function ({ modal }) {
    console.log(modal.modal.querySelector('#image_cropper'));
    const cropper = new Cropper(modal.modal.querySelector('#image_cropper'));
}