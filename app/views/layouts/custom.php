<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="{WEBSITE_DESCRIPTION}" />
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
            <div class="footer__copyrights">Designed for <?= MAFCLUB_NAME ?>, by <?= CFG_AUTHOR ?></div>
            <div class="footer__links">
                <!-- <a href="https://ru-ru.facebook.com/demontechno" target="_blank" class="footer__link"><i class="fa fa-facebook-square"></i></a> -->
                <!-- <a href="" class="footer__link"><i class="fa fa-linkedin-square"></i></a> -->
                <!-- <a href="https://github.com/dsvan88" target="_blank" class="footer__link"><i class="fa fa-github-square"></i></a> -->
                <a href="https://t.me/dsvan88" target="_blank" class="footer__link"><i class="fa fa-telegram"></i></a>
                <!-- <a href="tel:+380964518770" target="_blank" class="footer__link"><i class="fa fa-phone-square"></i></a> -->
            </div>
        </footer>
    </div>
</body>

</html>