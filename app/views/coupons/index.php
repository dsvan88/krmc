<section class="coupons">
    <?php foreach($coupons as $num=>$coupon):?>
        <div class="coupon <?=$coupon->class?>">
            <h4 class="coupon__title">Coupon #<span class="coupon__id"><?=$coupon->id?></span></h4>
            <div class="coupon__content">
                <div class="coupon__num"><?=($num+1)?>.</div>
                <div class="coupon__status"><?=$coupon->class === 'ready' ? '' : "<div>{$coupon->class}</div>"?></div>
                <div class="coupon__owner"><?=$coupon->owner->name?></div>
                <div class="coupon__dates">
                    <div class="coupon__used"><?=empty($coupon->used_on) ? '-' : "<a href='/week/{$coupon->used_on['weekId']}/day/{$coupon->used_on['dayId']}/' target='_blank'>Day: {$coupon->used_on['dayId']} Week: {$coupon->used_on['weekId']}</a>"?></div>
                    <div class="coupon__created"><?= date('d.m.Y', $coupon->created_at) ?></div>
                    <div class="coupon__expired"><?=  $coupon->expired_at > TIMESTAMP_YEAR+TIMESTAMP_DAY ? date('d.m.Y',$coupon->expired_at) : '-' ?></div>
                </div>
                <div class="coupon__dashboard">
                    <i class="fa fa-gavel" data-action-click="coupon/change/status" data-coupon-id="<?=$coupon->id?>"></i>
                    <i class="fa fa-trash" data-action-click="coupon/delete" data-verification="verification/root" data-coupon-id="<?=$coupon->id?>"></i>
                </div>
            </div>
        </div>
    <?php endforeach?>
</section>