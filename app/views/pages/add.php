<section class='section common-form'>
    <form action="/page/add" method="post" enctype="multipart/form-data" class="common-form__form">
        <h2 class="common-form__title"><?= $title ?></h2>
        <div>
            <select name="type" class="common-form__input">
                <option value="page">Сторінка</option>
                <option value="game">Опис гри</option>
            </select>
        </div>
        <div><input type="text" name="title" value="" class="common-form__input title" placeholder="Title"></div>
        <div><input type="text" name="subtitle" value="" class="common-form__input subtitle" placeholder="Subtitle"></div>
        <div class="editor-block">
            <div class="toolbar-container"></div>
            <div class="content-container">
                <div class="editor"></div>
            </div>
        </div>
        <div class="common-form__button-place">
            <button type="submit" class="common-form__button"><?= $texts['SubmitLabel'] ?></button>
        </div>
    </form>
</section>