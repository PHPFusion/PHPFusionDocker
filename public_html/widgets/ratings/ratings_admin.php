<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: ratings_admin.php
| Author: Frederick MC Chan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

/**
 * Class ratingsWidgetAdmin
 */
class ratingsWidgetAdmin extends \PHPFusion\Page\Composer\Node\ComposeEngine implements \PHPFusion\Page\WidgetAdminInterface {

    private static $instance = NULL;

    public static function widgetInstance() {
        if (self::$instance === NULL) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function excludeReturn() {
    }

    public function validateInput() {
    }

    public function validateDelete() {
        dbquery("DELETE FROM ".DB_RATINGS." WHERE rating_type='C' AND rating_item_id=".self::$data['page_id']);
    }

    public function validateSettings() {
    }

    public function displayFormButton() {
        return '';
    }

    public function displayFormInput() {
        $lang = file_exists(WIDGETS."ratings/locale/".LANGUAGE.".php") ? WIDGETS."ratings/locale/".LANGUAGE.".php" : WIDGETS."ratings/locale/English.php";
        $widget_locale = fusion_get_locale('', $lang);

        self::$colData['page_content'] = 'ratings';
        self::$colData['page_content_id'] = 0;
        $colId = dbquery_insert(DB_CUSTOM_PAGES_CONTENT, self::$colData, 'save');
        if ($colId) {
            addnotice('success', $widget_locale['RTW_0102']);
        } else {
            addnotice('danger', $widget_locale['RTW_0104']);
        }
        redirect(clean_request('', self::getComposerExclude(), FALSE));
    }

}
