<section class='section'>
    <header>
        <h1 class='title'><?= $page['title'] ?> <?php empty($dashboard) ? '' : self::component('page-dashboard', ['dashboard' => $dashboard]) ?></h1>
        <h2 class='subtitle'><?= $page['subtitle'] ?></h2>
    </header>
    <div class='content'>
        
        <?php if (!empty($page['logoLink'])): ?>
            <div class="page__image-place">
                <img src="<?= $page['logoLink'] ?>" alt="" class="page__image">
            </div>
        <?php endif ?>
        <?= $page['html'] ?>
    </div>
</section>