<section class="coupons">
    <header class="coupons__header">
        <menu class="types">
            <li class="types__item <?= $tab === 'index' ? 'active' : '' ?>" data-action-click="?tab=index" data-mode="location">List</li>
            <li class="types__item <?= $tab === 'types' ? 'active' : '' ?>" data-action-click="?tab=types" data-mode="location">Types</li>
        </menu>
        <i class="coupons__add fa fa-plus-circle" data-action-click="/coupons/add<?= $tab === 'types' ? 'Type' : '' ?>/form"></i>
        <h2 class="coupons__title"><?= $title ?></h2>
        <h3 class="coupons_subtitle"><?= $subtitle ?? '' ?></h3>
    </header>
    <?php if ($tab === 'index'): ?>
        <div class="coupons__list">
            <?php foreach ($coupons as $num => $coupon): ?>
                <? static::component('coupons/coupon', compact('coupon', 'num')) ?>
            <?php endforeach ?>
        </div>
    <?php else: ?>
        <div class="types__list">
            <?php foreach ($coupons as $num => $coupon): ?>
                <? static::component('coupons/type', compact('coupon', 'num')) ?>
            <?php endforeach ?>
        </div>
    <?php endif; ?>
</section>