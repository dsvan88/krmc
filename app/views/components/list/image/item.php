<div class="image">
    <div class="image__dashboard">
        <span class="dashboard__item delete fa fa-trash" data-action-click="image/delete" data-image-id="<?= $file['id'] ?>" data-verification="confirm"></span>
    </div>
    <div class="image__place">
        <img class="image__img" src="<?= $file['realLink'] ?>" loading="lazy" alt="<?= $file['name'] ?>" title="<?= $file['name'] ?>">
    </div>
    <div class="dropdown fa fa-ellipsis-v">
        <ul class="image__menu dropdown__menu">
            <li class="dropdown__item"><a href="<?= $file['realLink'] ?>" target="_blank">&lt;Link&gt;</a></li>
            <li class="dropdown__item"><a href="<?= $file['realLink'] ?>" target="_blank">&lt;Link&gt;</a></li>
            <li class="dropdown__item"><a href="<?= $file['realLink'] ?>" target="_blank">&lt;Link&gt;</a></li>
        </ul>
    </div>
</div>