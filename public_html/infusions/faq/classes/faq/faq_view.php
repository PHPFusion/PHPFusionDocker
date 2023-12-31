<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: faq_view.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\FAQ;

/**
 * Controller package
 * Class FaqView
 *
 * @package PHPFusion\Faq
 */
class FaqView extends Faq {

    /**
     * Displays FAQ
     */
    public function displayFaq() {
        if (isset($_GET['cat_id'])) {
            if (isnum($_GET['cat_id'])) {
                $info = $this->setFaqInfo($_GET['cat_id']);
                render_faq_item($info);
            } else {
                redirect(INFUSIONS."faq/faq.php");
            }
        } else {
            display_main_faq($this->setFaqInfo());
        }
    }
}
