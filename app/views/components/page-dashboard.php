<span class='page__dashboard' style='float:right'>
    <a href='/page/edit/<?=$dashboard['slug']?>' title='<?=$texts['edit']?>' class='fa fa-pencil-square-o'></a>
    <? if ($dashboard['slug'] !== 'home') : ?>
        <a href='/page/delete/<?=$dashboard['id']?>' onclick='return confirm("Are you sure?")' title='<?=$texts['delete']?>' class='fa fa-trash-o'></a>
    <? endif ?>
</span>