<div class="form__row">
    <div class="images__wrapper">
        <div class="images">
            <? self::component('list/image/new') ?>
            <? foreach ($files as $file): ?>
                <? self::component('list/image/item', compact('file', 'backgrounds')) ?>
            <? endforeach ?>
        </div>
        <div class="images__dashboard">
            <span class="dashboard__item fa fa-object-group" data-action-click="image/background/group" title="Set as background images"></span>
            <span class="dashboard__item delete fa fa-trash" data-action-click="image/delete/group" data-verification="confirm" title="Delete from the gDrive"></span>
        </div>
    </div>
    <div class="images__paginator">
        <a href="/images/index/<?= $_SESSION['nextPageToken'] ?>">-></a>
    </div>
</div>