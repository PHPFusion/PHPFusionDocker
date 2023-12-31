<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: blog_cat.php
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
pageaccess('BLOG');

$locale = fusion_get_locale();
$aidlink = fusion_get_aidlink();

/**
 * Delete category images
 */
if ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
    $result = dbcount("(blog_cat)", DB_BLOG, "blog_cat='".$_GET['cat_id']."'") || dbcount("(blog_cat_id)", DB_BLOG_CATS, "blog_cat_parent='".$_GET['cat_id']."'");
    if (!empty($result)) {
        addnotice("danger", $locale['blog_0522']." - <span class='small'>".$locale['blog_0523']."</span>");
    } else {
        $result = dbquery("SELECT blog_cat_image FROM ".DB_BLOG_CATS." WHERE blog_cat_id='".intval($_GET['cat_id'])."'");
        if (dbrows($result) > 0) {
            $photo = dbarray($result);
            if (!empty($photo['blog_cat_image']) && file_exists(IMAGES_BC.$photo['blog_cat_image'])) {
                unlink(IMAGES_BC.$photo['blog_cat_image']);
            }
        }

        dbquery("DELETE FROM ".DB_BLOG_CATS." WHERE blog_cat_id='".intval($_GET['cat_id'])."'");
        addnotice("success", $locale['blog_0524b']);
    }
    redirect(FUSION_SELF.$aidlink);
    redirect(clean_request("", ["action", "ref", "cat_id"], FALSE));
}
$data = [
    "blog_cat_id"       => 0,
    "blog_cat_name"     => "",
    "blog_cat_hidden"   => [],
    "blog_cat_parent"   => 0,
    "blog_cat_image"    => "",
    "blog_cat_language" => LANGUAGE,
];
$formAction = FUSION_REQUEST;
$formTitle = $locale['blog_0409'];
// if edit, override $data
if (isset($_POST['save_cat'])) {
    $inputArray = [
        "blog_cat_id"       => form_sanitizer($_POST['blog_cat_id'], 0, "blog_cat_id"),
        "blog_cat_name"     => form_sanitizer($_POST['blog_cat_name'], "", "blog_cat_name"),
        "blog_cat_parent"   => form_sanitizer($_POST['blog_cat_parent'], 0, "blog_cat_parent"),
        "blog_cat_image"    => form_sanitizer($_POST['blog_cat_image'], "", "blog_cat_image"),
        "blog_cat_language" => form_sanitizer($_POST['blog_cat_language'], LANGUAGE, "blog_cat_language"),
    ];
    $categoryNameCheck = [
        "when_updating" => "blog_cat_name='".$inputArray['blog_cat_name']."' and blog_cat_id !='".$inputArray['blog_cat_id']."' ".(multilang_table("BL") ? "and ".in_group('blog_cat_language', LANGUAGE) : ""),
        "when_saving"   => "blog_cat_name='".$inputArray['blog_cat_name']."' ".(multilang_table("BL") ? "and ".in_group('blog_cat_language', LANGUAGE) : ""),
    ];
    if (fusion_safe()) {
        // check category name is unique when updating
        if (dbcount("(blog_cat_id)", DB_BLOG_CATS, "blog_cat_id='".$inputArray['blog_cat_id']."'")) {
            if (!dbcount("(blog_cat_id)", DB_BLOG_CATS, $categoryNameCheck['when_updating'])) {
                dbquery_insert(DB_BLOG_CATS, $inputArray, "update");
                addnotice("success", $locale['blog_0521']);
                // FUSION_REQUEST without the "action" gets
                redirect(clean_request("", ["action"], FALSE));
            } else {
                addnotice('danger', $locale['blog_0561']);
            }
        } else {
            // check category name is unique when saving new
            if (!dbcount("(blog_cat_id)", DB_BLOG_CATS, $categoryNameCheck['when_saving'])) {
                dbquery_insert(DB_BLOG_CATS, $inputArray, "save");
                addnotice("success", $locale['blog_0520']);
                redirect(FUSION_REQUEST);
            } else {
                addnotice('danger', $locale['blog_0561']);
            }
        }
    }
} else if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
    $result = dbquery("SELECT blog_cat_id, blog_cat_name, blog_cat_parent, blog_cat_image, blog_cat_language FROM ".DB_BLOG_CATS." ".(multilang_table("BL") ? "WHERE ".in_group('blog_cat_language', LANGUAGE)." AND" : "WHERE")." blog_cat_id='".intval($_GET['cat_id'])."'");
    if (dbrows($result)) {
        $data = dbarray($result);
        $data['blog_cat_hidden'] = [$data['blog_cat_id']];
        $formTitle = $locale['blog_0402'];
    } else {
        // FUSION_REQUEST without the "action" gets
        redirect(clean_request("", ["action"], FALSE));
    }
}
add_breadcrumb(['link' => FUSION_REQUEST, 'title' => $formTitle]);

