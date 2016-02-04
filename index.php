<?php
ini_set('memory_limit', '1G');
ini_set('display_errors', 1);
error_reporting(E_ALL | E_STRICT);

$options = array(
    // which string should represent a tab for indentation
    'tabsize' => 4,
);



require_once 'lib/TFSN/Projects.php';
require_once 'lib/TFSN/Get.php';
require_once 'lib/TFSN/CommandLine.php';

$get = new Get();
$params = $get->getParams();

$commandLine = new CommandLine();
$projects = new Projects();


$projectsList = $title = $errors = '';
$projectsList .= $projects->renderProjects();

$siteDirectory = '';
if(array_key_exists('a', $params) && $params['a'] == 'debug' && array_key_exists('site', $params)){
    $site = $params['site'];
    $siteDirectory = $projects->getDirectoryFromSiteName($site);

    $mageFile = $siteDirectory . 'app/Mage.php';

    if(!file_exists($mageFile)) $mageFile = $siteDirectory . 'webroot/app/Mage.php';

    if(file_exists($mageFile)){
        require_once $mageFile;
        Mage::setIsDeveloperMode(true);
        umask(0);
        Mage::app((array_key_exists('isAdmin', $params) && $params['isAdmin']) ? 'admin' : '');
        $title = '<img src="' . Mage::getDesign()->getSkinUrl() . Mage::getStoreConfig('design/header/logo_src') .  '" />';
        $title .= (array_key_exists('site', $params) ? ' <a target="_blank" href="' . Mage::app()->getStore()->getBaseUrl() . '">' . $params['site'] . '</a>': '');
    }


}
/**
 * PHP Console
 *
 * A web-based php debug console
 *
 * Copyright (C) 2010, Jordi Boggiano
 * http://seld.be/ - j.boggiano@seld.be
 *
 * Licensed under the new BSD License
 * See the LICENSE file for details
 *
 * Source on Github http://github.com/Seldaek/php-console
 */
if (!in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1'), true)) {
    if(!file_exists('remote.flag')) {
    	header('HTTP/1.1 401 Access unauthorized');
    	die('ERR/401 Go Away');
    }
}

define('PHP_CONSOLE_VERSION', '1.3.0-dev');
require 'krumo/class.krumo.php';

$debugOutput = '';

