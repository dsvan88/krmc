<section class="section">
    <div class="images__wrapper">
        <div class="images">
            <?php self::component('list/image/new', ['type' => 'gallery']) ?>
            <?php foreach ($gallery as $file): ?>
                <?php self::component('list/image/item', compact('file')) ?>
            <?php endforeach ?>
            <?php if (!empty($nextPageToken)) : ?>
                <div class="image get-more" data-action-click="images/get-more" data-type="gallery" type data-page-token="<?= $nextPageToken ?>">
                    <span class="label fa fa-refresh"></span>
                </div>
            <?php endif ?>
        </div>
        <div class="images__details">
            <div class="images__dashboard">
                <span class="dashboard__item delete fa fa-trash" data-action-click="image/delete/group" data-verification="confirm" title="Remove there images from the Gallery"></span>
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