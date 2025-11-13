<input type="radio" name="image-radio" class="image__radio" id="r_<?= $image['id'] ?>">
<label class="image <?= $selected ?>" for="r_<?= $image['id'] ?>">
    <div class="image__place">
        <img class="image__img" src="<?= $image['realLink'] ?>" loading="lazy" alt="<?= $image['name'] ?>" title="<?= $image['name'] ?>">
    </div>
</label>