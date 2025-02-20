
actionHandler.newImage = function (event) {
    console.log(event);
    let file = event.target.files[0];
    // let img = createNewElement({
    //     tag: 'img',
    //     style: 'height:40vh;width:auto',
    //     src: URL.createObjectURL(file)
    // });
    // let imgPlace = document.body.querySelector('#news-main-image');
    // imgPlace.innerHTML = '';
    // imgPlace.append(img)

    // let reader = new FileReader();
    // reader.onload = applyNewImage(img);
    // reader.readAsDataURL(file);

    // function applyNewImage(img) {
    //     return function (e) {
    //         img.src = e.target.result;
    //         document.body.querySelector('input[name="main-image"]').value = e.target.result;
    //     };
    // }
}