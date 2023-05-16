<section class="section settings">
    <menu class="settings__categories">
        <li class="settings__category<?= $section==='email' ? ' active' : ''?>" data-action-click="/settings/section/index/email" data-mode='location'>Пошта</li>
        <li class="settings__category<?= $section==='contacts' ? ' active' : ''?>" data-action-click="/settings/section/index/contacts" data-mode='location'>Контакти</li>
        <li class="settings__category<?= $section==='socials' ? ' active' : ''?>" data-action-click="/settings/section/index/socials" data-mode='location'>Соц. мережі</li>
        <li class="settings__category<?= $section==='telegram' ? ' active' : ''?>" data-action-click="/settings/section/index/telegram" data-mode='location'>Телеграм</li>
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
                <? foreach ($settings as $slug=>$data) : ?>
                    <tr>
                        <td title="Назва"><?= $data['name'] ?></td>
                        <td title="Значення" class="settings__value"><?= $data['value'] ?></td>
                        <td title="Меню" class="settings__dasboard">
                            <span class="fa fa-pencil-square-o" data-action-click="settings/edit/form" data-setting-id="<?= $data['id'] ?>" title='Редагувати'></a>
                            <?/*<!-- <a href="/settings/delete/<?= $data['id'] ?>" onclick="return confirm('Are you sure?')" title='Видалити'><i class='fa fa-trash-o news-dashboard__button'></i></a>*/?>
                        </td>
                    </tr>
                <? endforeach; ?>
            </tbody>
        </table>
    </div>
    <!-- <a href="/settings/add">Додати</a> -->
</section>