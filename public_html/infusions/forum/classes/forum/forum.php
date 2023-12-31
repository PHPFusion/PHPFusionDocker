<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: forum.php
| Author: Chan (Frederick MC Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

namespace PHPFusion\Forums;

class Forum extends ForumServer {

    /**
     * Forum Data
     *
     * @var array
     */
    private $forum_info = [];

    /**
     * @return array
     */
    public function getForumInfo() {
        return $this->forum_info;
    }

    /**
     * Executes forum
     */
    public function setForumInfo() {
        $forum_settings = self::getForumSettings();
        $userdata = fusion_get_userdata();
        $locale = fusion_get_locale();

        // security boot due to insufficient access level
        if (isset($_GET['viewforum']) && (empty($_GET['forum_id']) or !isnum($_GET['forum_id']) || !verify_forum($_GET['forum_id']))) {
            redirect(INFUSIONS.'forum/index.php');
        }

        if (stristr($_SERVER['PHP_SELF'], 'forum_id')) {
            if (isset($_GET['section'])) {
                if ($_GET['section'] == 'latest') {
                    redirect(INFUSIONS.'forum/index.php?section=latest');
                }
                if ($_GET['section'] == 'mypost') {
                    redirect(INFUSIONS.'forum/index.php?section=mypost');
                }
                if ($_GET['section'] == 'tracked') {
                    redirect(INFUSIONS.'forum/index.php?section=tracked');
                }
            }
        }

        $this->forum_info = [
            'forum_id'         => isset($_GET['forum_id']) && isnum($_GET['forum_id']) ? $_GET['forum_id'] : 0,
            'parent_id'        => 0,
            'forum_page_link'  => [],
            'new_thread_link'  => '',
            'lastvisited'      => isset($userdata['user_lastvisit']) && isnum($userdata['user_lastvisit']) ? $userdata['user_lastvisit'] : time(),
            'posts_per_page'   => $forum_settings['posts_per_page'],
            'threads_per_page' => $forum_settings['threads_per_page'],
            'forum_index'      => dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat', (multilang_table("FO") ? "WHERE ".in_group('forum_language', LANGUAGE)." AND" : "WHERE")." ".groupaccess('forum_access')), // waste resources here.
            'threads'          => [],
            'section'          => isset($_GET['section']) ? $_GET['section'] : 'thread',
            'new_topic_link'   => ['link' => FORUM.'newthread.php', 'title' => $locale['forum_0057']],
        ];

        if (file_exists(INFUSIONS.'rss_feeds_panel/feeds/rss_forums.php')) {
            add_to_head('<link rel="alternate" type="application/rss+xml" title="'.fusion_get_locale('forum_0000').' - RSS Feed" href="'.fusion_get_settings('siteurl').'infusions/rss_feeds_panel/feeds/rss_forums.php"/>');
        }

        add_to_title($locale['forum_0000']);

        add_breadcrumb(['link' => FORUM."index.php", "title" => $locale['forum_0000']]);

        // Additional Sections in Index View
        if (isset($_GET['section'])) {
            switch ($_GET['section']) {
                case 'participated':
                    include FORUM_SECTIONS."participated.php";
                    add_to_title($locale['global_201'].$locale['global_024']);
                    add_breadcrumb([
                        'link'  => FORUM."index.php?section=participated",
                        'title' => $locale['global_024']
                    ]);
                    set_meta("description", $locale['global_024']);
                    break;
                case 'latest':
                    add_to_title($locale['global_201'].$locale['global_021']);
                    add_breadcrumb([
                        'link'  => FORUM."index.php?section=latest",
                        'title' => $locale['global_021']
                    ]);
                    set_meta("description", $locale['global_021']);
                    // Clocks at 0.5s
                    include FORUM_SECTIONS."latest.php";
                    break;
                case 'tracked':
                    include FORUM_SECTIONS."tracked.php";
                    add_to_title($locale['global_201'].$locale['global_056']);
                    add_breadcrumb([
                        'link'  => FORUM."index.php?section=tracked",
                        'title' => $locale['global_056']
                    ]);
                    set_meta("description", $locale['global_056']);
                    break;
                case "unanswered":
                    include FORUM_SECTIONS."unanswered.php";
                    add_to_title($locale['global_201'].$locale['global_027']);
                    add_breadcrumb([
                        'link'  => INFUSIONS."forum/index.php?section=unanswered",
                        'title' => $locale['global_027']
                    ]);
                    set_meta("description", $locale['global_027']);
                    break;
                case "unsolved":
                    include FORUM_SECTIONS."unsolved.php";
                    add_to_title($locale['global_201'].$locale['global_028']);
                    add_breadcrumb([
                        'link'  => INFUSIONS."forum/index.php?section=unsolved",
                        'title' => $locale['global_028']
                    ]);
                    set_meta("description", $locale['global_028']);
                    break;
                default:
                    redirect(FORUM);
            }
        } else {

            // Viewforum view
            if (!empty($this->forum_info['forum_id']) && isset($_GET['viewforum'])) {

                $result = dbquery("SELECT * FROM ".DB_FORUMS." WHERE forum_id=:this_forum_id", [':this_forum_id' => $this->forum_info['forum_id']]);

                if (dbrows($result)) {

                    require_once INCLUDES."mimetypes_include.php";

                    // @todo: turn this into ajax filtration to cut down SEO design pattern
                    // This is the thread filtration pattern, and therefore should go to the thread class , not the forum class.
                    $this->forum_info['filter'] = self::filter()->getFilterInfo();

                    // this is the current forum data
                    $this->forum_info = array_merge($this->forum_info, dbarray($result));

                    //if ($this->forum_info['forum_type'] == 1) redirect(FORUM.'index.php');

                    $this->forum_info['forum_moderators'] = Moderator::parseForumMods($this->forum_info['forum_mods']);
                    Moderator::defineForumMods($this->forum_info);
                    $this->setForumPermission($this->forum_info);

                    $this->forum_info['thread_count'] = dbcount("(thread_id)", DB_FORUM_THREADS, "forum_id=:forum_id", [':forum_id' => $this->forum_info['forum_id']]);
                    $this->forum_info['forum_threadcount_word'] = format_word($this->forum_info['thread_count'], $locale['fmt_thread']);
                    $this->forum_info['post_count'] = dbcount("(post_id)", DB_FORUM_POSTS, "forum_id=:forum_id", [':forum_id' => $this->forum_info['forum_id']]);
                    $this->forum_info['forum_postcount_word'] = format_word($this->forum_info['post_count'], $locale['fmt_post']);

                    $this->forum_info['forum_description'] = parse_text($this->forum_info['forum_description'], ['decode' => FALSE]);
                    $this->forum_info['forum_rules'] = parse_text($this->forum_info['forum_rules'], ['decode' => FALSE]);

                    if (!empty($this->forum_info['forum_description'])) {
                        set_meta('description', str_replace("\n", ' ', strip_tags($this->forum_info['forum_description'])));
                    }

                    if (!empty($this->forum_info['forum_meta'])) {
                        set_meta('keywords', $this->forum_info['forum_meta']);
                    }

                    /**
                     * Set Max Rows - XSS
                     * Why is there a forum rows?
                     *
                     * @todo: INSPECT TO SEE WHETHER THIS IS REQUIRED
                     *      It is taking some resource
                     */
                    //$this->forum_info['forum_max_rows'] = dbcount("('forum_id')", DB_FORUMS, (multilang_table("FO") ? in_group('forum_language', LANGUAGE)." AND" : '')." forum_cat='".$this->forum_info['parent_id']."' AND ".groupaccess('forum_access')."");
                    //$_GET['rowstart'] = (isset($_GET['rowstart']) && $_GET['rowstart'] <= $this->forum_info['forum_max_rows']) ? $_GET['rowstart'] : 0;
                    //$this->ext = isset($this->forum_info['parent_id']) && isnum($this->forum_info['parent_id']) ? "&parent_id=".$this->forum_info['parent_id'] : '';
                    /*
                     * End Inspection
                     */

                    // Generate forum breadcrumbs
                    $this->forumBreadcrumbs($this->forum_info['forum_index'], $this->forum_info['forum_id']);

                    // Generate New thread link
                    if ($this->getForumPermission("can_post") && $this->forum_info['forum_type'] > 1) {
                        $this->forum_info['new_thread_link'] = [
                            'link'  => FORUM."newthread.php?forum_id=".$this->forum_info['forum_id'],
                            'title' => $this->forum_info['forum_type'] == 4 ? $locale['forum_0058'] : $locale['forum_0057'],
                        ];
                    }
                    /**
                     * Forum Page Link
                     */
                    $this->forum_info['forum_page_link']['content'] = [
                        'link'  => FORUM.'index.php?viewforum&forum_id='.$this->forum_info['forum_id'],
                        'title' => $locale['forum_0015']
                    ];
                    $this->forum_info['forum_page_link']['activity'] = [
                        'link'  => FORUM.'index.php?viewforum&forum_id='.$this->forum_info['forum_id']."&view=activity",
                        'title' => $locale['forum_0016']
                    ];
                    if ($this->forum_info['forum_users']) {
                        $this->forum_info['forum_page_link']['people'] = [
                            'link'  => FORUM.'index.php?viewforum&forum_id='.$this->forum_info['forum_id']."&view=people",
                            'title' => $locale['forum_0017']
                        ];
                    }
                    $this->forum_info['subforum_count'] = dbcount("(forum_id)", DB_FORUMS, 'forum_cat=:forum_id', [':forum_id' => $this->forum_info['forum_id']]);
                    if ($this->forum_info['subforum_count']) {
                        $this->forum_info['forum_page_link']['subforums'] = [
                            'link'  => FORUM.'index.php?viewforum&forum_id='.$this->forum_info['forum_id'].'&view=subforums',
                            'title' => $locale['forum_0351'],
                        ];
                    }
                    // This count has been taking quite some resource

                    if (isset($_GET['view'])) {
                        switch ($_GET['view']) {
                            case 'subforums':
                                // Get Subforum data
                                if ($this->forum_info['subforum_count']) {
                                    $this->forum_info['item'][$this->forum_info['forum_id']]['child'] = $this->getSubForums($this->forum_info['forum_id']);
                                }
                                break;
                            case 'gallery':
                                // Under Development for Forum 3.0
                            case 'people':
                                $this->forum_info['item'] = [];
                                $this->forum_info['pagenav'] = '';
                                if ($this->forum_info['thread_count']) {
                                    $sql_param = [
                                        ':forum_id' => $this->forum_info['forum_id']
                                    ];

                                    $this->forum_info['max_user_count'] = dbrows(dbquery("SELECT u.*, p.* FROM ".DB_FORUM_POSTS." p INNER JOIN ".DB_USERS." u ON u.user_id=p.post_author WHERE p.forum_id=:forum_id GROUP BY u.user_id", $sql_param));

                                    $limit = $this->forum_info['posts_per_page'];
                                    $_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $this->forum_info['max_user_count'] ? $_GET['rowstart'] : 0;

                                    $result = dbquery("SELECT u.*, p.*, t.*, COUNT(p.post_id) post_count
                                        FROM ".DB_FORUM_THREADS." t
                                        INNER JOIN ".DB_FORUM_POSTS." p ON p.thread_id=t.thread_id AND p.forum_id=t.forum_id
                                        INNER JOIN ".DB_USERS." u ON u.user_id=p.post_author
                                        WHERE p.forum_id=:forum_id
                                        GROUP BY u.user_id
                                        ORDER BY u.user_name ASC, p.post_datestamp DESC
                                        LIMIT ".$_GET['rowstart'].", ".$limit."
                                    ", $sql_param);

                                    $rows = dbrows($result);
                                    if ($rows) {
                                        if ($this->forum_info['max_user_count'] > $limit) {
                                            $this->forum_info['pagenav'] = makepagenav($_GET['rowstart'], $limit, $this->forum_info['max_user_count'], 3, FORUM.'index.php?viewforum&forum_id='.$this->forum_info['forum_id'].'&view=people&');
                                        }
                                        while ($data = dbarray($result)) {
                                            $data['thread_link'] = [
                                                'link'  => FORUM.'viewthread.php?thread_id='.$data['thread_id'].'&pid='.$data['post_id'].'#post_'.$data['post_id'],
                                                'title' => $data['thread_subject']
                                            ];
                                            $this->forum_info['item'][$data['user_id']] = $data;
                                        }
                                    }
                                }
                                break;
                            case 'activity':
                                // Fetch the latest activity in this forum sort by the latest posts.
                                $this->forum_info['item'] = [];
                                $this->forum_info['pagenav'] = '';
                                if ($this->forum_info['thread_count']) {
                                    $sql_select = DB_FORUM_POSTS." p INNER JOIN ".DB_FORUM_THREADS." t ON p.thread_id=t.thread_id AND p.forum_id=t.forum_id";
                                    $sql_cond = "p.forum_id=:forum_id";
                                    $sql_param = [
                                        ':forum_id' => $this->forum_info['forum_id']
                                    ];
                                    $this->forum_info['max_post_count'] = dbcount("(post_id)", $sql_select, $sql_cond, $sql_param);
                                    $rowstart = (isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $this->forum_info['max_post_count'] ? $_GET['rowstart'] : 0);
                                    $query = "SELECT p.*, t.thread_id, t.thread_subject FROM $sql_select WHERE $sql_cond ORDER BY p.post_datestamp DESC LIMIT ".$rowstart.", ".$this->forum_info['posts_per_page'];
                                    // Make var for Limits
                                    $result = dbquery($query, $sql_param);
                                    $rows = dbrows($result);
                                    if ($rows) {
                                        if ($this->forum_info['max_post_count'] > $this->forum_info['posts_per_page']) {
                                            $this->forum_info['pagenav'] = makepagenav($rowstart, $this->forum_info['posts_per_page'], $this->forum_info['max_post_count'], 3, FORUM.'index.php?viewforum&forum_id='.$this->forum_info['forum_id'].'&view=activity&');
                                        }
                                        $i = 0;
                                        while ($data = dbarray($result)) {
                                            if ($user = fusion_get_user($data['post_author'])) {
                                                $data['post_author'] = [
                                                    'user_id'     => $user['user_id'],
                                                    'user_name'   => $user['user_name'],
                                                    'user_status' => $user['user_status'],
                                                    'user_level'  => getuserlevel($user['user_level']),
                                                    'user_avatar' => $user['user_avatar']
                                                ];
                                                $data['thread_link'] = [
                                                    'link'  => FORUM.'viewthread.php?thread_id='.$data['thread_id'].'&pid='.$data['post_id'].'#post_'.$data['post_id'],
                                                    'title' => $data['thread_subject']
                                                ];
                                                if (!$i) {
                                                    $this->forum_info['last_activity'] = [
                                                        'time'    => $data['post_datestamp'],
                                                        'subject' => $data['thread_subject'],
                                                        'link'    => $data['thread_link']['link'],
                                                        'title'   => $data['thread_link']['title'],
                                                        'user'    => $data['post_author']
                                                    ];
                                                }
                                                $this->forum_info['item'][$data['post_id']] = $data;
                                                $i++;
                                            }
                                        }
                                    }
                                }
                                /**
                                 * Benchmarking results:
                                 * logs at 0.32s render speed for 203 posts, 0.28s for 151 post (consumes between 0.00137s - 0.00185s per posts)
                                 * //showBenchmark(TRUE);
                                 */
                                break;
                            default:
                                redirect(FORUM.'index.php');
                        }
                    } else {
                        // Get Threads Data
                        if ($this->forum_info['forum_type'] == 4) {
                            // must be questions, so need 2 filters - 0596 is question.
                            $filter = [
                                'solved'   => [
                                    'link'   => FORUM."index.php?viewforum&forum_id=".$this->forum_info['forum_id']."&type=solved",
                                    'title'  => $locale['forum_0378'],
                                    'icon'   => "<i class='fa fa-question-circle-o text-success m-r-5'></i>",
                                    'active' => FALSE,
                                    'count'  => dbcount("(thread_id)", DB_FORUM_THREADS, "forum_id=:forum_id and thread_answered=:answer AND thread_hidden=:status", [':forum_id' => $this->forum_info['forum_id'], ':answer' => 1, ':status' => 0]) ?: 0,
                                ],
                                'unsolved' => [
                                    'link'   => FORUM."index.php?viewforum&forum_id=".$this->forum_info['forum_id']."&type=unsolved",
                                    'title'  => $locale['forum_0379'],
                                    'icon'   => "<i class='fa fa-question-circle text-warning m-r-5'></i>",
                                    'active' => FALSE,
                                    'count'  => dbcount("(thread_id)", DB_FORUM_THREADS, "forum_id=:forum_id and thread_answered=:answer and thread_hidden=:status", [':forum_id' => $this->forum_info['forum_id'], ':answer' => 0, ':status' => 0]) ?: 0,
                                ]
                            ];
                        } else {
                            // must be discussions
                            $filter = [
                                'discussions' => [
                                    'link'   => FORUM."index.php?viewforum&forum_id=".$this->forum_info['forum_id']."&type=discussions",
                                    'title'  => $locale['forum_0222'],
                                    'icon'   => "<i class='fa fa-comment text-primary m-r-5'></i>",
                                    'active' => FALSE,
                                    'count'  => $this->forum_info['thread_count'],
                                ]
                            ];
                        }

                        $attach_count = dbrows(dbquery("
                        SELECT attach_id FROM ".DB_FORUM_THREADS." t
                        INNER JOIN ".DB_FORUM_ATTACHMENTS." a ON t.thread_id=a.thread_id
                        WHERE t.forum_id=:forum_id AND t.thread_poll='0' AND t.thread_hidden='0' AND (a.attach_id IS NOT NULL OR attach_count > 0)
                        GROUP BY a.thread_id
                        ", [':forum_id' => $this->forum_info['forum_id']]));

                        $this->forum_info['filters']['type'] = [
                                'all'         => [
                                    'link'   => FORUM."index.php?viewforum&forum_id=".$this->forum_info['forum_id']."&type=all",
                                    'title'  => $locale['forum_0374'],
                                    'icon'   => '',
                                    'active' => FALSE,
                                    'count'  => $this->forum_info['thread_count']
                                ],
                                'attachments' => [
                                    'link'   => FORUM."index.php?viewforum&forum_id=".$this->forum_info['forum_id']."&type=attachments",
                                    'title'  => $locale['forum_0223'],
                                    'icon'   => "<i class='fa fa-file-text-o text-info m-r-5'></i>",
                                    'active' => FALSE,
                                    'count'  => $attach_count ?: 0,
                                ],
                                'poll'        => [
                                    'link'   => FORUM."index.php?viewforum&forum_id=".$this->forum_info['forum_id']."&type=poll",
                                    'title'  => $locale['forum_0314'],
                                    'icon'   => "<i class='fa fa-bar-chart text-success m-r-5'></i>",
                                    'active' => FALSE,
                                    'count'  => dbcount("(thread_id)", DB_FORUM_THREADS, "thread_poll=:has_poll and thread_hidden=:status and forum_id=:forum_id", [':has_poll' => 1, ':status' => 0, ':forum_id' => $this->forum_info['forum_id']]) ?: 0,
                                ]
                            ] + $filter;
                        // calculate active
                        $i = 0;
                        foreach (array_keys($this->forum_info['filters']['type']) as $key) {
                            if ((isset($_GET['type']) && $key == $_GET['type']) || ($i == 0 && !isset($_GET['type']))) {
                                $this->forum_info['filters']['type'][$key]['active'] = TRUE;
                            }
                            $i++;
                        }

                        /**
                         * Get existing threads in the current forum
                         *
                         * @todo: Make a new template, use Jquery to cut out loading time.
                         */
                        $filter_sql = $this->filter()->getFilterSql();
                        $thread_info = $this->thread(FALSE)->getForumThread($this->forum_info['forum_id'],
                            [
                                'condition' => $filter_sql['condition'],
                                'order'     => $filter_sql['order'],
                                //'debug'     => TRUE,
                            ]
                        );

                        $this->forum_info['subforums'] = $this->getSubForums($this->forum_info['forum_id']);

                        $this->forum_info = array_merge_recursive($this->forum_info, $thread_info);
                    }

                } else {
                    redirect(FORUM.'index.php');
                }
            } else {
                $this->forum_info['forums'] = self::getForum(); //Index view
            }
        }

    }

    /**
     * Set user permission based on current forum configuration
     *
     * @param array $forum_data
     */
    public function setForumPermission($forum_data) {
        // Access the forum
        $this->forum_info['permissions']['can_access'] = (iMOD || checkgroup($forum_data['forum_access']));
        // Create new thread -- whether user has permission to create a thread
        $this->forum_info['permissions']['can_post'] = (iMOD || (checkgroup($forum_data['forum_post']) && $forum_data['forum_lock'] == FALSE));
        // Poll creation -- thread has not exists, therefore cannot be locked.
        $this->forum_info['permissions']['can_create_poll'] = $forum_data['forum_allow_poll'] == TRUE && (iMOD || (checkgroup($forum_data['forum_poll']) && $forum_data['forum_lock'] == FALSE));
        $this->forum_info['permissions']['can_upload_attach'] = $forum_data['forum_allow_attach'] == TRUE && (iMOD || checkgroup($forum_data['forum_attach']));
        $this->forum_info['permissions']['can_download_attach'] = iMOD || ($forum_data['forum_allow_attach'] == TRUE && checkgroup($forum_data['forum_attach_download']));
    }

    /**
     * Get the relevant permissions of the current forum permission configuration
     *
     * @param null $key
     *
     * @return null
     */
    public function getForumPermission($key = NULL) {
        if (!empty($this->forum_info['permissions'])) {
            if (isset($this->forum_info['permissions'][$key])) {
                return $this->forum_info['permissions'][$key];
            }

            return $this->forum_info['permissions'];
        }

        return NULL;
    }

    /**
     * Get the forum structure
     *
     * @param bool $forum_id
     * @param bool $branch_id
     *
     * @return array
     */
    public static function getForum($forum_id = FALSE, $branch_id = FALSE) {

        $forum_settings = self::getForumSettings();
        $userdata = fusion_get_userdata();
        $locale = fusion_get_locale();
        $index = [];

        // define what a row is
        $row = [
            'forum_new_status'       => '',
            'last_post'              => '',
            'forum_moderators'       => '',
            'forum_link'             => [
                'link'  => '',
                'title' => ''
            ],
            'forum_description'      => '',
            'forum_postcount_word'   => '',
            'forum_threadcount_word' => '',
        ];

        $parameters = [];

        if ($forum_id && $branch_id) {
            $parameters = [
                ':forum_id'  => intval($forum_id),
                ':branch_id' => intval($branch_id)
            ];
        }

        $query = dbquery("
            SELECT f.forum_id, f.forum_cat, f.forum_name, f.forum_description, f.forum_branch, f.forum_access, f.forum_lock, f.forum_type, f.forum_mods, f.forum_postcount, f.forum_threadcount, f.forum_icon, f.forum_image, f.forum_lastpost, f.forum_lastpostid, f.forum_language,
            t.thread_id, t.thread_lastpost, t.thread_lastpostid, t.thread_lastuser, t.thread_subject
            FROM ".DB_FORUMS." f
            LEFT JOIN ".DB_FORUM_THREADS." t ON f.forum_lastpostid = t.thread_lastpostid
            ".(multilang_table("FO") ? "WHERE ".in_group('f.forum_language', LANGUAGE)." AND" : "WHERE")." ".groupaccess('f.forum_access')."
            ".($forum_id && $branch_id ? "AND f.forum_id=:forum_id or f.forum_cat=:forum_id OR f.forum_branch=:branch_id" : '')."
            GROUP BY f.forum_id ORDER BY f.forum_cat ASC, f.forum_order ASC, t.thread_lastpost DESC
        ", $parameters);

        while ($data = dbarray($query) and checkgroup($data['forum_access'])) {
            $newStatus = '';
            $lastPostInfo = [
                'avatar'       => '',
                'avatar_src'   => '',
                'profile_link' => '',
                'time'         => '',
                'date'         => '',
                'thread_link'  => '',
                'post_link'    => ''
            ];

            if ($data['forum_type'] > 1 && $data['forum_lastpost']) {
                $user = fusion_get_user($data['thread_lastuser']);

                if (!empty($user['user_id'])) {
                    $data['user_id'] = $user['user_id'];
                    $data['user_name'] = $user['user_name'];
                    $data['user_status'] = $user['user_status'];
                    $data['user_avatar'] = $user['user_avatar'];
                    $data['user_level'] = $user['user_level'];
                } else {
                    $data['user_id'] = 0;
                    $data['user_name'] = '';
                    $data['user_status'] = 0;
                    $data['user_avatar'] = '';
                    $data['user_level'] = '';
                }

                $lastPostInfo = [
                    'avatar'       => $forum_settings['forum_last_post_avatar'] ? display_avatar($data, '30px', '', '', 'img-rounded') : '',
                    'avatar_src'   => $data['user_avatar'] && file_exists(IMAGES.'avatars/'.$data['user_avatar']) && !is_dir(IMAGES.'avatars/'.$data['user_avatar']) ? IMAGES.'avatars/'.$data['user_avatar'] : '',
                    'profile_link' => profile_link($data['user_id'], $data['user_name'], $data['user_status']),
                    'time'         => timer($data['thread_lastpost']),
                    'date'         => showdate("forumdate", $data['thread_lastpost']),
                    'thread_link'  => INFUSIONS."forum/viewthread.php?thread_id=".$data['thread_id'],
                    'post_link'    => INFUSIONS."forum/viewthread.php?thread_id=".$data['thread_id']."&pid=".$data['thread_lastpostid']."#post_".$data['thread_lastpostid'],
                    'link_title'   => $data['forum_name']
                ];

                // Calculate Forum New Status
                $forum_match = "\\|".$data['thread_lastpost']."\\|".$data['forum_id'];
                $last_visited = (isset($userdata['user_lastvisit']) && isnum($userdata['user_lastvisit'])) ? $userdata['user_lastvisit'] : time();
                if ($data['thread_lastpost'] > $last_visited) {
                    if (iMEMBER && ($data['thread_lastuser'] !== $userdata['user_id'] || !preg_match("($forum_match\\.|$forum_match$)", $userdata['user_threads']))) {
                        $newStatus = "<span class='forum-new-icon'><i title='".$locale['forum_0260']."' class='".self::getForumIcons('new')."'></i></span>";
                    }
                }
            }

            // Icons
            $type_icon = [
                '1' => self::getForumIcons('forum'),
                '2' => self::getForumIcons('thread'),
                '3' => self::getForumIcons('link'),
                '4' => self::getForumIcons('question')
            ];

            $row = array_merge($row, $data, [
                "forum_moderators"       => Moderator::parseForumMods($data['forum_mods']),
                "forum_new_status"       => $newStatus,
                "forum_link"             => [
                    "link"  => INFUSIONS."forum/index.php?viewforum&forum_id=".$data['forum_id'],
                    "title" => $data['forum_name']
                ],
                "forum_description"      => nl2br(parseubb($data['forum_description'])),
                "forum_postcount_word"   => format_word($data['forum_postcount'], $locale['fmt_post']),
                "forum_threadcount_word" => format_word($data['forum_threadcount'], $locale['fmt_thread']),
                "last_post"              => $lastPostInfo,
                "forum_icon_alt"         => $type_icon[$data['forum_type']],
            ]);

            $row["forum_image"] = ($row['forum_image'] && file_exists(FORUM."images/".$row['forum_image'])) ? $row['forum_image'] : '';

            $thisref = &$refs[$data['forum_id']];
            $thisref = $row;
            if ($data['forum_cat'] == 0) {
                $index[0][$data['forum_id']] = &$thisref;
            } else {
                $refs[$data['forum_cat']]['child'][$data['forum_id']] = &$thisref;
            }
        }

        return $index;
    }

    public function getSubForums($forum_id) {
        $forum_settings = self::getForumSettings();
        $userdata = fusion_get_userdata();
        $locale = fusion_get_locale();

        if ($this->forum_info['subforum_count']) {

            $subforum_result = dbquery("SELECT f.forum_id, f.forum_cat, f.forum_name, f.forum_description, f.forum_branch, f.forum_access, f.forum_lock, f.forum_type, f.forum_mods, f.forum_postcount, f.forum_threadcount, f.forum_icon, f.forum_image, f.forum_lastpost, f.forum_lastuser, f.forum_lastpostid, f.forum_language,
                t.thread_id, t.thread_lastpost, t.thread_lastpostid, t.thread_lastuser, t.thread_subject
                FROM ".DB_FORUMS." f
                LEFT JOIN ".DB_FORUM_THREADS." t ON f.forum_lastpostid = t.thread_lastpostid
                ".(multilang_table("FO") ? "WHERE ".in_group('f.forum_language', LANGUAGE)." AND" : "WHERE")." ".groupaccess('f.forum_access')."
                AND f.forum_cat=:forum_id
                GROUP BY f.forum_id ORDER BY f.forum_cat ASC, f.forum_order ASC, t.thread_lastpost DESC
            ", [
                ':forum_id' => $forum_id
            ]);

            $refs = [];

            $list = [];

            // define what a row is
            $row_array = [
                'forum_new_status'       => '',
                'last_post'              => '',
                'forum_moderators'       => '',
                'forum_link'             => [
                    'link'  => '',
                    'title' => ''
                ],
                'forum_description'      => '',
                'forum_postcount_word'   => '',
                'forum_threadcount_word' => '',
            ];
            if (dbrows($subforum_result)) {

                while ($row = dbarray($subforum_result) and checkgroup($row['forum_access'])) {

                    // Calculate Forum New Status
                    $newStatus = "";
                    $forum_match = "\|".$row['forum_lastpost']."\|".$row['forum_id'];
                    $last_visited = (isset($userdata['user_lastvisit']) && isnum($userdata['user_lastvisit'])) ? $userdata['user_lastvisit'] : time();
                    if ($row['forum_lastpost'] > $last_visited) {
                        if (iMEMBER && ($row['forum_lastuser'] !== $userdata['user_id'] || !preg_match("($forum_match\.|$forum_match$)", $userdata['user_threads']))) {
                            $newStatus = "<span class='forum-new-icon'><i title='".$locale['forum_0260']."' class='".self::getForumIcons('new')."'></i></span>";
                        }
                    }

                    // Icons
                    $type_icon = [
                        '1' => self::getForumIcons('forum'),
                        '2' => self::getForumIcons('thread'),
                        '3' => self::getForumIcons('link'),
                        '4' => self::getForumIcons('question')
                    ];

                    // Calculate lastpost information
                    $lastPostInfo = [
                        'avatar'       => '',
                        'avatar_src'   => '',
                        'message'      => '',
                        'profile_link' => '',
                        'time'         => '',
                        'date'         => '',
                        'thread_link'  => '',
                        'post_link'    => '',
                    ];

                    if ($forum_settings['forum_show_lastpost'] == 1) {
                        if (!empty($row['forum_lastpostid'])) {
                            $last_user = fusion_get_user($row['forum_lastuser']);

                            if (!empty($last_user['user_id'])) {
                                $row['user_id'] = $last_user['user_id'];
                                $row['user_name'] = $last_user['user_name'];
                                $row['user_status'] = $last_user['user_status'];
                                $row['user_avatar'] = $last_user['user_avatar'];
                                $row['user_level'] = $last_user['user_level'];
                            } else {
                                $row['user_id'] = 0;
                                $row['user_name'] = '';
                                $row['user_status'] = 0;
                                $row['user_avatar'] = '';
                                $row['user_level'] = '';
                            }

                            // as first_post_datestamp
                            $last_post_sql = "SELECT post_message FROM ".DB_FORUM_POSTS." WHERE post_id=:post_id ORDER BY post_datestamp DESC";
                            $last_post_param = [':post_id' => $row['forum_lastpostid']];
                            $post_result = dbquery($last_post_sql, $last_post_param);

                            if (dbrows($post_result) > 0) {

                                // Get the current forum last user

                                $post_data = dbarray($post_result);

                                $last_post = [
                                    'avatar_src'   => $last_user['user_avatar'] && file_exists(IMAGES.'avatars/'.$last_user['user_avatar']) && !is_dir(IMAGES.'avatars/'.$last_user['user_avatar']) ? IMAGES.'avatars/'.$last_user['user_avatar'] : '',
                                    'message'      => trim_text(parseubb(parsesmileys($post_data['post_message'])), 100),
                                    'profile_link' => profile_link($row['forum_lastuser'], $last_user['user_name'], $last_user['user_status']),
                                    'time'         => timer($row['forum_lastpost']),
                                    'date'         => showdate("forumdate", $row['forum_lastpost']),
                                    'thread_link'  => INFUSIONS."forum/viewthread.php?thread_id=".$row['thread_id'],
                                    'post_link'    => INFUSIONS."forum/viewthread.php?thread_id=".$row['thread_id']."&pid=".$row['thread_lastpostid']."#post_".$row['thread_lastpostid'],
                                ];
                                if ($forum_settings['forum_last_post_avatar']) {
                                    $last_post['avatar'] = display_avatar($last_user, '30px', '', '', 'img-rounded');
                                }

                                $lastPostInfo = $last_post;
                            }
                        }
                    }

                    $row['forum_postcount'] = dbcount("(post_id)", DB_FORUM_POSTS, "forum_id=:forum_id", [':forum_id' => $row['forum_id']]);
                    $row['forum_threadcount'] = dbcount("(thread_id)", DB_FORUM_THREADS, "forum_id=:forum_id", [':forum_id' => $row['forum_id']]);

                    $_row = array_merge($row_array, $row, [
                        "forum_type"             => $row['forum_type'],
                        "forum_moderators"       => Moderator::parseForumMods($row['forum_mods']), //// display forum moderators per forum.
                        "forum_new_status"       => $newStatus,
                        "forum_link"             => [
                            "link"  => FORUM."index.php?viewforum&forum_id=".$row['forum_id'],
                            "title" => $row['forum_name']
                        ],
                        "forum_description"      => nl2br(parseubb($row['forum_description'])), // current forum description
                        // @this need a count
                        "forum_postcount_word"   => format_word($row['forum_postcount'], $locale['fmt_post']), // current forum post count
                        // @this need a count
                        "forum_threadcount_word" => format_word($row['forum_threadcount'], $locale['fmt_thread']), // thread in the current forum
                        "last_post"              => $lastPostInfo, // last post information
                        "forum_icon_alt"         => $type_icon[$row['forum_type']],
                        "forum_image"            => ($row['forum_image'] && file_exists(FORUM."images/".$row['forum_image'])) ? $row['forum_image'] : '',
                    ]);

                    // child hierarchy data.
                    $thisref = &$refs[$_row['forum_id']];
                    $thisref = $_row;
                    if ($_row['forum_cat'] == $this->forum_info['forum_id']) {
                        //$this->forum_info['item'][$_row['forum_id']] = &$thisref; // will push main item out.
                        $list[$_row['forum_id']] = &$thisref;
                    } else {
                        $refs[$_row['forum_cat']]['child'][$_row['forum_id']] = &$thisref;
                    }
                }

                return $list;
            }
        }

        return NULL;
    }
}
