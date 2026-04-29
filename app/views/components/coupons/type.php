<div class="type <?= $coupon['active'] ? 'active' : '' ?>">
    <div class="type__dashboard"><i class="fa fa-trash"></i></div>
    <h4 class="type__title"><?= $coupon['name'] ?> <?= $coupon['icon'] ?> </h4>
    <div class="type__content">
        <div class="type__row">
            <div class="type__label">Type</div>
            <span class="type__value"><?= $coupon['type'] ?></span>
        </div>
        <div class="type__row">
            <div class="type__label">Discount</div>
            <span class="type__value"><?= $coupon['options']['discount'] . $coupon['options']['discount_type'] ?></span>
        </div>
        <div class="type__row">
            <div class="type__label">Price</div>
            <span class="type__value"><?= $coupon['price'] ?></span>
        </div>
    </div>
</div>