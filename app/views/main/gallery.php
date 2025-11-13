<section class='section'>
    <header>
        <h1 class='title'>
            <?= $page['title'] ?>
            <span class='page__dashboard' style='float:right'>
                <a href='/main/gallery/edit' title='<?= $texts['edit'] ?>' class='fa fa-pencil-square-o'></a>
            </span>
        </h1>
        <h2 class='subtitle'><?= $page['subtitle'] ?></h2>
    </header>
    <div class='content gallery'>
        <? foreach ($gallery as $image): ?>
            <? self::component('list/gallery/item', compact('image')) ?>
        <? endforeach ?>
        <? if (!empty($nextPageToken)) : ?>
            <div class="image get-more" data-action-click="gallery/get-more" data-page-token="<?= $nextPageToken ?>">
                <span class="label fa fa-refresh"></span>
            </div>
        <? endif ?>
    </div>
</section>