<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <? #<meta name="description" content="<?= $webDescription ? >" />?>
    <meta name="author" content="<?= CFG_AUTHOR ?>" />
    <link rel="stylesheet" href="/public/css/style.css?v='<?= $_SERVER['REQUEST_TIME'] ?>" />
    <?= $scripts ?>
    <?= $styles ?>
    <title><?= $title . ' ' . MAFCLUB_SNAME . ' v' . SCRIPT_VERSION ?></title>
</head>

<body>
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
</body>

</html>