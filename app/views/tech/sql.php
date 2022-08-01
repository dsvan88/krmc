<section class='section common-form'>
    <form action="/tech/sql" method="post" enctype="multipart/form-data" class="common-form__form">
        <h2 class="common-form__title"><?= $title ?></h2>
        <div>
            <textarea type="text" name="sql_query" value="" class="common-form__textarea" placeholder="SQL-query"></textarea>
        </div>
        <div class="common-form__button-place"><button type="submit" class="common-form__button"><?= $texts['SubmitLabel'] ?></button></div>
    </form>
</section>