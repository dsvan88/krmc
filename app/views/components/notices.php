<div class="notices">
    <? if (!empty($notices)) : ?>
        <? foreach ($notices as $num => $notice) : ?>
            <div class="notice <?= $notice['type'] ?>"><span class="notice__message"><?= $notice['message'] ?></span><span class="notice__close fa fa-window-close"></span></div>
        <? endforeach ?>
    <? endif; ?>
</div>