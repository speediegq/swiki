<?php
/* swiki - simple wiki
 * See LICENSE file for copyright and license details.
 */

spl_autoload_register(function($class){ require str_replace('\\', DIRECTORY_SEPARATOR, ltrim($class, '\\')).'.php'; });
use md\MarkdownExtra;

define('PAGES',              dirname(__FILE__). '/articles');
define('BASE',           str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']));
define('SELF',               $_SERVER['SCRIPT_NAME']);
define('DEFAULTP',           'index');
define('SIDEBAR',            'sidebar');
define('FOOTERP',            'footer');
define('NXTP',               '404');
define('PREFIX',             'swiki: ');
define('ALLTITLE',           'all');
define('FAVICON',            true);
define('TITLE',              true);
define('CSS',                true);
define('HEAD',               true);
define('FOOTER',             true);
define('TITLEBAR',           true);
define('TOOLBAR',            true);
define('ALL',                true);
define('MAXCHARS',           512);
define('META_DESC',          true);
define('META_ENC',           true);
define('META_VIEWPORT',      true);
define('TITLE_DATE',         'j M Y H:i');

function __( $label, $alt_word = null ) {
	return is_null($alt_word) ? $label : $alt_word;
}

function truncate($text, $chars) {
    if (strlen($text) <= $chars) {
        return $text;
    }

    $text = $text." ";
    $text = substr($text,0,$chars);
    $text = substr($text,0,strrpos($text,' '));
    $text = $text."...";

    return $text;
}

function phead($title, $action, $html, $datetime, $bodyclass="") {
    /* TODO: improve this */
    if (META_DESC) {
        $mdesc = $html;
        $mdesc = preg_replace( '@<p\b[^>]*>(?=.*?<a\b[^>]*>).*?<\@p>@si', '', $mdesc);
        $mdesc = preg_replace( '@<(li)[^>]*?>.*?</\\1>@si', '', $mdesc);
        $mdesc = preg_replace( '@<(ul)[^>]*?>.*?</\\1>@si', '', $mdesc);
        $mdesc = strip_tags($mdesc);
        $mdesc = preg_replace('/\s*$^\s*/m', "\n", $mdesc);
        $mdesc = truncate($mdesc, MAXCHARS);
    }

	print "<!DOCTYPE html>\n";
	print "<html lang=\"en\">\n";

    if (HEAD) {
        print "  <head>\n";

        if (META_ENC) {
            print "    <meta charset=\"UTF-8\">\n";
        }

        if (META_DESC) {
            print "    <meta name=\"description\" content=\"$mdesc\">\n";
        }

        if (META_VIEWPORT) {
            print "    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\" />\n";
        }

        if (FAVICON) {
            print "    <link rel=\"icon\" href=\"/icons/fav.svg\"/>\n";
        }

        if (CSS) {
            print "    <link type=\"text/css\" rel=\"stylesheet\" href=\"/index.css\"/>\n";
        }

        if (TITLE) {
            print "    <title>".PREFIX."$title</title>\n";
        }

        print "  </head>\n";
    }

	print "  <body".($bodyclass != "" ? " class=\"$bodyclass\"":"").">\n";

    if (TITLEBAR) {
        print "    <div class=\"titlebar\"><span class=\"title\">$title</span>$datetime";
        print "    </div>\n";
    }

    if (TOOLBAR) {
        print "    <div class=\"toolbar\">\n";
        print "      <a href=\"" . SELF . "\"><img src=\"/icons/home.svg\" alt=\"". __(DEFAULTP) . "\" title=\"". __(DEFAULTP) . "\" class=\"icon\"></a>\n";
        print "      <a href=\"" . SELF . "?action=all\"><img src=\"/icons/list.svg\" alt=\"". __(ALLTITLE) . "\" title=\"". __(ALLTITLE) . "\" class=\"icon\"></a>\n";
        print "    </div>\n";
    }
}

function pfooter() {
    if (FOOTERP != '') {
        print "  </body>\n";

        $p = getf(FOOTERP);

        if (file_exists($p)) {
            $fp = file_get_contents($p);

            print "    <div class=\"footer\">\n\n";
            print tohtml($fp);
            print "    </div>\n";
        }
    }
}

function greppages($path = "") {
	$filenames = array();
	$dir = opendir(PAGES . "/$path" );

	while ( $filename = readdir($dir) ) {
		if ( $filename[0] == "." ) {
			continue;
		}

		if ( is_dir( PAGES . "/$path/$filename" ) ) {
			array_push($filenames, ...greppages( "$path/$filename" ) );
			continue;
		}

		if ( preg_match("/.md$/", $filename) != 1) {
			continue;
		}

		$filename = substr($filename, 0, -(strlen("md")+1) );

        if ($filename == NXTP || $filename == FOOTERP)
            continue;

		$filenames[] = substr("$path/$filename", 1);
	}
	closedir($dir);
	return $filenames;
}

function getf($page) {
	return PAGES . "/$page.md";
}

/* TODO: escape even more symbols */
function repsym($fn) {
	return str_replace(array('~', '..', '\\', ':', '|', '&'), '-', $fn);
}

function purl($page) {
	return SELF . "/".str_replace("%2F", "/", str_replace("%23", "#", urlencode(repsym($page))));
}

function linkpage($page, $title, $attributes="") {
	return "<a href=\"" . purl($page) ."\"$attributes>$title</a>";
}

function tohtml($fn) {
	$parser = new MarkdownExtra;
	$parser->no_markup = true;
	$out  = $parser->transform($fn);

	preg_match_all("/\[\[(.*?)\]\]/", $out, $matches, PREG_PATTERN_ORDER);

	for ($i = 0; $i < count($matches[0]); $i++) {
		$fulllinktext = $matches[1][$i];
		$linktitlesplit = explode('|', $fulllinktext);
		$linkedp = $linktitlesplit[0];
		$linkt = (count($linktitlesplit) > 1) ? $linktitlesplit[1] : $linkedp;
		$pagept = explode('#', $linkedp)[0];
		$linkedfn = getf(repsym($pagept));
		$exists = file_exists($linkedfn);
		$out = str_replace("[[$fulllinktext]]",
			linkpage($linkedp, $linkt, ($exists? "" : " class=\"noexist\"")), $out);
	}

	$out = preg_replace("/\{\{(.*?)\}\}/", "<img src=\"" . BASE . "/images/\\1\" alt=\"\\1\" />", $out);

	preg_match_all("/<h([1-4])>(.*?)<\/h\\1>/", $out, $matches, PREG_PATTERN_ORDER);

	for ($i = 0; $i < count($matches[0]); $i++) {
		$prefix = "<h".$matches[1][$i].">";
		$caption = $matches[2][$i];
		$suffix = substr_replace($prefix, "/", 1, 0);
	}

	return $out;
}

function main() {
    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
    $text = "";
    $html = "";

    if ($action === 'view') {
        $page = preg_match('@^/@', @$_SERVER["PATH_INFO"]) ?
            urldecode(substr($_SERVER["PATH_INFO"], 1)) : urldecode(@$_REQUEST['page']);
        $page = repsym($page);

        if ( $page == "" ) {
            $page = DEFAULTP;
        }

        $filename = getf($page);

        if ( file_exists($filename) ) {
            $text = file_get_contents($filename);
        } else {
            if (file_exists(getf(NXTP))) {
                $p = file_get_contents(getf(NXTP));

                phead('404', 'view', tohtml($p), '');
                print "    <div class=\"main\">\n\n";
                print tohtml($p);
                print "    </div>\n";
            }

            pfooter();

            die();
        }
    } else if ( $action === 'all' && ALL ) {
        $names = greppages();
        $filelist = array();
        $sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 'name';

        if (!in_array($sortby, array('name', 'recent'))) {
            $sortby = 'name';
        }

        if ($sortby === 'name') {
            natcasesort($names);
            foreach($names as $page) {
                $filelist[$page] = filemtime(getf($page));
        }
        } else {
            foreach($names as $page) {
                $filelist[$page] = filemtime(getf($page));
            }

            arsort($filelist, SORT_NUMERIC);
        }

        $html .= "<p>".__('Total').": ".count($names)." ".__("articles")."</p>";
        $html .= "<table><thead>";
        $html .= "<tr>".
            "<td>".(($sortby!='name')?("<a href=\"".SELF."?action=all&sortby=name\">Name</a>"):"<span class=\"sortby\">".__('Name')."</span>")."</td>".
            "<td>".(($sortby!='recent')?("<a href=\"".SELF."?action=all&sortby=recent\">".__('Modified')."</a>"):"<span class=\"sortby\">".__('Modified')."</span>")."</td>".
            "</tr></thead><tbody>";
        $dateformat = __('dateformat', TITLE_DATE);

        foreach ($filelist as $pname => $pdate) {
            $html .= "<tr>".
                "<td>".linkpage($pname, $pname)."</td>".
                "<td valign=\"top\"><nobr>".date( $dateformat, $pdate)."</nobr></td>".
                    "</tr>\n";
            }
            $html .= "</tbody></table>\n";
        }

        $html .= empty($text) ? '' : tohtml($text);

        $datetime = '';

        if (($action === 'all') && ALL) {
            $title = __(ALLTITLE);
        } else if ($filename != '') {
            $title = $page;
            $dateformat = __('dateformat', TITLE_DATE);

            if ( $dateformat ) {
                $datetime = "<span class=\"titledate\">" . date($dateformat, @filemtime($filename)) . "</span>";
            }
        } else {
            $title = __($action);
        }

        phead($title, $action, $html, $datetime);

    if (file_exists(getf(SIDEBAR))) {
        $sb = file_get_contents(getf(SIDEBAR));

        if (SIDEBAR != '') {
            print "    <div class=\"sidebar\">\n\n";
            $text = $sb;
            print tohtml($text);
            print "    </div>\n";
        }
    }

    print "    <div class=\"main\">\n\n";
    print "$html\n";
    print "    </div>\n";

    pfooter();
}

main();
