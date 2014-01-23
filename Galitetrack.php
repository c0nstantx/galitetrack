<?php
/**
 * Description of Galitetrack (Google Analytics Lite Tracking Class)
 * 
 * @author K.Christofilos <kostasxx@yahoo.com>
 * @link http://github.com/c0nstantx/php-ga-trackcking
 * @copyright Copyright &copy; 2013 K.Christofilos
 * @license http://www.gnu.org/licenses/gpl.html
 *
 */
class Galitetrack {
    
    /**
     *
     * @var string 
     */
    public $id = NULL;
    
    /**
     *
     * @var array 
     */
    public $pushParams;
    
    /**
     *
     * @var array 
     */
    public $trackElements;
    
    /**
     *
     * @var array
     */
    public $exludeIPs;

    /**
     *
     * @var string 
     */
    private $code;
    
    /**
     * This is a very VERY simple class that all it does is printing the Google Analytics
     * tracking code, with very simple configuration.
     *
     * @param type string $googleAnalyticsID
     */
    public function __construct($googleAnalyticsID) {
        $this->id = $googleAnalyticsID;
        $this->code = '';
    }
    
    /**
     * Returns the Google Analytics Tracking Code that will be included in the page header.
     * except if the server IP is in the $this->excludeIPs array.
     * @param boolean $withScriptTags If true returns <script></script> tags. If false returns only the content of tracking code.
     * @return string The Google Analytics Tracking Code.
     */
    public function getTrackingCode() {
        if (is_array($this->exludeIPs) && in_array($_SERVER['SERVER_ADDR'], $this->exludeIPs))
                return;
        $this->code .= "<script type=\"text/javascript\">\n\tvar _gaq = _gaq || [];\n\t_gaq.push(['_setAccount', '$this->id']);\n\t_gaq.push(['_trackPageview']);";
        $this->code .= is_array($this->pushParams) ? $this->_getPushParams($this->pushParams) : '';
        $this->code .= "\n\n\t(function() {\n\t\tvar ga = document.createElement('script');\n\t\tga.type = 'text/javascript';\n\t\tga.async = true;\n\t\tga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';\n\t\tvar s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);\n\t})();";
        $this->code .= is_array($this->trackElements) ? $this->_getTrackElements($this->trackElements) : '';
        $this->code .= "\n</script>";
        return $this->code;
    }

    /**
     * Returns the Google analytics tracking part for specific elements.<br />
     * The input should be an array for elements in the following form.
     * <p>array(
     *      'elementClassName1'=>array(
     *          'action',
     *          'param1',
     *          'param2'
     *      ),
     *      'elementClassName2'=>'action',
     *  );</p>
     * <p>e.x.<br /><br />
     * array(<br />
     *      &nbsp;&nbsp;&nbsp;&nbsp;'imageClass'=>array(<br />
     *      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'_trackEvent',<br />
     *      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'button3',<br />
     *      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'clicked'<br />
     *      &nbsp;&nbsp;&nbsp;&nbsp;),<br />
     *      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'imageClass1'=>'_trackEvent'<br />
     *  );<br />
     * <br />
     * And the output will be: <br /><br />
     * $('.imageClass').live('click', function() {<br />
     *      &nbsp;&nbsp;&nbsp;&nbsp;_gaq.push(['_trackEvent', 'button3', 'clicked']);<br />
     *      &nbsp;&nbsp;&nbsp;&nbsp;return true;<br />
     * });<br /><br />
     * $('.imageClass1').live('click', function() {<br />
     *      &nbsp;&nbsp;&nbsp;&nbsp;_gaq.push(['clicked']);<br />
     *      &nbsp;&nbsp;&nbsp;&nbsp;return true;<br />
     * });<br />
     * </p>
     * 
     * @param array $trackElements The Array of Elements
     * @return string The Google Analytics tracking snippet for elements.
     */
    private function _getTrackElements(array $trackElements) {
        $trackCode = '';
        if (is_array($trackElements)) {
            foreach($trackElements as $key=>$value)
                if (!is_numeric($key)) {
                    $trackCode .= "\n\t$('.$key').live('click', function() {\n\t\t";
                    if (is_array($value)) {
                        $trackCode .= "_gaq.push([";
                        $valueIterator = new CachingIterator(new ArrayIterator($value));
                        foreach($valueIterator as $elem) {
                            if (!is_numeric($elem))
                                if ($valueIterator->hasNext())
                                    $trackCode .= "'$elem', ";
                                else
                                    $trackCode .= "'$elem'";
                            else
                                if ($valueIterator->hasNext())
                                    $trackCode .= "$elem, ";
                                else
                                    $trackCode .= "$elem";
                        }
                    } else {
                        $trackCode .= "_gaq.push([";
                        if (!is_numeric($value))
                            $trackCode .= "'$elem'";
                        else
                            $trackCode .= "$elem";
                    }
                    $trackCode .= "]);\n\t\treturn true;\n\t});";
                }
        }
        return $trackCode;
    }

    /**
     * Returns the Google analytics tracking part for custom push items.<br />
     * The input should be an array for items in the following form.
     * <p>array(
     *      'action1'=>array(
     *          'param1',
     *          'param2',
     *          'param3'
     *      ),
     *      'action2'=>'param',
     *      'action3'
     *  );</p>
     * <p>e.x.<br /><br />
     * array(<br />
     *      &nbsp;&nbsp;&nbsp;&nbsp;'_setVisitorCookieTimeout'=>63072000000,<br />
     *      &nbsp;&nbsp;&nbsp;&nbsp;'trackPageLoadTime',<br />
     *      &nbsp;&nbsp;&nbsp;&nbsp;'_setCustomVar'=>array(<br />
     *      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1,<br />
     *      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'Section',<br />
     *      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'Life & Style',<br />
     *      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3<br />
     * &nbsp;&nbsp;&nbsp;&nbsp;)<br />
     *  );<br />
     * <br />
     * And the output will be: <br /><br />
     * _gaq.push(['_setVisitorCookieTimeout', 63072000000]);<br />
     * _gaq.push(['trackPageLoadTime']);<br />
     * _gaq.push(['_setCustomVar', 1, 'Section', 'Life & Style', 3]);<br />
     * </p>
     * 
     * @param array $pushParams The Array of Items
     * @return string The Google Analytics tracking snippet for Push Elements.
     */
    private function _getPushParams(array $pushParams = array()) {
        $pushCode = '';
        if (is_array($pushParams)) {
            foreach ($pushParams as $key=>$value)
                if (is_numeric($key))
                    $pushCode .= "\n\t_gaq.push(['$value']);";
                else
                    if (is_array($value)) {
                        $pushCode .= "\n\t_gaq.push(['$key',";
                        foreach ($value as $elem) {
                            if (!is_numeric($elem))
                                $pushCode .= " '$elem',";
                            else
                                $pushCode .= " $elem,";
                        }
                        $pushCode = substr($pushCode, 0, (strlen($pushCode)-1));
                        $pushCode .= "]);";
                    } else
                        if (is_numeric($value))
                            $pushCode .= "\n\t_gaq.push(['$key', $value]);";
                        else
                            $pushCode .= "\n\t_gaq.push(['$key', '$value']);";
        }
        return $pushCode;
    }
}

?>
