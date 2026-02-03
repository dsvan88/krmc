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
    <link rel="stylesheet" href="/public/css/style.css?v=<?= $_SERVER['REQUEST_TIME'] ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Source+Serif+Pro">
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
        <?php self::component('notices', compact('notices')) ?>
        <div class="wrapper">
            <?php if (!empty($backdroundImages)) : ?>
                <aside class="images">
                    <?php foreach ($backdroundImages as $index => $imageId): ?>
                        <?php if ($index === 4): ?>
                            </aside>
                            <aside class="images right">
                        <?php endif ?>
                            <img class="image" src="https://lh3.googleusercontent.com/d/<?= $imageId ?>" loading="lazy" alt="Background Image #<?= $index ?>">
                        <?php endforeach ?>
                </aside>
            <?php endif ?>
            <header class="header">
                <div class="header__content">
                    <div class="header__logo">
                        <a href="/"><?= $headerLogo ?></a>
                    </div>
                    <div class="header__options">
                        <div class="header__langs">
                            <a href='?lang=uk' class="header__lang<?= $lang === 'uk' ? ' selected' : '' ?>">🇺🇦</a>
                            <a href='?lang=en' class="header__lang<?= $lang === 'en' ? ' selected' : '' ?>">🇬🇧</a>
                            <a href='?lang=ru' class="header__lang<?= $lang === 'ru' ? ' selected' : '' ?>">🇷🇺</a>
                        </div>
                        <div class="header__menu">
                            <label for="navigation__checkbox" class="navigation-for-small-display menu-show fa fa-bars"></label>
                            <input type="checkbox" name="toggle-navigation" id="navigation__checkbox" class="navigation-for-small-display-chechbox">
                            <nav class="navigation" id="navigation">
                                <label for="navigation__checkbox" class="navigation-for-small-display menu-hide fa fa-times"></label>
                                <?php foreach ($headerMenu as $headerMenuItem) : ?>
                                    <?php self::component('header/menu-item', ['menuItem' => $headerMenuItem]) ?>
                                <?php endforeach ?>
                            </nav>
                        </div>
                    </div>
                    <div class="profile">
                        <?php if (empty($_SESSION['id'])) : ?>
                            <?php self::component('header/profile/login', ['headerLoginLabel' => $headerLoginLabel]) ?>
                        <?php else : ?>
                            <?php self::component('header/profile/profile', ['profileImage' => $profileImage, 'profile' => $profileMenu[0]]) ?>
                            <?php if (count($profileMenu) > 2) : ?>
                                <?php self::component('header/profile/menu', ['profileMenu' => $profileMenu]) ?>
                            <?php else : ?>
                                <?php self::component('header/profile/logout', ['headerLogoutLabel' => $headerLogoutLabel]) ?>
                            <?php endif ?>
                        <?php endif; ?>
                    </div>
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