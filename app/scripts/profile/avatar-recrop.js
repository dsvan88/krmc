import { Cropper } from "./plugins/cropper.js";

actionHandler.accountProfileAvatarRecropForm = async function (target){
    const modal = target.closest('.modal');
    console.log(modal);
    const cropper = new Cropper('#image_cropper');
    console.log(cropper);
}