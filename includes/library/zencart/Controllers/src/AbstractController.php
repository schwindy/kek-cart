<?php
/**
 * Class AbstractController
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  Modified in v1.6.0 $
 */
namespace ZenCart\Controllers;

/**
 * Class AbstractController
 * @package ZenCart\Controllers
 */
abstract class AbstractController extends \base
{
    /**
     * @var array
     */
    protected $tplVars;
    /**
     * @var
     */
    protected $controllerCommand;
    /**
     * @var
     */
    protected $controllerAction;
    /**
     * @var bool
     */
    protected $useView = true;
    /**
     * @var bool
     */
    protected $useFoundation = false;

    /**
     * @param $controllerCommand
     * @param $request
     * @param $db
     */
    public function __construct($controllerCommand, $request, $db)
    {
        $this->request = $request;
        $this->dbConn = $db;
        $this->controllerCommand = $controllerCommand;
        $this->tplVars = array();
        $this->response = array(
            'data' => null
        );
        $this->prepareDefaultCss();
        $this->prepareCommonTplVars();
        $this->preCheck();
    }

    /**
     *
     */
    protected function prepareCommonTplVars()
    {
        $this->tplVars['cmd'] = $this->request->readGet('cmd');
        $this->tplVars['useFoundation'] = $this->useFoundation;

        $this->tplVars['hide_languages'] = $GLOBALS['hide_languages'];
        $this->tplVars['languages'] = $GLOBALS['languages'];
        $this->tplVars['languages_array'] = $GLOBALS['languages_array'];
        $this->tplVars['languages_selected'] = $GLOBALS['languages_selected'];

    }

    /**
     *
     */
    protected function prepareDefaultCSS()
    {
        if ($this->useView) {
            $cssList [] = array(
                'href' => 'includes/template/css/normalize.css',
                'id' => 'normalizeCSS'
            );
            if ($this->useFoundation) {
                $cssList [] = array(
                    'href' => 'includes/template/css/foundation.min.css',
                    'id' => 'foundationCSS'
                );
            }
            $cssList [] = array(
                'href' => 'includes/template/css/stylesheet.css',
                'id' => 'stylesheetCSS'
            );
            $cssList [] = array(
                'href' => 'includes/template/css/stylesheet_print.css',
                'media' => 'print',
                'id' => 'printCSS'
            );
            if ($this->useFoundation) {
                $cssList [] = array(
                    'href' => 'includes/template/css/zen-foundation-reset.css',
                    'id' => 'zenFoundationResetCSS'
                );
            }
        }
        $this->tplVars ['cssList'] = $cssList;
    }

    /**
     *
     */
    public function invoke()
    {
        $this->controllerAction = 'main';
        $tmp = $this->request->get('action', $this->request->get('action', 'main', 'post'), 'get');
        if ($tmp = preg_replace('/[^a-zA-Z0-9_-]/', '', $tmp)) {
            $this->controllerAction = $tmp;
        }
        $this->controllerAction .= 'Execute';
        $this->controllerAction = (method_exists($this, $this->controllerAction)) ? $this->controllerAction : 'mainExecute';
        $this->{$this->controllerAction}();
        $this->doOutput();
    }

    /**
     *
     */
    protected function doOutput()
    {
        if (!$this->useView) {
            $this->doNonViewOutput();
        } else {
            $this->doViewOutput();
        }
    }

    /**
     *
     */
    protected function doViewOutput()
    {
        if (isset($this->response['redirect'])) {
            $this->notify('NOTIFIER_ADMIN_BASE_DO_VIEW_OUTPUT_REDIRECT_BEFORE');
            zen_redirect($this->response['redirect']);
        }
        $tplVars = $this->tplVars;
        require('includes/template/common/tplAdminHtmlHead.php');
        echo "\n" . "</head>";
        echo "\n" . "<body>";
        require_once('includes/template/common/tplHeader.php');
        $useTemplate = $this->getMainTemplate();
        if (isset($useTemplate)) {
            require($useTemplate);
        }
        require('includes/template/common/tplFooter.php');
    }

    /**
     * @return null|string
     */
    protected function getMainTemplate()
    {
        if (isset($this->mainTemplate)) {
            return ('includes/template/templates/' . $this->mainTemplate);
        }
        $tryTemplate = 'tpl' . ucfirst($this->controllerCommand) . '.php';
        if (file_exists('includes/template/templates/' . $tryTemplate)) {
            return ('includes/template/templates/' . $tryTemplate);
        }

        return null;
    }

    /**
     *
     */
    protected function doNonViewOutput()
    {
        echo json_encode($this->response);
    }

    /**
     * @param $template
     * @param $tplVars
     * @return string
     */
    protected function loadTemplateAsString($template, $tplVars)
    {
        ob_start();
        require_once($template);
        $result = ob_get_clean();
        ob_flush();

        return $result;
    }

    /**
     * @param $key
     * @param $value
     */
    public function setTplVar($key, $value)
    {
        $this->tplVars[$key] = $value;
    }

    /**
     * @param $tplVars
     */
    public function setTplVars($tplVars)
    {
        $this->tplVars = $tplVars;
    }

    /**
     * @return array
     */
    public function getTplVars()
    {
        return $this->tplVars;
    }

    /**
     * @param $templateName
     */
    public function setMainTemplate($templateName)
    {
        $this->mainTemplate = $templateName;
    }

    /**
     *
     */
    protected function preCheck()
    {
    }
}
