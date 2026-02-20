<?php
$selected = $selected ?? 'image';
?>
<ul class="block__dashboard dashboard">
    <li class="dashboard__item<?= $selected === 'text' ? ' selected' : '' ?>">
        <div class="dashboard__frame">
            <p>T</p>
        </div>
    </li>
    <li class="dashboard__item<?= $selected === 'image' ? ' selected' : '' ?>">
        <div class="dashboard__frame image">
            <p class="image">I</p>
        </div>
    </li>
    <li class="dashboard__item<?= $selected === 'text-image' ? ' selected' : '' ?>">
        <div class="dashboard__frame  double">
            <p>T</p>
            <p class="image">I</p>
        </div>
    </li>
    <li class="dashboard__item<?= $selected === 'image-text' ? ' selected' : '' ?>">
        <div class="dashboard__frame double">
            <p class="image">I</p>
            <p>T</p>
        </div>
    </li>
</ul>