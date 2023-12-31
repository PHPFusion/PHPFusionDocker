<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: OpenGraphArticles.php
| Author: Chubatyj Vitalij (Rizado)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion;

class OpenGraphArticles extends OpenGraph {
    public static function ogArticle($article_id = 0) {
        $settings = fusion_get_settings();
        $info = [];

        $result = dbquery("SELECT article_subject, article_snippet, article_keywords, article_thumbnail FROM ".DB_ARTICLES." WHERE article_id = :article", [':article' => $article_id]);
        if (dbrows($result)) {
            $data = dbarray($result);
            $info['title'] = $data['article_subject'].' - '.$settings['sitename'];
            $info['description'] = !empty($data['article_snippet']) ? fusion_first_words(strip_tags(html_entity_decode($data['article_snippet'])), 50) : $settings['description'];
            $info['url'] = $settings['siteurl'].'infusions/articles/articles.php?readmore='.$article_id;
            $info['keywords'] = !empty($data['article_keywords']) ? $data['article_keywords'] : $settings['keywords'];
            $info['type'] = 'article';

            if (!empty($data['article_thumbnail']) && file_exists(IMAGES_A.'thumbs/'.$data['article_thumbnail'])) {
                $info['image'] = $settings['siteurl'].'infusions/articles/images/thumbs/'.$data['article_thumbnail'];
            }
        }

        self::setValues($info);
    }

    public static function ogArticleCat($cat_id = 0) {
        $settings = fusion_get_settings();
        $info = [];
        $result = dbquery("SELECT article_cat_name, article_cat_description FROM ".DB_ARTICLE_CATS." WHERE article_cat_id = :cat_id", [':cat_id' => $cat_id]);

        if (dbrows($result)) {
            $data = dbarray($result);
            $info['title'] = $data['article_cat_name'].' - '.$settings['sitename'];
            $info['description'] = !empty($data['article_cat_description']) ? fusion_first_words(strip_tags(html_entity_decode($data['article_cat_description'])), 50) : $settings['description'];
            $info['url'] = $settings['siteurl'].'infusions/articles/articles.php?cat_id='.$cat_id;
            $info['keywords'] = $settings['keywords'];
        }

        self::setValues($info);
    }
}
