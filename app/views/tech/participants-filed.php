<div class="booking__participant">
    <label for="booking__participant-<?= $newId ?>" class="booking__participant-num"><?= $participantNum ?>.</label>
    <div class="booking__participant-info">
        <input name="participant[]" type="text" value="" class="booking__participant-name" data-action-input="autocomplete-users-names" list="users-names-list" autocomplete="off" placeholder="<?= $texts['participantNamePlaceholder'] ?>" /><? //data-action-change="participant-check-change"
                                                                                                                                                                                                                                                ?>
        <input name="arrive[]" list="time-list" type="text" class="booking__participant-arrive" value="" autocomplete="off" placeholder="<?= $texts['participantTimeArrivePlaceholder'] ?>" />
        <input name="prim[]" value="" placeholder="<?= $texts['participantRemarkPlaceHolder'] ?>">
        <i class="fa fa-minus-circle booking__participant-remove" data-action-click="participant-field-remove" title="<?= $texts['clearLabel'] ?>"></i>
    </div>
</div>