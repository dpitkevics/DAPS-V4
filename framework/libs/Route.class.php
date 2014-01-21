<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace base\libs;

use base\core\ApplicationComponent;

/**
 * Description of Route
 *
 * @author User
 */
class Route extends ApplicationComponent {

    public $urlSuffix;

    /**
     * @var boolean whether the rule is case sensitive. Defaults to null, meaning
     * using the value of {@link CUrlManager::caseSensitive}.
     */
    public $caseSensitive;

    /**
     * @var array the default GET parameters (name=>value) that this rule provides.
     * When this rule is used to parse the incoming request, the values declared in this property
     * will be injected into $_GET.
     */
    public $defaultParams = array();

    /**
     * @var boolean whether the GET parameter values should match the corresponding
     * sub-patterns in the rule when creating a URL. Defaults to null, meaning using the value
     * of {@link CUrlManager::matchValue}. When this property is false, it means
     * a rule will be used for creating a URL if its route and parameter names match the given ones.
     * If this property is set true, then the given parameter values must also match the corresponding
     * parameter sub-patterns. Note that setting this property to true will degrade performance.
     * @since 1.1.0
     */
    public $matchValue;

    /**
     * @var string the HTTP verb (e.g. GET, POST, DELETE) that this rule should match.
     * If this rule can match multiple verbs, please separate them with commas.
     * If this property is not set, the rule can match any verb.
     * Note that this property is only used when parsing a request. It is ignored for URL creation.
     * @since 1.1.7
     */
    public $verb;

    /**
     * @var boolean whether this rule is only used for request parsing.
     * Defaults to false, meaning the rule is used for both URL parsing and creation.
     * @since 1.1.7
     */
    public $parsingOnly = false;

    /**
     * @var string the controller/action pair
     */
    public $route;

    /**
     * @var array the mapping from route param name to token name (e.g. _r1=><1>)
     */
    public $references = array();

    /**
     * @var string the pattern used to match route
     */
    public $routePattern;

    /**
     * @var string regular expression used to parse a URL
     */
    public $pattern;

    /**
     * @var string template used to construct a URL
     */
    public $template;

    /**
     * @var array list of parameters (name=>regular expression)
     */
    public $params = array();

    /**
     * @var boolean whether the URL allows additional parameters at the end of the path info.
     */
    public $append;

    /**
     * @var boolean whether host info should be considered for this rule
     */
    public $hasHostInfo;

    public function __construct($pattern, $route) {
        parent::__construct();

        if (is_array($route)) {
            foreach (array('urlSuffix', 'caseSensitive', 'defaultParams', 'matchValue', 'verb', 'parsingOnly') as $name) {
                if (isset($route[$name]))
                    $this->$name = $route[$name];
            }
            if (isset($route['pattern']))
                $pattern = $route['pattern'];
            $route = $route[0];
        }
        $this->route = trim($route, '/');

        $tr2['/'] = $tr['/'] = '\\/';
        $tr['.'] = '\\.';

        if (strpos($route, '<') !== false && preg_match_all('/<(\w+)>/', $route, $matches2)) {
            foreach ($matches2[1] as $name)
                $this->references[$name] = "<$name>";
        }

        $this->hasHostInfo = !strncasecmp($pattern, 'http://', 7) || !strncasecmp($pattern, 'https://', 8);

        if ($this->verb !== null)
            $this->verb = preg_split('/[\s,]+/', strtoupper($this->verb), -1, PREG_SPLIT_NO_EMPTY);

        if (preg_match_all('/<(\w+):?(.*?)?>/', $pattern, $matches)) {
            $tokens = array_combine($matches[1], $matches[2]);
            foreach ($tokens as $name => $value) {
                if ($value === '')
                    $value = '[^\/]+';
                $tr["<$name>"] = "(?P<$name>$value)";
                if (isset($this->references[$name]))
                    $tr2["<$name>"] = $tr["<$name>"];
                else
                    $this->params[$name] = $value;
            }
        }
        $p = rtrim($pattern, '*');
        $this->append = $p !== $pattern;
        $p = trim($p, '/');
        $this->template = preg_replace('/<(\w+):?.*?>/', '<$1>', $p);
        $this->pattern = '/^' . strtr($this->template, $tr) . '\/';
        if ($this->append)
            $this->pattern.='/u';
        else
            $this->pattern.='$/u';

        if ($this->references !== array())
            $this->routePattern = '/^' . strtr($this->route, $tr2) . '$/u';

        if (FTK_DEBUG && @preg_match($this->pattern, 'test') === false)
            throw new SystemException(Lang::tr('system', 'The URL pattern "{pattern}" for route "{route}" is not a valid regular expression.', array('{route}' => $route, '{pattern}' => $pattern)));
    }

    public function parseUrl($manager, $request, $pathInfo, $rawPathInfo) {
        if ($this->verb !== null && !in_array($request->getRequestType(), $this->verb, true))
            return false;

        if ($manager->caseSensitive && $this->caseSensitive === null || $this->caseSensitive)
            $case = '';
        else
            $case = 'i';

        if ($this->urlSuffix !== null)
            $pathInfo = $manager->removeUrlSuffix($rawPathInfo, $this->urlSuffix);

        // URL suffix required, but not found in the requested URL
        if ($manager->useStrictParsing && $pathInfo === $rawPathInfo) {
            $urlSuffix = $this->urlSuffix === null ? $manager->urlSuffix : $this->urlSuffix;
            if ($urlSuffix != '' && $urlSuffix !== '/')
                return false;
        }
        
        if ($this->hasHostInfo)
            $pathInfo = strtolower($request->getHostInfo()) . rtrim('/' . $pathInfo, '/');
        
        $pathInfo.='/';
        
        if (preg_match($this->pattern . $case, $pathInfo, $matches)) {
            foreach ($this->defaultParams as $name => $value) {
                if (!isset($_GET[$name]))
                    $_REQUEST[$name] = $_GET[$name] = $value;
            }
            $tr = array();
            foreach ($matches as $key => $value) {
                if (isset($this->references[$key]))
                    $tr[$this->references[$key]] = $value;
                elseif (isset($this->params[$key]))
                    $_REQUEST[$key] = $_GET[$key] = $value;
            }
            if ($pathInfo !== $matches[0]) // there're additional GET params
                $manager->parsePathInfo(ltrim(substr($pathInfo, strlen($matches[0])), '/'));
            if ($this->routePattern !== null)
                return strtr($this->route, $tr);
            else
                return $this->route;
        } else
            return false;
    }

}
