<div class="booking__participant">
    <label for="booking__participant-<?= $participantId ?>" class="booking__participant-num"><?= ($participantId + 1) ?>.</label>
    <div class="booking__participant-info">
        <input name="participant[]" id="booking__participant-<?= $participantId ?>" type="text" value="<?= empty($participant) ? '' : $participant['name'] ?>" class="booking__participant-name" data-action-input="autocomplete-users-names" list="users-names-list" autocomplete="off" data-action-change="participant-check-change" />
        <input name="arrive[]" list="time-list" type="text" class="booking__participant-arrive" value="<?= empty($participant) ? '' : $participant['arrive'] ?>" autocomplete="off" placeholder="<?= $texts['ArrivePlaceHolder'] ?>"/>
        <input name="prim[]" type="text" class="booking__participant-prim" value="<?= empty($participant) ? '' : $participant['prim'] ?>" placeholder="<?= $texts['RemarkPlaceHolder'] ?>">
        <i class="fa fa-minus-circle booking__participant-remove" data-action-click="participant-field-clear" title="<?= $texts['clearLabel'] ?>"></i>
    </div>
</div>