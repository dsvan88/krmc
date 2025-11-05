<section class="section settings">
    <menu class="settings__categories">
        <li class="settings__category<?= $section === 'email' ? ' active' : '' ?>" data-action-click="/settings/section/index/email" data-mode='location'>Пошта</li>
        <li class="settings__category<?= $section === 'contacts' ? ' active' : '' ?>" data-action-click="/settings/section/index/contacts" data-mode='location'>Контакти</li>
        <li class="settings__category<?= $section === 'socials' ? ' active' : '' ?>" data-action-click="/settings/section/index/socials" data-mode='location'>Соц. мережі</li>
        <li class="settings__category<?= $section === 'telegram' ? ' active' : '' ?>" data-action-click="/settings/section/index/telegram" data-mode='location'>Телеграм</li>
        <li class="settings__category<?= $section === 'backup' ? ' active' : '' ?>" data-action-click="/settings/section/index/backup" data-mode='location'>Резервування</li>
        <li class="settings__category<?= $section === 'gdrive' ? ' active' : '' ?>" data-action-click="/settings/section/index/gdrive" data-mode='location'>Google Drive</li>
    </menu>
    <div class="settings__content">
        <table class="settings__table">
            <thead>
                <tr>
                    <th>Назва</th>
                    <th>Значення</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <? foreach ($settings as $setting) : ?>
                    <? self::component('settings\row', compact('setting', 'section')) ?>
                <? endforeach ?>
            </tbody>
        </table>
    </div>
</section>