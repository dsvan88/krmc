<div class="image">
    <div class="image__dashboard">
        <a href="/image/edit/<?= $file['id'] ?>" class="fa fa-pencil-square-o"></a>
        <a href="/image/delete/<?= $file['id'] ?>" onclick="return confirm('Are you sure?')" class="fa fa-trash"></a>
    </div>
    <img class="image__img" src="<?= $file['realLink'] ?>" loading="lazy" alt="<?= $file['name'] ?>" title="<?= $file['name'] ?>">
    <div class="image__description">
        <div><?= $file['name'] ?></div>
        <div><a href="<?= $file['realLink'] ?>" target="_blank">&lt;Link&gt;</a></div>
    </div>
</div>