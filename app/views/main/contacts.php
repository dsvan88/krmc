<section class='section'>
    <header>
        <h1 class='title'><?= $title ?> <? empty($dashboard) ? '' : self::component('page-dashboard', ['dashboard' => $dashboard]) ?></h1>
        <h2 class='subtitle'><?= $contacts['tg-name']['value'] ?></h2>
    </header>
    <div class='content contacts'>
        <div class="contacts__columns">
            <div class="contacts__column">
                <div class="contacts__item">
                    <h3 class="contacts__label"><?= $contacts['phone']['name'] ?></h3>
                    <p><a class="contacts__link" href="tel:<?= $contacts['phone']['value'] ?>"><?= $contacts['phone']['value'] ?></a></p>
                </div>
                <div class="contacts__item">
                    <h3 class="contacts__label"><?= $contacts['telegram']['name'] ?></h3>
                    <p><a class="contacts__link" href="<?= $contacts['telegram']['value'] ?>"><?= $contacts['tg-name']['value'] ?></a></p>
                </div>
                <div class="contacts__item">
                    <h3 class="contacts__label"><?= $contacts['email']['name'] ?></h3>
                    <p><a class="contacts__link" href="mailto:<?= $contacts['email']['value'] ?>"><?= $contacts['email']['value'] ?></a></p>
                </div>
            </div>
            <div class="contacts__column">
                <div class="contacts__item">
                    <h3 class="contacts__label"><?= $contacts['adress']['name'] ?></h3>
                    <p><?= str_replace('  ', '<p></p>', $contacts['adress']['value']) ?></p>
                </div>
                <div class="contacts__gmap">
                    <iframe src="<?= $contacts['gmap_widget']['value'] ?>" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>
        <hr>
        <? if (!empty($socials['youtube']['value'])) : ?>
            <div class="contacts__item">
                <h3 class="contacts__label"><?= $socials['youtube']['name'] ?></h3>
                <p><a class="contacts__link" href="<?= $socials['youtube']['value'] ?>"><?= $socials['youtube']['name'] ?></a></p>
            </div>
        <? endif ?>
        <? if (!empty($socials['instagram']['value'])) : ?>
            <div class="contacts__item">
                <h3 class="contacts__label"><?= $socials['instagram']['name'] ?></h3>
                <p><a class="contacts__link" href="<?= $socials['instagram']['value'] ?>"><?= $socials['instagram']['name'] ?></a></p>
            </div>
        <? endif ?>
        <? if (!empty($socials['facebook']['value'])) : ?>
            <div class="contacts__item">
                <h3 class="contacts__label"><?= $socials['facebook']['name'] ?></h3>
                <p><a class="contacts__link" href="<?= $socials['facebook']['value'] ?>"><?= $socials['facebook']['name'] ?></a></p>
            </div>
        <? endif ?>
    </div>
</section>