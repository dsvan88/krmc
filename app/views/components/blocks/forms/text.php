<div class="block" data-block-type="text">
    <fieldset>
        <legend>Block:</legend>
        <div class="block__title">
            <input class="form__input" type="text" value="<?= $block['title']  ?? ''?>" placeholder="Block title">
        </div>
        <?php self::component('blocks/forms/dashboard', ['selected' => 'text']) ?>
        <div class="block__content">
            <div class="editor-block">
                <div class="toolbar-container"></div>
                <div class="content-container">
                    <div class="editor"><?= $block['html']  ?? ''?></div>
                </div>
            </div>
        </div>
    </fieldset>
</div>
