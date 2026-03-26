<section class="coupons">
    <?php foreach($coupons as $num=>$coupon):?>
        <div class="coupon">
            <div class="coupon__num"><?=($num+1)?>.</div>
            <div class="coupon__id"><?=$coupon->id?></div>
            <div class="coupon__owner"><?=$coupon->owner->name?></div>
            <div class="coupon__used"><?=empty($coupon->used_on) ? '-' : "<a href='/week/{$coupon->used_on['weekId']}/day/{$coupon->used_on['dayId']}/' target='_blank'>Day: {$coupon->used_on['dayId']} Week: {$coupon->used_on['weekId']}</a>"?></div>
            <div class="coupon__expired"><?=$coupon->expired_at?></div>
        </div>
    <?php endforeach?>
</section>