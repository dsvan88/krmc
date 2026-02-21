<div class="block" data-block-type="text">
    <?php self::component('blocks/forms/dashboard', ['selected' => 'text']) ?>
    <div class="editor-block" data-field="html[]">
        <div class="toolbar-container"></div>
        <div class="content-container">
            <div class="editor"><?= $block['html']  ?? ''?></div>
        </div>
    </div>
</div>
<hr>
