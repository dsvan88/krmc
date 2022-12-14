<!DOCTYPE html>
<html lang="uk">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="{WEBSITE_DESCRIPTION}" />
    <meta name="author" content="<?= CFG_AUTHOR ?>" />
    <link rel="stylesheet" href="/public/css/style.css?v=<?= $_SERVER['REQUEST_TIME'] ?>" />
    <link rel="stylesheet" href="/public/css/jquery-ui.min.css" />
    <link rel="stylesheet" href="/public/css/cropper.css" />
    <link rel="stylesheet" href="/public/css/jquery.datetimepicker.min.css" />
    <?/*= //$styles */ ?>
    <script defer="" type="text/javascript" src="/public/scripts/jquery.min.js"></script>
    <script defer="" type="text/javascript" src="/public/scripts/jquery-ui.min.js"></script>
    <script defer="" type="text/javascript" src="/public/scripts/jquery.datetimepicker.full.min.js"></script>
    <script defer="" type="text/javascript" src="/public/scripts/jquery-cropper.js"></script>
    <script defer="" type="text/javascript" src="/public/scripts/common-funcs.js?v=<?= $_SERVER['REQUEST_TIME'] ?>"></script>
    <? if (is_string($scripts)) : ?>
        <script defer="" type="text/javascript" src="<?= $scripts ?>"></script>
        <? else :
        for ($x = 0; $x < count($scripts); $x++) : ?>
            <script defer="" type="text/javascript" src="<?= $scripts[$x] ?>"></script>
    <? endfor;
    endif; ?>
    <script defer="" type="text/javascript" src="/public/scripts/common.js?v=<?= $_SERVER['REQUEST_TIME'] ?>"></script>
    <script defer="" type="text/javascript" src="/public/scripts/modals.js?v=<?= $_SERVER['REQUEST_TIME'] ?>"></script>
    <link rel="icon" type="image/x-icon" href="/public/images/club_favicon.png">
    <title><?= $title . ' ' . MAFCLUB_SNAME . ' v' . SCRIPT_VERSION ?></title>
</head>

<body>
    <div class="wrapper">
        <header class="header">
            <div class="header__logo"><?= $headerLogo ?></div>
            <div class="header__menu">
                <label for="header__navigation-checkbox" class="navigation-for-small-display menu-show"><i class="fa fa-bars"></i></label>
                <input type="checkbox" name="toggle-navigation" id="header__navigation-checkbox" class="navigation-for-small-display" autocomplete="off" />
                <nav class="header__navigation" id="header__navigation">
                    <label for="header__navigation-checkbox" class="navigation-for-small-display menu-hide"><i class="fa fa-times"></i></label>
                    <? for ($x = 0; $x < count($headerMenu); $x++) : ?>
                        <? if (!isset($headerMenu[$x]['drop-down-menu'])) : ?>
                            <li class="header__navigation-item">
                                <a href="/<?= $headerMenu[$x]['path'] ?>"><?= $headerMenu[$x]['label'] ?></a>
                                <div class="bar"></div>
                            <? else : ?>
                            <li class="header__navigation-item drop-down" style="position:relative">
                                <label for="header__dropdown-menu-checkbox" class="header__dropdown-menu-label"><?= $headerMenu[$x]['label'] ?></label>
                                <div class="bar"></div>
                                <input type="checkbox" class='header__dropdown-menu-checkbox' id="header__dropdown-menu-checkbox">
                                <menu class="header__dropdown-menu-list">
                                    <? for ($i = 0; $i < count($headerMenu[$x]['drop-down-menu']); $i++) :
                                        $path = '';
                                        if ($headerMenu[$x]['drop-down-menu'][$i]['short_name'] !== 'index') {
                                            $path = $headerMenu[$x]['type'] . '/' . $headerMenu[$x]['drop-down-menu'][$i]['short_name'];
                                        }
                                    ?>
                                        <div class="header__dropdown-menu-list-item">
                                            <a href="/<?= $path ?>"><?= $headerMenu[$x]['drop-down-menu'][$i]['name'] ?></a>
                                            <div class="header__dropdown-menu-list-item-bar"></div>
                                        </div>
                                    <? endfor; ?>
                                </menu>
                            </li>
                        <? endif; ?>
                    <? endfor; ?>
                </nav>
            </div>
            <div class="header__profile"><?= $headerProfileButton ?></div>
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