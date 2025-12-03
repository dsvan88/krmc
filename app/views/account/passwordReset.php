<section class='section form'>
    <form action="/account/password-reset/<?= $hash ?>" method="post" class="form__form">
        <h2 class="form__title"><?= $title ?></h2>
        <div><input type="password" name="password" value="" class="form__input" placeholder="Password" required></div>
        <div><input type="password" name="check" value="" class="form__input" placeholder="Password Again" required></div>
        <div class="form__button-place"><button type="submit" class="form__button"><?= $texts['SubmitLabel'] ?></button></div>
    </form>
</section>