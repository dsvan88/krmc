<section class='section'>
    <header>
        <h1 class='title'><?= $page['title'] ?> <? empty($dashboard) ? '' : self::component('page-dashboard', ['dashboard' => $dashboard]) ?></h1>
        <h2 class='subtitle'><?= $page['subtitle'] ?></h2>
    </header>
    <div class='content'>
        
        <? if (!empty($page['logoLink'])): ?>
            <div class="page__image-place">
                <img src="<?= $page['logoLink'] ?>" alt="Main image" class="page__image">
            </div>
        <? endif ?>
        <?= $page['html'] ?>
    </div>
</section>