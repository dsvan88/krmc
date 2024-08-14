<!DOCTYPE html>
<html lang="uk">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <? if (!empty($description)) : ?>
        <meta name="description" content="<?= $description ?>">
    <? endif ?>
    <meta name="author" content="<?= CFG_AUTHOR ?>">
    <link rel="stylesheet" href="/public/css/style-tg.css?v=<?= $_SERVER['REQUEST_TIME'] ?>">
    <link rel="stylesheet" href="/public/css/jquery-ui.min.css">
    <link rel="stylesheet" href="/public/css/cropper.css">
    <link rel="stylesheet" href="/public/css/jquery.datetimepicker.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Source+Serif+Pro">

    <script defer="" src="https://telegram.org/js/telegram-web-app.js"></script>
    <script defer="" src="/public/scripts/request.js?v=<?= $_SERVER['REQUEST_TIME'] ?>"></script>
    <script defer="" src="/public/scripts/action-handler.js?v=<?= $_SERVER['REQUEST_TIME'] ?>"></script>
    <script defer="" src="/public/scripts/popups.js?v=<?= $_SERVER['REQUEST_TIME'] ?>"></script>
    <script defer="" src="/public/scripts/noticer.js?v=<?= $_SERVER['REQUEST_TIME'] ?>"></script>
    <script defer="" src="/public/scripts/common-funcs.js?v=<?= $_SERVER['REQUEST_TIME'] ?>"></script>
    <script defer="" src="/public/scripts/common.js?v=<?= $_SERVER['REQUEST_TIME'] ?>"></script>
    <script defer="" src="/public/scripts/modals.js?v=<?= $_SERVER['REQUEST_TIME'] ?>"></script>
    <? if (!empty($scripts)) : ?>
        <? if (is_string($scripts)) : ?>
            <script defer="" src="<?= SCRIPTS_STORAGE . $scripts . '?v=' . $_SERVER['REQUEST_TIME'] ?>"></script>
            <? else :
            for ($x = 0; $x < count($scripts); $x++) : ?>
                <script defer="" src="<?= SCRIPTS_STORAGE . $scripts[$x] . '?v=' . $_SERVER['REQUEST_TIME'] ?>"></script>
    <? endfor;
        endif;
    endif; ?>

    <link rel="icon" type="image/x-icon" href="/public/images/mafia-vintage-logo-short.svg">
    <?= $locales ?>
    <title><?= (isset($pageTitle) ? $pageTitle  : $title) . ' | ' . CLUB_SNAME . ' v' . APP_VERSION ?></title>
    <? if (!empty($og)) self::component('open-graph', ['og' => $og]) ?>

</head>

<body>
    <? self::component('notices', ['notices' => $notices]) ?>
    <div class="wrapper">
        <header class="header">
            <div class="header__content">
                <? if (!empty($_SESSION['id'])) : ?>
                    <div class="header__line">
                        <div class="header__profile">
                            <a href="/account/profile/<?= $_SESSION['id'] ?>/" alt="<?= $_SESSION['name'] ?>" title="<?= $_SESSION['name'] ?>"><?= $_SESSION['name'] ?></a>
                        </div>
                    </div>
                    <menu class="header__menu">
                        <? self::component('icons/gradient') ?>
                        <li class="header__menu-item">
                            <a href="/near/">
                                <? self::component('icons/booking') ?>
                                <span>Booking</span>
                            </a>
                        </li>
                        <li class="header__menu-item">
                            <a href="/weeks/">
                                <? self::component('icons/schelude') ?>
                                <span>Schelude</span>
                            </a>
                        </li>
                        <li class="header__menu-item">
                            <a href="/game/" class="test">
                                <? self::component('icons/games') ?>
                                <span>Games</span>
                            </a>
                        </li>
                        <? if ($isAdmin) : ?>
                            <li class="header__menu-item">
                                <a href="/game/mafia/start/">
                                    <? self::component('icons/play') ?>
                                    <span>Play</span>
                                </a>
                            </li>
                        <? endif ?>
                    </menu>
                <? endif ?>
            </div>
            <? if (!empty($headerDashboard)) : ?>
                <div class="header__dashboard">
                    <? self::component('header-dashboard', ['dashboard' => $headerDashboard]) ?>
                </div>
            <? endif ?>
        </header>
        <div class="header-for-auto-scroll" id="start-page"></div>
        <main class="main <?= $mainClass ?>">
            <?= $content ?>
        </main>
        <footer class="footer">
            <div class="footer__content">
                <div class="footer__block">
                    <div class="footer__logo"><?= $headerLogo ?></div>
                </div>
                <div class="footer__block">
                    <div class='footer__adress'>
                        <a class='fa fa-map-marker footer__adress-link' href='<?= $footerGmapLink ?>' target='_blank'> Адреса: </a>
                        <?= $footerAdress ?>
                    </div>
                </div>
                <div class="footer__block footer__gmap">
                    <iframe src="<?= $footerGmapWidget ?>" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
                <div class="footer__block contacts">
                    <div class="footer__contacts">
                        <h4 class="footer__contacts-label">Контакти:</h4>
                        <?= $footerContacts ?>
                        <div class="footer__socials">
                            <?= $footerSocials ?>
                        </div>
                    </div>
                    <div class="footer__copyrights">Designed for <?= CLUB_NAME ?>, by <a class="fa fa-telegram" href="https://t.me/dsvan88" target="_blank"> <?= CFG_AUTHOR ?></a></div>
                </div>
            </div>
        </footer>
    </div>
</body>

</html>