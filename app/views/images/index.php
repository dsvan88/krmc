<section class="section">
    <div class="images">
        <? foreach ($files as $file): ?>
            <? self::component('list/image', compact('file')) ?>
        <? endforeach ?>
    </div>
</section>