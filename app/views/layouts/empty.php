<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php if (!empty($description)) : ?>
        <meta name="description" content="<?= $description ?>">
    <?php endif ?>
    <meta name="author" content="<?= CFG_AUTHOR ?>">
    <?php if (!empty($styles)) : ?>
        <?php if (is_string($styles)) : ?>
            <link rel="stylesheet" href="<?= STYLES_STORAGE . "$styles.css?v={$_SERVER['REQUEST_TIME']}" ?>">
        <?php else : ?>
            <?php for ($x = 0; $x < count($styles); $x++) : ?>
                <link rel="stylesheet" href="<?= STYLES_STORAGE . "{$styles[$x]}.css?v={$_SERVER['REQUEST_TIME']}" ?>">
            <?php endfor ?>
        <?php endif ?>
    <?php endif ?>
    <link rel="stylesheet" href="/public/css/style.css?v=<?= $_SERVER['REQUEST_TIME'] ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Source+Serif+Pro">
    <script defer="" src="https://telegram.org/js/telegram-web-app.js"></script>
    <?php if (!empty($scripts)) : ?>
        <?php if (is_string($scripts)) : ?>
            <script defer="" src="<?= static::$scriptsPath . $scripts ?>"></script>
            <?php else :
            for ($x = 0; $x < count($scripts); $x++) : ?>
                <script defer="" src="<?= static::$scriptsPath . $scripts[$x] ?>"></script>
            <?php endfor ?>
        <?php endif ?>
    <?php endif ?>
    <link rel="icon" type="image/x-icon" href="/public/images/mafia-vintage-logo-short.svg">
    <?= $locales ?>
    <title><?= (isset($pageTitle) ? $pageTitle  : $title) . ' | ' . CLUB_SNAME . ' v' . APP_VERSION ?></title>
    <?php if (!empty($og)) self::component('open-graph', ['og' => $og]) ?>
</head>

<body>
    <div class="body">
        <?php self::component('notices', ['notices' => $notices]) ?>
        <div class="wrapper">
        </div>
    </div>
</body>

</html>