<section class='section'>
    <header>
        <h1 class='title'><?= $page['title'] ?> <? empty($dashboard) ? '' : self::component('page-dashboard', ['dashboard' => $dashboard]) ?></h1>
        <h2 class='subtitle'><?= $page['subtitle'] ?></h2>
    </header>
    <div class='content'>
        <? foreach ?>
        <? var_dump($contacts); ?>
    </div>
</section>