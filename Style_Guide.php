<?php

/**
 * TODO Example hooks for a Pico plugin
 *
 * @package Pico
 * @subpackage Style Guide
 * @since version Ver 0.1
 * @author Shawn Sandy
 * @link TODO http://www.shawnsandy.com
 * @license http://opensource.org/licenses/MIT
 */

class Style_Guide {

    private $plugin_path,
            $stylizer = null,
            $theme_dir,
            $theme_url,
            $base_dir,
            $content = 'index';

    public function __construct() {
        $this->plugin_path = dirname(__FILE__);
    }

    public function request_url(&$url) {
        //var_dump($url);
        //check see if style_guide is in the $url
        if (preg_match("/style_guide/i", $url)):
            //get the url paths
            $paths = explode("/", $url);
            if (count($paths) > 1):
                $this->content = $paths[1].'.html';
            else:
                $this->stylizer = 'style_guide';
            endif;
        endif;
    }

    public function before_render(&$twig_vars, &$twig) {
        //var_dump($twig_vars);
        $this->theme_url = $twig_vars['theme_url'];

        $this->theme_dir = $twig_vars['theme_dir'];

        $this->base_dir = $twig_vars['theme_dir'] . '/style-guide/markup/base/';

        $this->pattern_dir = $twig_vars['theme_dir'] . '/style-guide/markup/patterns/';

        $base_url = '/markup/base/';
        $pattern_url = '/markup/patterns/';

        //page content
        $twig_vars['sg_content'] = 'style_'.$this->content.'.html';

        // get the sg base files

        $base_styles = $this->sg_base();
        if (!empty($base_styles)):
            $twig_vars['_base'] = $base_styles;
            foreach ($twig_vars['_base'] as $key) {
                $name = preg_replace('/-/','_' ,basename($key, '.html'));
                $base_array[$name] = $base_url . $key;
                $source[$name] = $this->get_source($this->base_dir.$key);
            }
            //var_dump($base_array);
            $twig_vars['base'] = $base_array;
            $twig_vars['sg_source'] = $source;
        endif;


        //get sg user patterns files
        $patterns = $this->sg_patterns();
        //var_dump($patterns);
        if (!empty($patterns)):
            $twig_vars['_patterns'] = $patterns;
            foreach ($twig_vars['_patterns'] as $pattern_key) {
                $pattern_name = preg_replace('/-/', '_', basename($pattern_key, '.html'));
                $pattern_array[$pattern_name] = $pattern_url . $pattern_key;
                $pattern_source[$pattern_name] = $this->get_source($this->pattern_dir.$pattern_key);
            }
            $twig_vars['pattern'] = $pattern_array;
            $twig_vars['pattern_source'] = $pattern_source;

        //var_dump($pattern_array);
        endif;


        //override pico default html template
        $system_path = $this->theme_dir . '/style-guide';
        if ($this->stylizer):
            header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK'); // Override 404 header
            $loader = new Twig_Loader_Filesystem($system_path);
            $style_guide = new Twig_Environment($loader, $twig_vars);
            $output = $style_guide->render('style_guide.html', $twig_vars);
            echo $output;
            exit;
        endif;
    }

    public function after_render(&$output) {

    }

    /**
     * Helper function to recusively get all files in a directory
     *
     * @param string $directory start directory
     * @param string $ext optional limit to file extensions
     * @return array the matched files
     * @todo make this function reusable by other plugins (DRY)
     */
    private function get_files($directory, $ext = '') {
        $array_items = array();
        if(!is_dir($directory)) return false;
        if ($handle = opendir($directory)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    if (is_dir($directory . "/" . $file)) {
                        $array_items = array_merge($array_items, $this->get_files($directory . "/" . $file, $ext));
                    } else {
                        $file = $directory . "/" . $file;
                        if (!$ext || strstr($file, $ext))
                            $array_items[] = basename(preg_replace("/\/\//si", "/", $file));
                    }
                }
            }
            closedir($handle);
        }
        return $array_items;
    }

    private function sg_base() {
        $files = $this->get_files($this->base_dir, '.html');
        return $files;
    }

    private function sg_patterns() {
        $files = $this->get_files($this->pattern_dir, '.html');
        return $files;
    }

    public function get_source($file, $title = 'View Source') {
        //$code = $this->theme_url . "/style-guide/index.php#sg-{$name}";
        if(!file_exists($file)) return '';
        $code = file_get_contents($file);
        return $code;
    }



}
