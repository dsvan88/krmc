<section class='section'>
    <header>
        <h1 class='title'><?= $page['title'] ?> <? empty($dashboard) ? '' : self::component('page-dashboard', ['dashboard' => $dashboard]) ?></h1>
        <h2 class='subtitle'><?= $page['subtitle'] ?></h2>
    </header>
    <div class='content contacts'>
        <div class="contacts__item">
            <h3><?= $contacts['phone']['name'] ?></h3>
            <p><a href="tel:<?= $contacts['phone']['value'] ?>"><?= $contacts['phone']['value'] ?></a></p>
        </div>
        <div class="contacts__item">
            <h3><?= $contacts['telegram']['name'] ?></h3>
            <p><a href="<?= $contacts['telegram']['value'] ?>"><?= $contacts['telegram']['value'] ?></a></p>
        </div>
        <div class="contacts__item">
            <h3><?= $contacts['tg-chatbot']['name'] ?></h3>
            <p><a href="https://t.me/<?= $contacts['tg-chatbot']['value'] ?>"><?= $contacts['tg-chatbot']['value'] ?></a></p>
        </div>
        <div class="contacts__item">
            <h3><?= $contacts['email']['name'] ?></h3>
            <p><a href="mailto:<?= $contacts['email']['value'] ?>"><?= $contacts['email']['value'] ?></a></p>
        </div>
        <? foreach ($contacts as $contact): ?>
            <div>
                <h3><?= $contact['name'] ?></h3>
                <p><?= $contact['value'] ?></p>
            </div>
        <? endforeach ?>
        <pre>
        <? var_dump($contacts) ?>
        </pre>
    </div>
</section>