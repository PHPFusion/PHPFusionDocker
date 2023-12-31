<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: infusion.php
| Author: J.Falk (Falk)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

$locale = fusion_get_locale("", LOCALE.LOCALESET."setup.php");

// Infusion general information
$inf_title = $locale['downloads']['title'];
$inf_description = $locale['downloads']['description'];
$inf_version = "1.2.0";
$inf_developer = "PHP Fusion Development Team";
$inf_email = "info@phpfusion.com";
$inf_weburl = "https://phpfusion.com";
$inf_folder = "downloads";
$inf_image = "download.svg";

// Create tables
$inf_newtable[] = DB_DOWNLOADS." (
    download_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    download_user MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    download_homepage VARCHAR(100) NOT NULL DEFAULT '',
    download_title VARCHAR(100) NOT NULL DEFAULT '',
    download_description_short VARCHAR(255) NOT NULL,
    download_description TEXT NOT NULL,
    download_keywords VARCHAR(250) NOT NULL DEFAULT '',
    download_image VARCHAR(100) NOT NULL DEFAULT '',
    download_image_thumb VARCHAR(100) NOT NULL DEFAULT '',
    download_url TEXT NOT NULL,
    download_file VARCHAR(100) NOT NULL DEFAULT '',
    download_cat MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    download_license VARCHAR(50) NOT NULL DEFAULT '',
    download_copyright VARCHAR(250) NOT NULL DEFAULT '',
    download_os VARCHAR(50) NOT NULL DEFAULT '',
    download_version VARCHAR(20) NOT NULL DEFAULT '',
    download_filesize VARCHAR(20) NOT NULL DEFAULT '',
    download_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',
    download_visibility VARCHAR(50) NOT NULL DEFAULT '0',
    download_count INT(10) UNSIGNED NOT NULL DEFAULT '0',
    download_allow_comments TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    download_allow_ratings TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (download_id),
    KEY download_user (download_user),
    KEY download_cat (download_cat),
    KEY download_count (download_count),
    KEY download_datestamp (download_datestamp)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

$inf_newtable[] = DB_DOWNLOAD_CATS." (
    download_cat_id MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    download_cat_parent MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
    download_cat_name VARCHAR(100) NOT NULL DEFAULT '',
    download_cat_description TEXT NOT NULL,
    download_cat_sorting VARCHAR(50) NOT NULL DEFAULT 'download_title ASC',
    download_cat_language VARCHAR(50) NOT NULL DEFAULT '".LANGUAGE."',
    PRIMARY KEY (download_cat_id),
    KEY download_cat_parent (download_cat_parent)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";

// Insert settings
$settings = [
    'download_max_b'               => 15728640,
    'download_types'               => '.pdf,.gif,.jpg,.png,.zip,.rar,.tar,.bz2,.7z',
    'download_screen_max_b'        => 26214400,
    'download_screen_max_w'        => 1024,
    'download_screen_max_h'        => 768,
    'download_thumb_max_w'         => 800,
    'download_thumb_max_h'         => 640,
    'download_screenshot'          => 1,
    'download_stats'               => 1,
    'download_pagination'          => 15,
    'download_allow_submission'    => 1,
    'download_screenshot_required' => 1,
    'download_extended_required'   => 1,
    'download_submission_access'   => USER_LEVEL_MEMBER
];

foreach ($settings as $name => $value) {
    $inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('".$name."', '".$value."', '".$inf_folder."')";
}

// Multilanguage table
$inf_mlt[] = [
    "title"  => $locale['downloads']['title'],
    "rights" => "DL"
];

// Multilanguage links
$enabled_languages = makefilelist(LOCALE, ".|..", TRUE, "folders");
if (!empty($enabled_languages)) {
    foreach ($enabled_languages as $language) {
        if (file_exists(LOCALE.$language.'/setup.php')) {
            include LOCALE.$language.'/setup.php';
        } else {
            include LOCALE.'English/setup.php';
        }

        $mlt_adminpanel[$language][] = [
            "rights"   => "D",
            "image"    => $inf_image,
            "title"    => $locale['setup_3010'],
            "panel"    => "downloads_admin.php",
            "page"     => 1,
            'language' => $language
        ];

        // Add
        $mlt_insertdbrow[$language][] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES ('".$locale['setup_3302']."', 'infusions/downloads/downloads.php', '0', '2', '0', '2', '1', '".$language."')";

        // Delete
        $mlt_deldbrow[$language][] = DB_SITE_LINKS." WHERE link_url='infusions/downloads/downloads.php' AND link_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_DOWNLOAD_CATS." WHERE download_cat_language='".$language."'";
        $mlt_deldbrow[$language][] = DB_ADMIN." WHERE admin_rights='D' AND admin_language='".$language."'";
    }
} else {
    $inf_adminpanel[] = [
        "rights"   => "D",
        "image"    => $inf_image,
        "title"    => $locale['setup_3010'],
        "panel"    => "downloads_admin.php",
        "page"     => 1,
        'language' => LANGUAGE
    ];

    $inf_insertdbrow[] = DB_SITE_LINKS." (link_name, link_url, link_visibility, link_position, link_window, link_order, link_status, link_language) VALUES('".$locale['setup_3302']."', 'infusions/downloads/downloads.php', '0', '2', '0', '2', '1', '".LANGUAGE."')";
}

// Uninstallation
$inf_droptable[] = DB_DOWNLOADS;
$inf_droptable[] = DB_DOWNLOAD_CATS;
$inf_deldbrow[] = DB_COMMENTS." WHERE comment_type='D'";
$inf_deldbrow[] = DB_RATINGS." WHERE rating_type='D'";
$inf_deldbrow[] = DB_SUBMISSIONS." WHERE submit_type='d'";
$inf_deldbrow[] = DB_SETTINGS_INF." WHERE settings_inf='".$inf_folder."'";
$inf_deldbrow[] = DB_ADMIN." WHERE admin_rights='D'";
$inf_deldbrow[] = DB_SITE_LINKS." WHERE link_url='infusions/downloads/downloads.php'";
$inf_deldbrow[] = DB_LANGUAGE_TABLES." WHERE mlt_rights='DL'";
$inf_delfiles[] = IMAGES_D;
$inf_delfiles[] = INFUSIONS."downloads/files/";
