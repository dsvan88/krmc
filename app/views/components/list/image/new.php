<?
if (empty($type)) $type = 'images';
?>

<form class="image new">
    <label for="new_image" class="label fa fa-plus-circle"></label>
    <input type="file" name="image" id="new_image" accept=".png,.jpg,.jpeg,.webp" data-action-change="image/add" data-type="<?= $type ?>" class="hidden" multiple>
</form>