<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: maintenance.php
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
require_once __DIR__.'/maincore.php';

$settings = fusion_get_settings();

if (!$settings['maintenance']) {
    redirect(BASEDIR.'index.php');
}

if ($settings['site_seo'] == 1 && !defined("IN_PERMALINK")) {
    \PHPFusion\Rewrite\Permalinks::getPermalinkInstance()->handleUrlRouting("");
}

$locale = fusion_get_locale();

$info = [];

ob_start();

if (!iMEMBER) {
    switch ($settings['login_method']) {
        case "2" :
            $placeholder = $locale['global_101c'];
            break;
        case "1" :
            $placeholder = $locale['global_101b'];
            break;
        default:
            $placeholder = $locale['global_101a'];
    }
    $user_name = isset($_POST['user_name']) ? form_sanitizer($_POST['user_name'], "", "user_name") : "";
    $user_password = isset($_POST['user_pass']) ? form_sanitizer($_POST['user_pass'], "", "user_pass") : "";
    $path = $settings['opening_page'];
    if (!defined('IN_PERMALINK')) {
        $path = BASEDIR.(!stristr($settings['opening_page'], '.php') ? $settings['opening_page'].'/index.php' : $settings['opening_page']);
    }
    $info = [
        "open_form"            => openform('loginpageform', 'POST', $path),
        "user_name"            => form_text('user_name', "", $user_name, ['placeholder' => $placeholder, 'inline' => TRUE]),
        "user_pass"            => form_text('user_pass', "", $user_password, [
            'placeholder' => $locale['global_102'],
            'type'        => 'password', 'inline' => TRUE
        ]),
        "remember_me"          => form_checkbox('remember_me', $locale['global_103'], ""),
        "login_button"         => form_button('login', $locale['global_104'], $locale['global_104'], [
            'class' => 'btn-primary btn-block m-b-20'
        ]),
        "registration_link"    => $settings['enable_registration'] ? "<p>".$locale['global_105']."</p>\n" : "",
        "forgot_password_link" => $locale['global_106'],
        "close_form"           => closeform(),
        'maintenance_message'  => parse_text($settings['maintenance_message'], [
            'parse_bbcode'    => FALSE,
            'add_line_breaks' => TRUE
        ])
    ];
}

require_once THEME."theme.php";
if (file_exists(INCLUDES."header_includes.php")) {
    require_once INCLUDES."header_includes.php";
}
require_once INCLUDES."theme_functions_include.php";
include THEMES."templates/global/maintenance.tpl.php";

header("Content-Type: text/html; charset=".$locale['charset']."");
echo "<!DOCTYPE html>\n";
echo "<html lang='".$locale['xml_lang']."' dir='".$locale['text-direction']."'>\n";
echo "<head>\n";
echo "<title>".$settings['sitename']."</title>\n";
echo "<meta charset='".$locale['charset']."'>\n";
echo "<meta name='description' content='".$settings['description']."'>\n";
echo "<meta name='url' content='".$settings['siteurl']."'>\n";
echo "<meta name='keywords' content='".$settings['keywords']."'>\n";
echo "<meta name='image' content='".$settings['siteurl'].$settings['sitebanner']."'>\n";

if ((defined('BOOTSTRAP') && BOOTSTRAP == TRUE) || (defined('BOOTSTRAP4') && BOOTSTRAP4 == TRUE)) {
    if (defined('BOOTSTRAP4')) {
        echo '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">';
        echo '<link rel="stylesheet" href="'.INCLUDES.'bootstrap/bootstrap4/css/bootstrap.min.css">';
    } else {
        echo '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
        echo '<link rel="stylesheet" href="'.INCLUDES.'bootstrap/bootstrap3/css/bootstrap.min.css">';

        if ($locale['text-direction'] == 'rtl') {
            echo '<link rel="stylesheet" href="'.INCLUDES.'bootstrap/bootstrap3/css/bootstrap-rtl.min.css">';
        }
    }
}

if (defined('ENTYPO') && ENTYPO == TRUE) {
    echo "<link rel='stylesheet' href='".INCLUDES."fonts/entypo/entypo.min.css'>\n";
}

if (defined('FONTAWESOME') && FONTAWESOME == TRUE) {
    echo "<link rel='stylesheet' href='".INCLUDES."fonts/font-awesome-5/css/all.min.css'>\n";
}

if (!defined('NO_DEFAULT_CSS')) {
    echo "<link rel='stylesheet' href='".THEMES."templates/default.min.css?v=".filemtime(THEMES.'templates/default.min.css')."'>\n";
}

$theme_css = file_exists(THEME.'styles.min.css') ? THEME.'styles.min.css' : THEME.'styles.css';
echo "<link rel='stylesheet' href='".$theme_css."?v=".filemtime($theme_css)."'>\n";

/*if (defined('BOOTSTRAP') && BOOTSTRAP == TRUE) {
    $user_theme = fusion_get_userdata('user_theme');
    $theme_name = $user_theme !== 'Default' ? $user_theme : $settings['theme'];
    $theme_data = dbarray(dbquery("SELECT theme_file FROM ".DB_THEME." WHERE theme_name='".$theme_name."' AND theme_active='1'"));

    if (!empty($theme_data)) {
        echo "<link rel='stylesheet' href='".THEMES.$theme_data['theme_file']."'>\n";
    }
}*/

echo render_favicons(defined('THEME_ICON') ? THEME_ICON : IMAGES.'favicons/');

echo "<script src='".INCLUDES."jquery/jquery.min.js'></script>\n";
echo "</head>";
echo "<body class='maintenance-page'>";

display_maintenance($info);

echo \PHPFusion\OutputHandler::$pageFooterTags;
$fusion_jquery_tags = PHPFusion\OutputHandler::$jqueryCode;
if (!empty($fusion_jquery_tags)) {
    $minifier = new PHPFusion\Minify\JS($fusion_jquery_tags);
    echo "<script>$(function(){".$minifier->minify()."});</script>\n";
}

if ((defined('BOOTSTRAP') && BOOTSTRAP == TRUE) || (defined('BOOTSTRAP4') && BOOTSTRAP4 == TRUE)) {
    if (defined('BOOTSTRAP4')) {
        echo '<script src="'.INCLUDES.'bootstrap/bootstrap4/js/bootstrap.bundle.min.js"></script>';
    } else {
        echo '<script src="'.INCLUDES.'bootstrap/bootstrap3/js/bootstrap.min.js"></script>';
    }
}
echo "</body>\n";
echo "</html>";

$output = ob_get_contents();
if (ob_get_length() !== FALSE) {
    ob_end_clean();
}
$output = handle_output($output);
echo $output;
if ((ob_get_length() > 0)) {
    ob_end_flush();
}
