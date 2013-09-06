<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Detector
 *
 * @copyright        Copyright 2011, MatchMove Games.
 * @package          detector
 * @subpackage       detector.class
 * @link             http://wiki.matchmove.com/mobile_detection
 * @author           Mark Gao
 */
class Detector {

    /**
     * Detector rules
     *
     * @var array
     * @access protected
     */
    protected $_config = array(
        'platform'  => array(
            "android"           => "android.*mobile",
            "androidtablet"     => "android(?!.*mobile)",
            "blackberry"        => "blackberry",
            "blackberrytablet"  => "rim tablet os",
            "iphone"            => "(iphone|ipod)",
            "ipad"              => "(ipad)",
            "palm"              => "(avantgo|blazer|elaine|hiptop|palm|plucker|xiino)",
            "windows"           => "windows ce; (iemobile|ppc|smartphone)",
            "windowsphone"      => "windows phone os",
            "kindle"            => "kindle",
            "wap"               => "(midp|pocket|psp|symbian|wap|opera mini|brew|ucweb)"
        ),
        'browser' => array(
            'firefox'   => 'Firefox',
            'chrome'    => 'Chrome',
            'safari'    => 'Safari'
        )
    );

    /**
     * Site direct list by specified key
     *
     * @var array
     * @access protected
     */
    protected $_target = array();

    /**
     * User agent cached detections
     *
     * @var array
     * @access protected
     */
    protected $_agent = array();

    /**
     * @var string User_Agent string
     */
    protected $_user_agent;

    /**
     * @var string server name
     */
    protected $_server_name = 'mmvpay.com';

    /**
     * Seperator
     *
     * @var const String
     * @access protected
     */
    const SEP = ',';

    /**
     * Default Configuration Path
     *
     * @var const String
     * @access protected
     */
    const DEFAULT_CONFIG_PATH = 'detector';

    /**
     * Gets a refrence to the Detector object instance
     *
     * @param mixed $group Config group name
     * @return object
     * @access public
     */
    public static function &getInstance($config = 'default')
    {
        static $instance = array();

        if (!$instance) {
            $instance[0] = new Detector($config);
        }

        return $instance[0];
    }

    /**
     * Construct a new Detector object.
     *
     * @throws Kohana_Exception
     * @param mixed $config Config name or Config array
     * @return void
     */
    public function __construct($config = NULL)
    {
        // Set server name
        $this->_server_name = isset($_SERVER['SERVER_NAME']) ? strtolower($_SERVER['SERVER_NAME']) : $this->_server_name;

        // Set user_agent
        $this->_user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : NULL;

        // No config group name given
        //$config = is_string($config) ? $config : 'default';

        // Load and validate config
        if (is_string($config) AND !is_array($this->_target = Kohana::$config->load(elf::DEFAULT_CONFIG_PATH)->get($config))) {
            throw new Kohana_Exception('Detector config not defined in :config configureation', array(':config' => $config));
        } elseif (is_array($config)) {
            $this->_target = $config;
        } else {
            return false;
        }
    }

    /**
     * Cache detected values, and return if already available
     *
     * @param string $type detection type
     * @param string $key Original value
     * @param string $value Detected value
     * @return string Detected value, from cache
     * @access protected
     */
    /*protected function _cache($type, $key, $value = false)
    {
        $key = '_' . $key;
        $type = '_' . $type;
        if (false !== $value) {
            $this->{$type}[$key] = $value;
            return $value;
        }

        if (!isset($this->{$type}[$key])) {
            return false;
        }
        return $this->{$type}[$key];
    }*/

    /**
     * Adds custom detection $rules
     *
     * ### Usage:
     *
     * {{{
     * }}}
     *
     * @param
     */
    /*public static function rules($type, $rules, $config = 'default')
    {
        $_this =& Detector::getInstance($config);
        $var = '_' . $type;
    }
    */

    /**
     * Return $name in platform config form
     *
     * @param string $name Name of Platform or Brower
     * @return boolean
     * @access public
     * @static
     */
    protected function pattern($name)
    {
        if (isset($this->_agent[$name])) {
            return $this->_agent[$name];
        }

        // WAP
        if ('wap' == $name) {
            if ((isset($_SERVER['HTTP_ACCEPT']) AND (0 < stripos($_SERVER['HTTP_ACCEPT'], 'text/vnd.wap.wml') OR 0 < stripos($_SERVER['HTTP_ACCEPT'], 'application/vnd.wap.xhtml+xml')))
                OR (isset($_SERVER['HTTP_X_WAP_PROFILE']) OR isset($_SERVER['HTTP_PROFILE']))
            )
            {
                return $this->_agent[$name] = true;
            }
        }

        $type = !isset($this->_config['platform'][$name])
                ? (isset($this->_config['browser']) ? 'browser' : 'UNKNOWN')
                : 'platform';
        $reg = 'UNKNOWN'==$type ? $name : $this->_config[$type][$name];

        return $this->_agent[$name] = (bool) preg_match('/'.$reg.'/i', $this->_user_agent);
    }

