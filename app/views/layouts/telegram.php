<!DOCTYPE html>
<html lang="uk">

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
    <link rel="stylesheet" href="/public/css/style-tg.css?v=<?= $_SERVER['REQUEST_TIME'] ?>">
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
    <?/*<script defer="" src="<?= static::$scriptsPath . $scripts ?>"></script>*/ ?>
    <link rel="icon" type="image/x-icon" href="/public/images/mafia-vintage-logo-short.svg">
    <?= $locales ?>
    <title><?= (isset($pageTitle) ? $pageTitle  : $title) . ' | ' . CLUB_SNAME . ' v' . APP_VERSION ?></title>
    <?php if (!empty($og)) self::component('open-graph', ['og' => $og]) ?>

</head>

<body>
    <div class="body">
        <?php self::component('notices', ['notices' => $notices]) ?>
        <div class="wrapper">
            <header class="header">
                <div class="header__content">
                    <?php if (!empty($_SESSION['id'])) : ?>
                        <div class="header__line">
                            <div class="profile">
                                <a href="/account/profile/<?= $_SESSION['id'] ?>/" alt="<?= $_SESSION['name'] ?>" title="<?= $_SESSION['name'] ?>"><?= $_SESSION['name'] ?></a>
                            </div>
                        </div>
                        <menu class="header__menu">
                            <?php self::icon('gradient') ?>
                            <?php foreach ($headerMenu as $index => $item) : ?>
                                <li class="header__menu-item">
                                    <a href="/<?= $item['path'] ?>/" <?= empty($item['active']) ? '' : 'class="active"' ?>>
                                        <?php self::icon($item['icon']) ?>
                                        <span><?= $item['label'] ?></span>
                                    </a>
                                </li>
                            <?php endforeach ?>
                        </menu>
                    <?php endif ?>
                </div>
                <?php if (!empty($headerDashboard)) : ?>
                    <div class="header__dashboard">
                        <?php self::component('header/dashboard', ['dashboard' => $headerDashboard]) ?>
                    </div>
                <?php endif ?>
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
    </div>
</body>

</html>