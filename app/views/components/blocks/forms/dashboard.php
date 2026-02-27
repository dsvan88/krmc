<?php
$selected = $selected ?? 'text';
?>
<ul class="block__dashboard dashboard">
    <li class="dashboard__item<?= $selected === 'text' ? ' selected' : '' ?>" data-action-click="pages/set-block-type" data-block-type="text">
        <div class="dashboard__frame">
            <p>TXT</p>
        </div>
    </li>
    <li class="dashboard__item<?= $selected === 'image' ? ' selected' : '' ?>" data-action-click="pages/set-block-type" data-block-type="image">
        <div class="dashboard__frame image">
            <p class="image">IMG</p>
        </div>
    </li>
    <li class="dashboard__item<?= $selected === 'text-image' ? ' selected' : '' ?>" data-action-click="pages/set-block-type" data-block-type="text-image">
        <div class="dashboard__frame  double">
            <p>TXT</p>
            <p class="image">IMG</p>
        </div>
    </li>
    <li class="dashboard__item<?= $selected === 'image-text' ? ' selected' : '' ?>" data-action-click="pages/set-block-type" data-block-type="image-text">
        <div class="dashboard__frame double">
            <p class="image">IMG</p>
            <p>TXT</p>
        </div>
    </li>
    <li class="dashboard__item<?= $selected === 'text-text' ? ' selected' : '' ?>" data-action-click="pages/set-block-type" data-block-type="text-text">
        <div class="dashboard__frame double">
            <p>TXT</p>
            <p>TXT</p>
        </div>
    </li>
</ul>