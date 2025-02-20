<section class="section">
    <div class="images">
        <? foreach ($files as $file): ?>
            <? self::component('list/image/item', compact('file')) ?>
        <? endforeach ?>
        <? self::component('list/image/new') ?>
    </div>
</section>