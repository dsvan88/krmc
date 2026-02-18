<section class='section form'>
    <form action="/page/edit/<?= $page['slug'] ?>" method="post" enctype="multipart/form-data" class="form__form" data-action-submit="/page/edit/<?= $page['slug'] ?>">
        <h2 class="form__title"><?= $title ?></h2>
        <div class="form__columns">
            <div class="form__column">
                <div class="form__row">
                    <input type="hidden" name="type" value="<?= $page['type'] ?>">
                    <input type="text" name="title" value="<?= $page['title'] ?>" class="form__input" placeholder="Title">
                </div>
                <div class="form__row">
                    <input type="text" name="subtitle" value="<?= $page['subtitle'] ?>" class="form__input" placeholder="Subtitle">
                </div>
            </div>
            <div class="form__column">
                <div class="form__row image__place">
                    <?php self::component('forms/images-pad', ['link' => empty($page['image_link']) ? '' : $page['image_link']]) ?>
                </div>
            </div>
        </div>
        <details>
            <summary>Додаткові параметри:</summary>
            <fieldset>
                <legend>Додаткові налаштування сторінки:</legend>
                <div class="form__columns">
                    <div class="form__column">
                        <div class="form__row">
                            <label class="form__label">Зтислий опис:</label>
                        </div>
                        <div class="form__row">
                            <div class="editor-block" data-field="description">
                                <div class="toolbar-container"></div>
                                <div class="content-container">
                                    <div class="editor"><?= $page['description'] ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form__column">
                        <div class="form__row">
                            <label class="form__label" for="keywords">Ключові слова:</label>
                            <input type="text" id="keywords" name="keywords" value="<?= $page['keywords'] ?>" class="form__input" placeholder="Keywords">
                        </div>
                        <div class="form__row">
                            <label class="form__label" for="published_at">Дата публікації:</label>
                            <input type="datetime-local" id="published_at" name="published_at" value="<?= $page['published_at'] ?>" class="form__input" placeholder="Дата публікації">
                        </div>
                        <div class="form__row">
                            <label class="form__label" for="expired_at">Дійсна до:</label>
                            <input type="datetime-local" id="expired_at" name="expired_at" value="<?= $page['expired_at'] ?>" class="form__input" placeholder="Дійсна до">
                        </div>
                    </div>
                </div>
            </fieldset>
        </details>
        <div class="form__row">
            <label class="form__label">Контент сторінки:</label>
        </div>
        <div>
        <div class="blocks">
            <?php if (is_array($page['html'])): ?>
                <? foreach($page['html'] as $block):?>
                    <?php self::component('blocks/forms/'.$block['type'], compact('block')) ?>
                    <?php self::component('page-add-block') ?>
                <?php endforeach ?>
            <?php else :?>
                <?php self::component('blocks/forms/text', ['block' => ['html' => $page['html']]]); ?>
                <?php self::component('page-add-block') ?>
            <?php endif ?>
            </div>
            <div class="form__button-place">
                <button type="submit" class="form__button"><?= $texts['SubmitLabel'] ?></button>
            </div>
        </div>
    </form>
</section>