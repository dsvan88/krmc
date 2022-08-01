<section class='section common-form'>
    <form action="/settings/add" method="post" enctype="multipart/form-data" class="common-form__form">
        <h2 class="common-form__title"><?= $title ?></h2>
        <div><input type="text" name="type" value="" class="common-form__input subtitle" placeholder="Тип"></div>
        <div><input type="text" name="name" value="" class="common-form__input subtitle" placeholder="Назва"></div>
        <div><input type="text" name="value" value="" class="common-form__input subtitle" placeholder="Значення"></div>
        <div class="common-form__button-place"><button type="submit" class="common-form__button"><?= $texts['SubmitLabel'] ?></button></div>
    </form>
</section>