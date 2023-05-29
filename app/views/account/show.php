<section class="section profile">
    <div class="profile__wrapper">
        <h1 class="profile__title"><?= $title ?></h1>
        <div class="profile__content">
            <menu class="profile__sections">
                <li class="profile__section fa fa-user active" data-action-click="account/profile/section" data-uid="<?= $userId ?>" data-section="personal"><span class="section-label">Особисті</span><span class="edit-section fa fa-pencil-square-o" data-action-click="account/profile/section/edit" title="Редагувати"></span></li>
                <li class="profile__section fa fa-envelope-o" data-action-click="account/profile/section" data-uid="<?= $userId ?>" data-section="contacts"><span class="section-label">Контакти</span><span class="edit-section fa fa-pencil-square-o" data-action-click="account/profile/section/edit" title="Редагувати"></span></li>
                <!-- <li class="profile__section fa fa-quote-right" data-action-click="account/profile/section" data-uid="<?= $userId ?>" data-section="credo"><span class="section-label">Кредо</span><span class="edit-section fa fa-pencil-square-o" data-action-click="account/profile/section/edit" title="Редагувати"></span></li> -->
                <li class="profile__section fa fa-lock" data-action-click="account/profile/section" data-uid="<?= $userId ?>" data-section="security"><span class="section-label">Безпека</span><span></span></li>
                <? if ($isAdmin) :?>
                    <li class="profile__section fa fa-lock" data-action-click="account/profile/section" data-uid="<?= $userId ?>" data-section="control"><span class="section-label">Керування</span><span class="edit-section fa fa-pencil-square-o" data-action-click="account/profile/section/edit" title="Редагувати"></span></li>
                <? endif; ?>
            </menu>
            <div class="profile__card">
                <div class="profile__card-avatar avatar">
                    <div class="avatar__wrapper">
                        <a class="avatar__image" data-action-click="account/avatar" data-uid="<?= $userId ?>">
                            <?= $data['avatar'] ?>
                        </a>
                    </div>
                </div>
                <div class="profile__card-content">
                    <? require $_SERVER['DOCUMENT_ROOT'] . '/app/views/account/sections/personal.php'?>
                </div>
            </div>
        </div>
    </div>
</section>