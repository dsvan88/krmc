<section class='section form'>
    <form action="/tech/restore" method="post" enctype="multipart/form-data" class="form__form">
        <h2 class="form__title"><?= $title ?></h2>
        <input type="file" name="data" style="display:block">
        <input type="input" name="table">
        <div>
            <textarea type="text" name="sql_query" value="" class="form__textarea" placeholder="SQL-query"></textarea>
        </div>
        <div class="form__button-place"><button type="submit" class="form__button"><?= $texts['SubmitLabel'] ?></button></div>
    </form>
</section>