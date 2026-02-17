<div class="profile__card-row">
    <h3 class="profile__card-title">Керування профілем:</h3>
</div>
<div class="profile__card-row">
    <h5 class="profile__card-label">
        Бан <?= !empty($data['ban']) ? " дійсний до <u>{$data['ban']['expired']}</u>" : '' ?>
    </h5>
    <div class="profile__card-value">
        <?php if (empty($data['ban']['options'])): ?>
            <span class="text-accent">Відсутній</span>
        <?php else: ?>
            <span class="text-accent"><?= $data['ban']['options'] ?></span>
        <?php endif ?>
    </div>
</div>
<div class="profile__card-row">
    <h5 class="profile__card-label">
        Статус
    </h5>
    <div class="profile__card-value">
        <span class="text-accent"><?= $data['privilege']['status'] ?? '' ?></span>
    </div>
</div>
<div class="profile__card-row">
    <h5 class="profile__card-label">
        Перейменування
    </h5>
    <div class="profile__card-value">
        <span class="text-accent">Доступне</span>
    </div>
</div>
<div class="profile__card-row">
    <h5 class="profile__card-label">
        Видалення
    </h5>
    <div class="profile__card-value">
        <span class="text-accent">Доступне</span>
    </div>
</div>