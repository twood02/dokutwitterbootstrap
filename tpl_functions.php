<?php
/**
 * Template Functions
 *
 * This file provides template specific custom functions that are
 * not provided by the DokuWiki core.
 * It is common practice to start each function with an underscore
 * to make sure it won't interfere with future core functions.
 */

// must be run from within DokuWiki
if (!defined('DOKU_INC')) die();

/**
 * Create link/button to discussion page and back
 *
 * @author Anika Henke <anika@selfthinker.org>
 */
function _tpl_discussion($discussionPage, $title, $backTitle, $link=0, $wrapper=0) {
    global $ID;

    $discussPage    = str_replace('@ID@', $ID, $discussionPage);
    $discussPageRaw = str_replace('@ID@', '', $discussionPage);
    $isDiscussPage  = strpos($ID, $discussPageRaw) !== false;
    $backID         = str_replace($discussPageRaw, '', $ID);

    if ($wrapper) echo "<$wrapper>";

    if ($isDiscussPage) {
        if ($link)
            tpl_pagelink($backID, $backTitle);
        else
            echo html_btn('back2article', $backID, '', array(), 'get', 0, $backTitle);
    } else {
        if ($link)
            tpl_pagelink($discussPage, $title);
        else
            echo html_btn('discussion', $discussPage, '', array(), 'get', 0, $title);
    }

    if ($wrapper) echo "</$wrapper>";
}

/**
 * Create link/button to user page
 *
 * @author Anika Henke <anika@selfthinker.org>
 */
function _tpl_userpage($userPage, $title, $link=0, $wrapper=0) {
    if (!$_SERVER['REMOTE_USER']) return;

    global $conf;
    $userPage = str_replace('@USER@', $_SERVER['REMOTE_USER'], $userPage);

    if ($wrapper) echo "<$wrapper>";

    if ($link)
        tpl_pagelink($userPage, $title);
    else
        echo html_btn('userpage', $userPage, '', array(), 'get', 0, $title);

    if ($wrapper) echo "</$wrapper>";
}

/**
 * Create link/button to register page
 * @deprecated DW versions > 2011-02-20 can use the core function tpl_action('register')
 *
 * @author Anika Henke <anika@selfthinker.org>
 */
function _tpl_register($link=0, $wrapper=0) {
    global $conf;
    global $lang;
    global $ID;
    $lang_register = !empty($lang['btn_register']) ? $lang['btn_register'] : $lang['register'];

    if ($_SERVER['REMOTE_USER'] || !$conf['useacl'] || !actionOK('register')) return;

    if ($wrapper) echo "<$wrapper>";

    if ($link)
        tpl_link(wl($ID, 'do=register'), $lang_register, 'class="action register" rel="nofollow"');
    else
        echo html_btn('register', $ID, '', array('do'=>'register'), 'get', 0, $lang_register);

    if ($wrapper) echo "</$wrapper>";
}

/**
 * Wrapper around custom template actions
 *
 * @author Anika Henke <anika@selfthinker.org>
 */
function _tpl_action($type, $link=0, $wrapper=0) {
    switch ($type) {
        case 'discussion':
            if (tpl_getConf('discussionPage')) {
                _tpl_discussion(tpl_getConf('discussionPage'), tpl_getLang('discussion'), tpl_getLang('back_to_article'), $link, $wrapper);
            }
            break;
        case 'userpage':
            if (tpl_getConf('userPage')) {
                _tpl_userpage(tpl_getConf('userPage'), tpl_getLang('userpage'), $link, $wrapper);
            }
            break;
        case 'register': // deprecated
            _tpl_register($link, $wrapper);
            break;
    }
}



/* fallbacks for things missing in older DokuWiki versions
********************************************************************/


/* if newer settings exist in the core, use them, otherwise fall back to template settings */

if (!isset($conf['tagline'])) {
    $conf['tagline'] = tpl_getConf('tagline');
}

if (!isset($conf['sidebar'])) {
    $conf['sidebar'] = tpl_getConf('sidebarID');
}

/* these $lang strings are now in the core */

if (!isset($lang['user_tools'])) {
    $lang['user_tools'] = tpl_getLang('user_tools');
}
if (!isset($lang['site_tools'])) {
    $lang['site_tools'] = tpl_getLang('site_tools');
}
if (!isset($lang['page_tools'])) {
    $lang['page_tools'] = tpl_getLang('page_tools');
}
if (!isset($lang['skip_to_content'])) {
    $lang['skip_to_content'] = tpl_getLang('skip_to_content');
}


/**
 * copied from core (available since Adora Belle)
 */
if (!function_exists('tpl_getMediaFile')) {
    function tpl_getMediaFile($search, $abs = false, &$imginfo = null) {
        $img     = '';
        $file    = '';
        $ismedia = false;
        // loop through candidates until a match was found:
        foreach($search as $img) {
            if(substr($img, 0, 1) == ':') {
                $file    = mediaFN($img);
                $ismedia = true;
            } else {
                $file    = tpl_incdir().$img;
                $ismedia = false;
            }

            if(file_exists($file)) break;
        }

        // fetch image data if requested
        if(!is_null($imginfo)) {
            $imginfo = getimagesize($file);
        }

        // build URL
        if($ismedia) {
            $url = ml($img, '', true, '', $abs);
        } else {
            $url = tpl_basedir().$img;
            if($abs) $url = DOKU_URL.substr($url, strlen(DOKU_REL));
        }

        return $url;
    }
}

