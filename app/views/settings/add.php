<section class='section form'>
    <form action="/settings/add" method="post" enctype="multipart/form-data" class="form__form">
        <h2 class="form__title"><?= $title ?></h2>
        <div><input type="text" name="type" value="" class="form__input subtitle" placeholder="Тип"></div>
        <div><input type="text" name="name" value="" class="form__input subtitle" placeholder="Назва"></div>
        <div><input type="text" name="value" value="" class="form__input subtitle" placeholder="Значення"></div>
        <div class="form__button-place"><button type="submit" class="form__button"><?= $texts['SubmitLabel'] ?></button></div>
    </form>
</section>