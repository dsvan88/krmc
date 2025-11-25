<input type="radio" name="image-radio" class="image__radio" id="r_<?= $file['id'] ?>" data-name="<?= $file['name'] ?>" data-action-change="show-folder-info" data-action-dblclick="open-images-folder" data-fid="<?= $file['id'] ?>">
<label class="folder" for="r_<?= $file['id'] ?>">
    <div class="folder__dashboard">
        <span class="dashboard__item delete fa fa-trash" data-action-click="image/delete" data-image-id="<?= $file['id'] ?>" data-verification="verification/root"></span>
    </div>
    <div class="folder__place">
        <i class="folder__folder fa fa-folder-o" title="<?= $file['name'] ?>"></i>
    </div>
    <div class="folder__name"><?= $file['name'] ?></div>
</label>