/**
 * copied from core (available since Angua)
 */
if (!function_exists('tpl_favicon')) {
    function tpl_favicon($types = array('favicon')) {

        $return = '';

        foreach($types as $type) {
            switch($type) {
                case 'favicon':
                    $look = array(':wiki:favicon.ico', ':favicon.ico', 'images/favicon.ico');
                    $return .= '<link rel="shortcut icon" href="'.tpl_getMediaFile($look).'" />'.NL;
                    break;
                case 'mobile':
                    $look = array(':wiki:apple-touch-icon.png', ':apple-touch-icon.png', 'images/apple-touch-icon.png');
                    $return .= '<link rel="apple-touch-icon" href="'.tpl_getMediaFile($look).'" />'.NL;
                    break;
                case 'generic':
                    // ideal world solution, which doesn't work in any browser yet
                    $look = array(':wiki:favicon.svg', ':favicon.svg', 'images/favicon.svg');
                    $return .= '<link rel="icon" href="'.tpl_getMediaFile($look).'" type="image/svg+xml" />'.NL;
                    break;
            }
        }

        return $return;
    }
}

/**
 * copied from core (available since Adora Belle)
 */
if (!function_exists('tpl_includeFile')) {
    function tpl_includeFile($file) {
        global $config_cascade;
        foreach(array('protected', 'local', 'default') as $config_group) {
            if(empty($config_cascade['main'][$config_group])) continue;
            foreach($config_cascade['main'][$config_group] as $conf_file) {
                $dir = dirname($conf_file);
                if(file_exists("$dir/$file")) {
                    include("$dir/$file");
                    return;
                }
            }
        }

        // still here? try the template dir
        $file = tpl_incdir().$file;
        if(file_exists($file)) {
            include($file);
        }
    }
}

/**
 * copied from core (available since Adora Belle)
 */
if (!function_exists('tpl_incdir')) {
    function tpl_incdir() {
        global $conf;
        return DOKU_INC.'lib/tpl/'.$conf['template'].'/';
    }
}

function _tpl_toc_to_twitter_bootstrap_event_hander_dump_level($data)
{
    global $conf;
    $ret = '';
    $ret .= '<ul class="nav nav-list">';
    $ret .= '<li class="nav-header">'.$conf['start'].'</li>';
    //$ret .= '<li class="divider"></li>';

    //Only supports top level links for now.
    foreach($data as $heading)
    {
        $ret .= '<li><a href="#' . $heading['hid'] . '"><i class="icon-chevron-right"></i> '. $heading['title'] . '</a></li>';
    }

    $ret .= '</ul>';

    return $ret;
}

function _tpl_toc_to_twitter_bootstrap_event_hander(&$event, $param)
{
    //This is tied to the specific format of the DokuWiki TOC.
    //
    //$toc = $event['data'];
    //foreach ($
    //print_r($event->data);
    echo _tpl_toc_to_twitter_bootstrap_event_hander_dump_level($event->data);
}

function _tpl_toc_to_twitter_bootstrap()
{
    //Force generation of TOC, request that the TOC is returned as HTML, but then ignore the returned string. The hook will instead dump out the TOC.
    global $EVENT_HANDLER;
    $EVENT_HANDLER->register_hook('TPL_TOC_RENDER', 'AFTER', NULL, '_tpl_toc_to_twitter_bootstrap_event_hander');
    tpl_toc(true);
}


//Output various links to user tools wrapped in a user-specified element.
function _tpl_output_tools_twitter_bootstrap($showTools = true, $element = 'li')
{
    if ($showTools) {
        tpl_action('admin', 1, $element);
        _tpl_action('userpage', 1, $element);
        tpl_action('profile', 1, $element);
        tpl_action('register', 1, $element);
        tpl_action('login', 1, $element);
    }
}

function _tpl_output_page_tools($showTools = true, $element = 'li'){
    global $lang;

    $textonly = true;
    //Doesn't work until we can specify a span to append after the <a></a> but before the closing </li>.
    $spandivider = '<span class="divider">/</span>';
    $elementbegin = "<$element>";
    $elementend = "</$element>";

    if ($showTools) {
        echo '<ul class="breadcrumb">';
            echo '<li>'.$lang['page_tools'].$spandivider;

            echo $elementbegin;
            $content = tpl_action('edit', $textonly, '', true);
            if ($content != '') { echo $content.$spandivider; }
            echo $elementend;

            echo $elementbegin;
            //Notice the use of _tpl_action instead of tpl_action. This doesn't
            //actully return the string and instead automatically prints it
            //out.
            _tpl_action('discussion', $textonly, '', true);
            echo $spandivider;
            echo $elementend;

            echo $elementbegin;
            $content = tpl_action('revisions', $textonly, '', true);
            if ($content != '') { echo $content.$spandivider; }
            echo $elementend;

            echo $elementbegin;
            $content = tpl_action('backlink', $textonly, '', true);
            if ($content != '') { echo $content.$spandivider; }
            echo $elementend;

            echo $elementbegin;
            $content = tpl_action('subscribe', $textonly, '', true);
            if ($content != '') { echo $content.$spandivider; }
            echo $elementend;

            echo $elementbegin;
            $content = tpl_action('revert', $textonly, '', true);
            if ($content != '') { echo $content.$spandivider; }
            echo $elementend;

            echo $elementbegin;
            $content = tpl_action('top', $textonly, '', true);
            echo $content;
            echo $elementend;
        echo '</ul>';
    }
}

