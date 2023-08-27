<span class='page__dashboard' style='float:right'>
    <a href='/page/edit/<?=$dashboard?>' title='<?=$texts['edit']?>' class='fa fa-pencil-square-o'></a>
    <? if (!in_array($dashboard, ['home', '1'])) : ?>
        <a href='/page/delete/<?=$dashboard?>' onclick='return confirm("Are you sure?")' title='<?=$texts['delete']?>' class='fa fa-trash-o'></a>
    <? endif ?>
</span>