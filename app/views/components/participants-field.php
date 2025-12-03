<div class="participant">
    <label for="participant-<?= $participantId ?>" class="participant__num"><?= ($participantId + 1) ?></label>
    <div class="participant__info">
        <input name="participant[]" class="participant__name" id="participant-<?= $participantId ?>" type="text" value="<?= empty($participant) ? '' : $participant['name'] ?>" data-action-input="autocomplete-users-names" list="users-names-list" autocomplete="off" data-action-change="participant-check-change" />
        <input name="arrive[]" class="participant__arrive" list="time-list" type="text" value="<?= empty($participant) ? '' : $participant['arrive'] ?>" autocomplete="off" placeholder="<?= $texts['ArrivePlaceHolder'] ?>" />
        <input name="prim[]" class="participant__prim" type="text" value="<?= empty($participant) ? '' : $participant['prim'] ?>" placeholder="<?= $texts['RemarkPlaceHolder'] ?>">
    </div>
</div>