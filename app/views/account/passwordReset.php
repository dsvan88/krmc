<section class='section common-form'>
    <form action="/account/password-reset/<?= $hash ?>" method="post" class="common-form__form">
        <h2 class="common-form__title"><?= $title ?></h2>
        <div><input type="password" name="password" value="" class="common-form__input" placeholder="Password" required></div>
        <div><input type="password" name="check" value="" class="common-form__input" placeholder="Password Again" required></div>
        <div class="common-form__button-place"><button type="submit" class="common-form__button"><?= $texts['SubmitLabel'] ?></button></div>
    </form>
</section>