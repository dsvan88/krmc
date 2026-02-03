<div class="dashboard">
    <input type="checkbox" class="hidden" name="dashboard-control" id="dashboard-control" checked>
    <menu class="dashboard__list">
        <?php foreach ($dashboard as $item) : ?>
            <li class="dashboard__item">
                <a href='/<?= $item['link'] ?>' class="fa fa-<?= $item['icon'] ?>" title='<?= $item['label'] ?>'></a>
            </li>
        <?php endforeach ?>
    </menu>
    <label class="dashboard__hide fa fa-chevron-up" for="dashboard-control"></label>
    <label class="dashboard__show fa fa-chevron-down" for="dashboard-control"></label>
</div>