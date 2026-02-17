<?php

use app\core\ImageProcessing;

?>
<section class="section news-list">
    <h2 class="news-preview__title section__title"><?= $title ?>
        <?php if ($setDashBoard) : ?>
            <a href="/news/add"><i class="fa fa-plus-square"></i></a>
        <?php endif ?>
    </h2>
    <div class="news-preview__list">
        <?php
        for ($i = 0; $i < count($newsAll); $i++) :

            if (mb_strlen($newsAll[$i]['html'], 'UTF-8') > 150)
                $newsAll[$i]['html'] = mb_substr(preg_replace('/<[^>]+?>/i', '', $newsAll[$i]['html']), 0, 250) . '...';
        ?>
            <div class="news-preview__item">
                <div class="news-preview__item-title">
                    <div class="news-preview__item-logo">
                        <?php
                        if (!empty($newsAll[$i]['logo']))
                            echo ImageProcessing::inputImage(FILE_MAINGALL . 'news/' . $newsAll[$i]['logo'], ['title' => $newsAll[$i]['title']]);
                        else
                            echo ImageProcessing::inputImage($defaultImage['value'], ['title' => $newsAll[$i]['title']])
                        ?>
                    </div>
                    <h3 class="news-preview__item-title-text"><?= $newsAll[$i]['title'] ?></h3>
                </div>
                <h4 class="news-preview__item-subtitle"><?= $newsAll[$i]['title'] ?></h4>
                <div class="news-preview__item-content"><?= $newsAll[$i]['html'] ?></div>
                <div class="news-preview__read-more">
                    <?php if ($setDashBoard) : ?>
                        <span class='news-preview__dashboard'>
                            <a href='/news/edit/<?= $newsAll[$i]['id'] ?>'>
                                <i class='fa fa-pencil-square-o news-dashboard__button' title='Редагувати новину'></i>
                            </a>
                            <a onclick="return confirm('Are you sure, you want to delete News with id: <?= $newsAll[$i]['id'] ?>')" href='/news/delete/<?= $newsAll[$i]['id'] ?>'>
                                <i class='fa fa-trash-o news-dashboard__button' title='Видалити новину'></i>
                            </a>
                        </span>
                    <?php endif ?>
                    <a class="news-preview__read-more-link" href="/news/show/<?= $newsAll[$i]['id'] ?>"><?= $texts['ReadMore'] ?></a>
                </div>
            </div>
        <?php endfor; ?>
    </div>
    <?php if ($newsCount > CFG_NEWS_PER_PAGE) : ?>
        <div class="paginator">
            <div class="paginator__links"><?= $paginator ?></div>
        </div>
    <?php endif; ?>
</section>