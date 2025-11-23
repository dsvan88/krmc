<section class="section">
    <div class="images__wrapper">
        <div class="images">
            <? self::component('list/image/new') ?>
            <? foreach ($files as $file): ?>
                <? if (empty($file['thumbnailLink'])) :?>
                    <? self::component('list/image/folder', compact('file')) ?>
                <? else :?>
                    <? self::component('list/image/item', compact('file', 'backgrounds')) ?>
                <? endif ?>
            <? endforeach ?>
            <? if (!empty($nextPageToken)) : ?>
                <div class="image get-more" data-action-click="images/get-more" data-page-token="<?= $nextPageToken ?>">
                    <span class="label fa fa-refresh"></span>
                </div>
            <? endif ?>
        </div>
        <div class="images__details">
            <div class="images__dashboard">
                <span class="dashboard__item fa fa-link" data-action-click="image-get-link" title="Get a link"></span>
                <span class="dashboard__item fa fa-object-group" data-action-click="image/background/group" title="Set as background images"></span>
                <span class="dashboard__item delete fa fa-trash" data-action-click="image/delete/group" data-verification="confirm" title="Delete from the gDrive"></span>
            </div>
            <div class="images__info info">
                <div class="info__row">
                    <span class="info__caption">Назва</span>
                    <span class="info__value" id="info_value_name">-</span>
                </div>
                <div class="info__row">
                    <span class="info__caption">Розмір (байт)</span>
                    <span class="info__value" id="info_value_bytes">-</span>
                </div>
                <div class="info__row">
                    <span class="info__caption">Розмір (піскселі)</span>
                    <span class="info__value" id="info_value_resol">-</span>
                </div>
            </div>
        </div>
    </div>
</section>