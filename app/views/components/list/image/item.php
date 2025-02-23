<? 
$selected = '';
if (in_array($file['id'],$backgrounds,true)){
    $selected = 'select_bg';
}
?>
<div class="image <?=$selected?>">
    <label class="dashboard__label">
        <input type="checkbox" name="image_check[]" value="<?= $file['id'] ?>" data-action-change="image-toogle">
    </label>
    <div class="image__dashboard">
        <span class="dashboard__item delete fa fa-trash" data-action-click="image/delete" data-image-id="<?= $file['id'] ?>" data-verification="confirm"></span>
    </div>
    <div class="image__place">
        <img class="image__img" src="<?= $file['thumbnailLink'] ?>" loading="lazy" alt="<?= $file['name'] ?>" title="<?= $file['name'] ?>">
    </div>
    <div class="dropdown fa fa-ellipsis-v">
        <ul class="image__menu dropdown__menu">
            <? if ($selected): ?>
                <li class="dropdown__item"><span data-action-click="image/background/remove" data-image-id="<?= $file['id'] ?>">Прибрати з фону</span></li>
            <? else: ?>
                <li class="dropdown__item"><span data-action-click="image/background/set" data-image-id="<?= $file['id'] ?>">Додати на фон</span></li>
            <?endif?>
            <li class="dropdown__item"><span data-action-click="get-link" data-link="<?= $file['realLink'] ?>">&lt;Отримати посилання&gt;</span></li>
        </ul>
    </div>
</div>