<section class='section common-form'>
    <form action="/page/edit/<?= $page['id'] ?>" method="post" enctype="multipart/form-data" class="common-form__form">
        <h2 class="common-form__title"><?= $title ?></h2>
        <div class="common-form__columns">
            <div class="common-form__column">
                <div class="common-form__row"><input type="text" name="title" value="<?= $page['title'] ?>" class="common-form__input title" placeholder="Title"></div>
                <div class="common-form__row"><input type="text" name="subtitle" value="<?= $page['subtitle'] ?>" class="common-form__input subtitle" placeholder="Subtitle"></div>
            </div>
            <div class="common-form__column">
                <div class="common-form__row"><label class="common-form__label" for="keywords">Ключові слова:</label><input type="text" id="keywords" name="keywords" value="<?= $page['keywords'] ?>" class="common-form__input" placeholder="Keywords"></div>
                <div class="common-form__row"><label class="common-form__label" for="published_at">Дата публікації:</label><input type="datetime-local" id="published_at" name="published_at" value="<?=$page['published_at']?>" class="common-form__input" placeholder="Дата публікації"></div>
                <div class="common-form__row"><label class="common-form__label" for="expired_at">Дійсна до:</label><input type="datetime-local" id="expired_at" name="expired_at" value="<?=$page['expired_at']?>" class="common-form__input" placeholder="Дійсна до"></div>
            </div>
        </div>
        <div class="editor-block">
            <div class="toolbar-container"></div>
            <div class="content-container">
                <div class="editor"><?= $page['html'] ?></div>
            </div>
        </div>
        <div class="common-form__button-place">
            <button type="submit" class="common-form__button"><?= $texts['SubmitLabel'] ?></button>
        </div>
    </form>
</section>