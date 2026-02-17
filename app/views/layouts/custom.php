<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <?php #<meta name="description" content="<?= $webDescription ? >" />
    ?>
    <meta name="author" content="<?= CFG_AUTHOR ?>" />
    <?php if (!empty($styles)) : ?>
        <?php if (is_string($styles)) : ?>
            <link rel="stylesheet" href="<?= STYLES_STORAGE . "$styles.css?v={$_SERVER['REQUEST_TIME']}" ?>">
        <?php else : ?>
            <?php for ($x = 0; $x < count($styles); $x++) : ?>
                <link rel="stylesheet" href="<?= STYLES_STORAGE . "{$styles[$x]}.css?v={$_SERVER['REQUEST_TIME']}" ?>">
            <?php endfor ?>
        <?php endif ?>
    <?php endif ?>
    <link rel="stylesheet" href="/public/css/style.css?v='<?= $_SERVER['REQUEST_TIME'] ?>" />
    <?php if (!empty($scripts)) : ?>
        <?php if (is_string($scripts)) : ?>
            <script defer="" src="<?= static::$scriptsPath . $scripts ?>"></script>
            <?php else :
            for ($x = 0; $x < count($scripts); $x++) : ?>
                <script defer="" src="<?= static::$scriptsPath . $scripts[$x] ?>"></script>
            <?php endfor ?>
        <?php endif ?>
    <?php endif ?>
    <?= $styles ?>
    <title><?= $title . ' ' . CLUB_SNAME . ' v' . APP_VERSION ?></title>
</head>

<body>
    <div class="body">
        <div class="wrapper">
            <header class="header">
                <?= $header ?>
            </header>
            <div class="header-for-auto-scroll" id="start-page"></div>
            <main class="main">
                <?= $content ?>
            </main>
            <footer class="footer">
                <div class="footer_content"><?= $footerContent ?></div>
            </footer>
        </div>
    </div>
</body>

</html>