echo '<h3 class="m-t-0">'.$formTitle.'</h3>';

echo openform("addcat", "post", $formAction);
openside("");
echo form_hidden("blog_cat_id", "", $data['blog_cat_id']);
echo form_text("blog_cat_name", $locale['blog_0530'], $data['blog_cat_name'], [
    "required"   => TRUE,
    "inline"     => TRUE,
    "error_text" => $locale['blog_0560']
]);
echo form_select_tree("blog_cat_parent", $locale['blog_0533'], $data['blog_cat_parent'], [
    "inline"        => TRUE,
    "disable_opts"  => $data['blog_cat_hidden'],
    "hide_disabled" => TRUE,
    "query"         => (multilang_table("BL") ? "WHERE ".in_group('blog_cat_language', LANGUAGE) : "")
], DB_BLOG_CATS, "blog_cat_name", "blog_cat_id", "blog_cat_parent");
if (multilang_table("BL")) {
    echo form_select("blog_cat_language[]", $locale['global_ML100'], $data['blog_cat_language'], [
        "inline"      => TRUE,
        "options"     => fusion_get_enabled_languages(),
        "placeholder" => $locale['choose'],
        'multiple'    => TRUE
    ]);
} else {
    echo form_hidden("blog_cat_language", "", $data['blog_cat_language']);
}
echo form_select("blog_cat_image", $locale['blog_0531'], $data['blog_cat_image'], [
    "inline"  => TRUE,
    "options" => blog_cat_image_opts(),
]);
echo form_button("save_cat", $locale['blog_0532'], $locale['blog_0532'], ["class" => "btn-success", "icon" => "fa fa-hdd-o"]);
closeside();
echo closeform();

echo "<div class='overflow-hide'>";
echo "<div class='pull-right'><a class='btn btn-primary' href='".ADMIN."images.php".$aidlink."&ifolder=imagesbc&section=upload'>".$locale['blog_0536']."</a><br /><br />\n</div>\n";
echo "<h4>".$locale['blog_0407']."</h4>\n";
echo "</div>";
$result = dbquery("SELECT blog_cat_id, blog_cat_name FROM ".DB_BLOG_CATS." ".(multilang_table("BL") ? "WHERE ".in_group('blog_cat_language', LANGUAGE) : "")." ORDER BY blog_cat_name");
$rows = dbrows($result);
if ($rows != 0) {
    echo "<div class='row equal-height'>";
    while ($data = dbarray($result)) {
        echo "<div class='col-xs-12 col-sm-3'>";
        echo "<div class='well clearfix'>\n";
        echo "<div class='pull-left' style='width:70px;'>\n";
        echo thumbnail(get_image("bl_".$data['blog_cat_name']), '50px');
        echo "</div>\n";
        echo "<div class='overflow-hide'><h4 class='m-b-5 m-t-5'>".get_blog_cat_path($data['blog_cat_id'])."</h4>";
        echo "<span><a href='".clean_request("action=edit&cat_id=".$data['blog_cat_id'], ['aid', 'section'])."'>".$locale['edit']."</a> &middot; ";
        echo "<a href='".clean_request("action=delete&cat_id=".$data['blog_cat_id'], ['aid', 'section'])."' onclick=\"return confirm('".$locale['blog_0550']."');\">".$locale['delete']."</a></span>\n";
        echo "</div>\n</div>\n";
        echo "</div>";
    }
    echo "</div>";
} else {
    echo "<div class='well text-center'>".$locale['blog_0461']."</div>\n";
}

echo '</div>';

function get_blog_cat_path($item_id) {
    $full_path = "";
    while ($item_id > 0) {
        $result = dbquery("SELECT blog_cat_id, blog_cat_name, blog_cat_parent FROM ".DB_BLOG_CATS." WHERE blog_cat_id='$item_id'".(multilang_table("BL") ? " AND ".in_group('blog_cat_language', LANGUAGE) : ""));
        if (dbrows($result)) {
            $data = dbarray($result);
            if ($full_path) {
                $full_path = " / ".$full_path;
            }
            $full_path = $data['blog_cat_name'].$full_path;
            $item_id = $data['blog_cat_parent'];
        }
    }

    return $full_path;
}

function blog_cat_image_opts() {
    $image_files = makefilelist(IMAGES_BC, ".|..|index.php");
    $image_list = [];
    foreach ($image_files as $image) {
        $image_list[$image] = $image;
    }

    return $image_list;
}
