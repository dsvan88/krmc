<section class='section form'>
    <form action="/tech/backup" method="post" enctype="multipart/form-data" class="form__form">
        <h2 class="form__title"><?= $title ?></h2>
        <select name="table" id="table">
            <option value=""></option>
            <option value="all">Всі</option>
            <option value="contacts">Контакти</option>
            <option value="pages">Сторінки</option>
            <option value="settings">Налаштування</option>
            <option value="tgchats">ТГ чати</option>
            <option value="users">Користувачі</option>
            <option value="weeks">Розклад</option>
            <option value="games">Ігри</option>
        </select>
        <div>
            <textarea type="text" name="sql_query" value="" class="form__textarea" placeholder="SQL-query"></textarea>
        </div>
        <div class="form__button-place"><button type="submit" class="form__button"><?= $texts['SubmitLabel'] ?></button></div>
    </form>
</section>