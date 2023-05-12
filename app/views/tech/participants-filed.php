<div class="booking__participant">
    <label for="booking__participant-<?= $newId ?>" class="booking__participant-num"><?= $participantNum ?>.</label>
    <div class="booking__participant-info">
        <input name="participant[]" type="text" value="" class="booking__participant-name" data-action-input="autocomplete-users-names" list="users-names-list" autocomplete="off" placeholder="<?= $texts['NamePlaceholder'] ?>" /><? //data-action-change="participant-check-change"
                                                                                                                                                                                                                                    ?>
        <input name="arrive[]" list="time-list" type="text" class="booking__participant-arrive" value="" autocomplete="off" placeholder="<?= $texts['TimeArrivePlaceholder'] ?>" />
        <input name="prim[]" type="text" class="booking__participant-prim" value="" placeholder="<?= $texts['RemarkPlaceHolder'] ?>">
        <i class="fa fa-minus-circle booking__participant-remove" data-action-click="participant-field-remove" title="<?= $texts['ClearLabel'] ?>"></i>
    </div>
</div>