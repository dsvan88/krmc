<section class="section profile">
    <div class="profile__wrapper">
        <h1 class="profile__title"><?= $title ?></h1>
        <div class="profile__content">
            <menu class="profile__sections">
                <hr>
                <li class="profile__section fa fa-user active" data-action-click="account/profile/section" data-uid="<?= $userId ?>" data-section="personal"><span class="section-label">Особисті</span></li>
                <!-- <li class="profile__section fa fa-user active" data-action-click="account/profile/section" data-uid="<?= $userId ?>" data-section="personal"><span class="section-label">Особисті</span><span class="edit-section fa fa-pencil-square-o" data-action-click="account/profile/section/edit" title="Редагувати"></span></li> -->
                <!-- <li class="profile__section fa fa-envelope-o" data-action-click="account/profile/section" data-uid="<?= $userId ?>" data-section="contacts"><span class="section-label">Контакти</span><span class="edit-section fa fa-pencil-square-o" data-action-click="account/profile/section/edit" title="Редагувати"></span></li> -->
                <li class="profile__section fa fa-lock" data-action-click="account/profile/section" data-uid="<?= $userId ?>" data-section="security"><span class="section-label">Безпека</span><span></span></li>
                <? if ($isAdmin) : ?>
                    <li class="profile__section fa fa-lock" data-action-click="account/profile/section" data-uid="<?= $userId ?>" data-section="control"><span class="section-label">Керування</span><span class="edit-section fa fa-pencil-square-o" data-action-click="account/profile/section/edit" title="Редагувати"></span></li>
                <? endif ?>
                <hr>
            </menu>
            <div class="profile__card">
                <div class="profile__card-row">
                    <h3 class="profile__card-title"><?= $texts['profileCardTitle'] ?><span class="text-accent"><?= $userId ?></span>:</h3>
                </div>
                <div class="profile__avatar avatar">
                    <? if ($emptyAvatar): ?>
                        <div class="avatar__wrapper empty">
                            <? if ($isAdmin):?>
                                <span class="avatar__image" data-action-dblclick="account/avatar/tg/get" data-uid="<?= $userId ?>">
                                    <?= $data['avatar'] ?>
                                </span>
                            <? elseif ($isSelf): ?>
                                <span class="avatar__image" data-action-dblclick="account/avatar/edit/form" data-uid="<?= $userId ?>">
                                    <?= $data['avatar'] ?>
                                </span>
                            <? else: ?>
                                <span class="avatar__image">
                                    <?= $data['avatar'] ?>
                                </span>
                            <? endif ?>
                        </div>
                    <? else : ?>
                        <div class="avatar__wrapper">
                            <? if ($isSelf):?>
                                <i class="avatar__edit fa fa-pencil-square-o" data-action-click="account/avatar/edit/form" data-uid="<?= $userId ?>"></i>
                            <?endif?>
                            <span class="avatar__image" data-action-click="account/avatar/show">
                                <?= $data['avatar'] ?>
                            </span>
                        </div>
                    <? endif ?>
                </div>
                <div class="profile__card-content">
                    <? require $_SERVER['DOCUMENT_ROOT'] . '/app/views/account/sections/personal.php' ?>
                </div>
            </div>
        </div>
    </div>
</section>