    /**
     * Return boolean with specified conditions.
     *
     * ### Usage:
     *
     * {{{
     * Detector::is('iphone');
     * Detector::is(array('iphone','android')) - NOT WORKING
     * Detector::is(array(
     *     'OR' => array(
     *         array('iphone'),
     *         array('android')
     *     ),
     *     'AND' => array(...)
     * )) - NOT WORKING
     * See More on http://wiki.matchmove.com/mobile_detection
     * }}}
     *
     * @param mixed $conditionshy
     */
    public static function is($conditions)
    {
        $_this =& Detector::getInstance();
        return $_this->pattern($conditions);
        // return $_this->condition_to_boolean($conditions);
    }
    

    /**
     * Return a boolean value by parsing given conditions array. Used by Detector::is().
     *
     * @param array $conditions Array or string of conditions
     * @return boolean
     * @access protected
     */
    /*protected function condition_to_boolean($conditions) {
        $result = false;
        // Define a logiccal operators array
        $bool = array('and', 'or', 'not', 'and not', 'or not', 'xor', '||', '&&');

        if (!is_array($conditions)) {
            $conditions = array($conditions);
        } reset($conditions);

        foreach ($conditions as $key => $value) {
            if (is_numeric($key) and empty($value)) {
                continue;
            } elseif (is_numeric($key) && is_string($value)) {
            } elseif ((is_numeric($key) && is_array($value)) || in_array(strtolower(trim($key)), $bool)) {
            } else {
            }
        }
        return $result;
    }
    */

    /**
     * Redirect to allow site
     *
     * ### Usage:
     *
     * {{{
     * Detector::redirect('iphone');
     * Detector::redirect(array('iphone', 'android', 'blackberry'));
     * ** exception **
     * Detector::redirect(array('iphone', 'android'), array());
     * }}}
     *
     * @param mixed $conditions
     * @param array $exception Array of rules to be added.
     * @param string $config Site aceess config to load.
     * @return void
     * @access public
     * @static
     */
    public static function redirect($conditions, $exception = array(), $config = 'default')
    {
        $_this =& Detector::getInstance($config);

        // interim way to resolve oauth stuff
        if (is_array($exception) and count($exception)>0) {
            $uri = $_this->request_uri();
            foreach ($exception as $exc) {
                if ((bool) preg_match('/'.$exc.'/i', $uri)) {
                    return false;
                }
            }
        }

        // Check Cookie
        $var = $_this->validate_cookie();

        if ($var === true) return false;

        // Get module config
        $sites = $_this->_target;

        if (!is_array($conditions)) {
            $conditions = array($conditions);
        } reset($conditions);

        foreach ($conditions as $key) {
            $key = strtolower($key);
            if (!$_this->pattern($key)) {
                continue;
            }

            $location = $_this->url_filter($key);

            if ($location) {
                Cookie::$domain = '.mmvpay.com';
                Cookie::set('auto_detect', $key . self::SEP . $location[0]);
                header('Location: http://' . implode('', $location));
                exit;
            }
        }
    }

    /**
     * Make redirect URL
     *
     * @param Array $sites
     * @param String $key
     * @return array
     * @access protectedb
     */
    protected function url_filter($key)
    {
        if (!isset($this->_target[$key])) {
            return false;
        }

        $server_name = $this->_server_name;
        if (0 === strpos($server_name, 'beta')) {
            $server_name = substr($server_name, strpos($server_name, '.')+1);
        }

        if (in_array($server_name, $this->_target[$key], true) OR !array_key_exists($server_name, $this->_target[$key])) {
            return false;
        }
        $url = $this->_target[$key][$server_name];

        $request_uri = $this->request_uri();
        $url = str_replace(strtolower($server_name), $url, trim($this->_server_name, '/'));
        $url  = array($url, $request_uri);
        return $url;
    }

    /**
     * Request_uri
     *
     * @return String
     * @access protected
     */
    protected function request_uri()
    {
        if (isset($_SERVER['REQUEST_URI'])) {
            $uri = $_SERVER['REQUEST_URI'];
        } else {
            if (isset($_SERVER['argv'])) {
                $uri = $_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['argv'][0];
            }
            elseif (isset($_SERVER['QUERY_STRING'])) {
                $uri = $_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUESRY_STRING'];
            }
            else {
                $uri = $_SERVER['SCRIPT_NAME'];
            }
        }
        // Prevent multiple slashes to avoid cross site requests
        $uri = '/' . ltrim($uri, '/');

        return $uri;
    }

    /**
     * Validate Cookie
     *
     * @return mixed
     * @access protected
     */
    protected function validate_cookie()
    {
        $var = false;

        if ($site = Cookie::get('auto_detect')) {
            // deal cookie string example:"iphone,m.matchmove.com"
            $site = explode(self::SEP, $site);

            if (count($site) == 1) {
                $var = ($this->_server_name == $site[0]) ? true : false;
            } else {
                $var = ($this->_server_name == $site[1]) ? true : false;
            }
        }

        return $var;
    }
}