if (isset($_POST['code'])) {
    $code = $_POST['code'];

    if (get_magic_quotes_gpc()) {
        $code = stripslashes($code);
    }

    // if there's only one line wrap it into a krumo() call
    if (preg_match('#^(?!var_dump|echo|print|< )([^\r\n]+?);?\s*$#is', $code, $m) && trim($m[1])) {
        $code = 'krumo('.$m[1].');';
    }

    // replace '< foo' by krumo(foo)
    $code = preg_replace('#^<\s+(.+?);?[\r\n]?$#m', 'krumo($1);', $code);

    // replace newlines in the entire code block by the new specified one
    // i.e. put #\r\n on the first line to emulate a file with windows line
    // endings if you're on a unix box
    if (preg_match('{#((?:\\\\[rn]){1,2})}', $code, $m)) {
        $newLineBreak = str_replace(array('\\n', '\\r'), array("\n", "\r"), $m[1]);
        $code = preg_replace('#(\r?\n|\r\n?)#', $newLineBreak, $code);
    }

    ob_start();
    eval($code);
    $debugOutput = ob_get_clean();

    if (isset($_GET['js'])) {
        header('Content-Type: text/plain');
        echo $debugOutput;
        die('#end-php-console-output#');
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Magento/Zend/PHP Debug Console</title>
    <link rel="stylesheet" type="text/css" href="./assets/styles/styles.css" />
    <link rel="stylesheet" type="text/css" href="./assets/js/google-code-prettify/prettify.css" />
    <link href="https://fonts.googleapis.com/css?family=Roboto+Condensed:400,700" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" />
</head>
<body>
    <header>
        <a class="brand" href="./index.php">TFSN</a>
        <div class="options">
            <span class="options-menu-icon"><i class="fa fa-cogs"></i>Menu</span>
            <ul class="options-menu">
                <li>
                    <label>Projects</label>
                    <select name="projects" class="options-projects">
                        <option value="./index.php">Select A Project</option>
                        <?php echo $projectsList ?>
                    </select>
                </li>
                <li>
                    <label for="run_as_admin">Run as Admin?</label>
                    <input type="checkbox" name="run_as_admin" />
                </li>
                <li>
                    <label for="output_select">Output:</label>
                    <select id="output_select">
                        <option value="html">HTML</option>
                        <option value="text">Text</option>
                    </select>
                </li>
                <li>
                    <label for="options-font">Editor Font Size?</label>
                    <select class="options-font editor-option-fontsize" name="options-font">
                        <?php for ($i = 10; $i <= 24; $i++) { ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                        <?php } ?>
                    </select>
                </li>
                <li>
                    <label for="options-editor">Editor Orientation?</label>
                    <select class="options-editor" name="options-editor">
                        <option value="vert">Vertical</option>
                        <option value="hor">Horizontal</option>
                    </select>
                </li>
            </ul>

            <span class="options-snippets-icon"><i class="fa fa-scissors"></i>Snippets</span>
            <ul class=" options-snippets">
                <li>
                    <label>Snippets</label>
                    <ul id="snippet-container" class="nav nav-pills nav-stacked"></ul>
                    <script id="snippetsTemplate" type="text/x-jQuery-tmpl">
                        <li class="active row">
                            <a
                                class="load-snippet span7"
                                data-project="${snippetProject}"
                                data-label="${snippetLabel}"
                                onClick="TFSN.LocalStorageHelper.checkSnippet(this)"
                                >
                                ${snippetProject}: ${snippetLabel}
                            </a>
                            <i class="preview-snippet icon-plus-sign"></i>
                            <i class="remove-snippet icon icon-remove-sign"></i>
                            <xmp class="prettyprint linenums span6 lang-php" style="display: none;">${snippetCode}</xmp>
                            <pre class="prettyprint linenums span6 lang-php" style="display: none;">${snippetCode}</pre>
                        </li>
                    </script>
                </li>
                <li>
                    <button id="clearSnippets" class="btn remove">Remove All Snippets <i></i></button>
                </li>
            </ul>
        </div>
    </header>

    <div class="container">
        <div id="messages" class="messages"></div>

        <div class="output-container">
            <?php if($siteDirectory): ?>
                <div class="site-logo">
                    <?php echo ($title != '') ? $title . '<a class="" href="./index.php"><i class="icon icon-remove-sign"></i></a>' : ''; ?>
                </div>
            <?php endif; ?>
            <div class="output xmp" style="display: none;"><xmp><?php echo $debugOutput ?></xmp></div>
            <div class="output pre"><pre><?php echo $debugOutput ?></pre></div>
            <div class="output error" style="display: none;"><pre><?php echo $debugOutput ?></pre></div>
        </div>

        <div class="editor-container">
            <form id="code-form" method="POST" action="">
                <div class="input">
                    <label for="editor"></label>
                    <textarea class="editor" id="editor" name="code"></textarea>
                    <div class="statusbar">
                        <span class="position">Line: 1, Column: 1</span>
                    </div>
                </div>
                <input id="try-this" type="submit" name="subm" value="Run Snippet" class="btn btn-large btn-success" />
                <input id="save-snippet" type="button" name="save-snippet" value="Save Snippet" class="btn btn-primary" />
            </form>
        </div>

        <div class="help-container">
            <button class="btn btn-help">Help?</button>
            <div class="help">
                <ul>
                    <li>
                        <h4>debug:</h4>
                        <p>
                            &lt; foo()<br/>
                            krumo(foo());
                        </p>
                    </li>
                    <li>
                        <h4>commands:</h4>
                        <p>
                            krumo::backtrace();<br/>
                            krumo::includes();<br/>
                            krumo::functions();<br/>
                            krumo::classes();<br/>
                            krumo::defines();
                        </p>
                    </li>
                    <li>
                        <h4>misc:</h4>
                        <p>
                            press ctrl + enter (&#8984; + return) to submit<br/>
                            put '#\n' on the first line to enforce<br/>
                            \n line breaks (\r\n etc work too)
                        </p>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <script src="./assets/js/jquery-1.11.3.min.js"></script>
    <script src="./assets/js/jquery-tmpl-min.js"></script>
    <script src="./assets/js/ace/ace.js"></script>
    <script src="./assets/js/ace/mode-php.js"></script>
    <script src="./assets/js/php-console.js"></script>
    <script src="./assets/js/google-code-prettify/prettify.js"></script>
    <script src="./assets/js/storage.js"></script>
    <script src="./assets/js/optionstorage.js"></script>
    <script type="text/javascript">
        $.console({
            tabsize: <?php echo json_encode($options['tabsize']) ?>
        });
    </script>
    <script src="./assets/js/app.js"></script>
    <script src="./assets/js/app-dropdowns.js"></script>
</body>
</html>
