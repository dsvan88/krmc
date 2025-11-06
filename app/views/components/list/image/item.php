<?
$selected = '';
if (in_array($file['id'], $backgrounds, true)) {
    $selected = 'select_bg';
}
?>
<input type="radio" name="image-radio" class="image__radio" id="<?= $file['id'] ?>" data-name="<?= $file['name'] ?>" data-size="<?= $file['size'] ?>" data-action-change="show-image-info" data-action-dblclick="get-link" data-link="<?= $file['realLink'] ?>">
<label class="image <?= $selected ?>" for="<?= $file['id'] ?>">
    <label class="dashboard__label">
        <input type="checkbox" name="image_check[]" value="<?= $file['id'] ?>" data-action-change="image-toogle">
    </label>
    <div class="image__dashboard">
        <span class="dashboard__item delete fa fa-trash" data-action-click="image/delete" data-image-id="<?= $file['id'] ?>" data-verification="confirm"></span>
    </div>
    <div class="image__place">
        <img class="image__img" src="<?= $file['thumbnailLink'] ?>" loading="lazy" alt="<?= $file['name'] ?>" title="<?= $file['name'] ?>">
    </div>
</label>