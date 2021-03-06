<?php

class ADBT_View_Base {

    /** @var ADBT_App */
    protected $app;

    /** @var Model_User The current user. */
    public $user;

    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Turn a spaced or underscored string to camelcase (with no spaces or underscores).
     * 
     * @param string $str
     * @return string
     */
    public static function camelcase($str) {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $str)));
    }

    public function url($path, $params = false)
    {
        // Add root slash if required
        if (substr($path, 0, 1) != '/') {
            $path = '/' . $path;
        }
        // Remove trailing slash
        if (substr($path, -1, 1) == '/') {
            $path = substr($path, 0, -1);
        }
        // If no path, default to current controller and action
        if (empty($path)) {
            //$path = $this->controller_name.'/'.$this->action_name;
        }
        // Construct query string parameters
        $query_string = '';
        if ($params) {
            foreach ($params as $param => $value) {
                $query_string .= (empty($query_string)) ? '?' : '&amp;';
                $query_string .= "$param=$value";
            }
        }
        return BASE_URL.$path.$query_string;
    }

    /**
     * Apply the titlecase filter to a string: removing underscores, uppercasing
     * initial letters, and performing a few common (and not-so-common) word
     * replacements such as initialisms and punctuation.
     *
     * @param string|array $value    The underscored and lowercase string to be
     *                               titlecased, or an array of such strings.
     * @param 'html'|'latex' $format The desired output format.
     * @return string                A properly-typeset title.
     * @todo Get replacement strings from configuration file.
     */
    public function titlecase($value, $format = 'html') {

        /**
         * The mapping of words (and initialisms, etc.) to their titlecased
         * counterparts for HTML output.
         * @var array
         */
        $html_replacements = array(
            'id' => 'ID',
            'cant' => "can't",
            'in' => 'in',
            'at' => 'at',
            'of' => 'of',
            'for' => 'for',
            'sql' => 'SQL',
            'todays' => "Today's",
        );

        /**
         * The mapping of words (and initialisms, etc.) to their titlecased
         * counterparts for LaTeX output.
         * @var array
         */
        $latex_replacements = array(
            'cant' => "can't",
        );

        /**
         * Marshall the correct replacement strings.
         */
        if ($format == 'latex') {
            $replacements = array_merge($html_replacements, $latex_replacements);
        } else {
            $replacements = $html_replacements;
        }

        /**
         * Recurse if neccessary
         */
        if (is_array($value)) {
            return array_map(array($this, 'titlecase'), $value);
        } else {
            $out = ucwords(preg_replace('|_|', ' ', $value));
            foreach ($replacements as $search => $replacement) {
                $out = preg_replace("|\b$search\b|i", $replacement, $out);
            }
            return trim($out);
        }
    }

}