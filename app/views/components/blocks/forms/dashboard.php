<?php
$selected = $selected ?? 'image';
?>
<ul class="block__dashboard dashboard">
    <li class="dashboard__item<?= $selected === 'text' ? ' selected' : '' ?>">
        <div class="dashboard__frame">
            <p>TXT</p>
        </div>
    </li>
    <li class="dashboard__item<?= $selected === 'image' ? ' selected' : '' ?>">
        <div class="dashboard__frame image">
            <p class="image">IMG</p>
        </div>
    </li>
    <li class="dashboard__item<?= $selected === 'text-image' ? ' selected' : '' ?>">
        <div class="dashboard__frame  double">
            <p>TXT</p>
            <p class="image">IMG</p>
        </div>
    </li>
    <li class="dashboard__item<?= $selected === 'image-text' ? ' selected' : '' ?>">
        <div class="dashboard__frame double">
            <p class="image">IMG</p>
            <p>TXT</p>
        </div>
    </li>
</ul>