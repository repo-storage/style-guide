<?php

/**
 * TODO Example hooks for a Pico plugin
 *
 * @package Pico
 * @subpackage TODO
 * @since version TODO
 * @author TODO Shawn Sandy
 * @link TODO http://www.shawnsandy.com
 * @license http://opensource.org/licenses/MIT
 */
class Style_Guide {

    private $plugin_path,
            $stylizer = false,
            $theme_dir,
            $theme_url,
            $base_dir,
            $pattern_dir;

    public function __construct() {
        $this->plugin_path = dirname(__FILE__);
    }

    public function request_url(&$url) {
        //var_dump($url);
        if ($url == 'style_guide')
            $this->stylizer = true;
    }

    public function before_render(&$twig_vars, &$twig) {

         $this->theme_url = $twig_vars['theme_url'];

        $this->theme_dir = $twig_vars['theme_dir'];

        $this->base_dir = $twig_vars['theme_dir'].'/style-guide/markup/base/';
        $this->pattern_dir = $twig_vars['theme_dir'].'/style-guide/markup/patterns';

        $base_url = '/markup/base/';
        $pattern_url = '/markup/patterns';

        // get the sg base files

        $base_styles = $this->sg_base();
        if(!empty($base_styles)):
        $twig_vars['_base'] = $base_styles;
        foreach ($twig_vars['_base'] as $key) {
            $name = basename($key, '.html');
            $base_array[$name] = $base_url.$key;
        }
        //var_dump($base_array);
        $twig_vars['sg_base'] = $base_array;
        endif;


        //get sg user patterns files
        $patterns = $this->sg_patterns();
        //var_dump($patterns);
        if(!empty($patterns)):
        $twig_vars['_patterns'] = $patterns;
        foreach ($twig_vars['_patterns'] as $pattern_key) {
            $pattern_name = basename($pattern_key, '.html');
            $pattern_array[$pattern_name] = $pattern_url.$pattern_key ;
        }
        $twig_vars['sg_patterns'] = $pattern_array;
        //var_dump($pattern_array);
        endif;


        //override pico default pages
       $system_path = $this->theme_dir.'/style-guide';

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
     * Processes any hooks and runs them
     *
     * @param string $hook_id the ID of the hook
     * @param array $args optional arguments
     */
    private function run_hooks($hook_id, $args = array()) {
        if (!empty($this->plugins)) {
            foreach ($this->plugins as $plugin) {
                if (is_callable(array($plugin, $hook_id))) {
                    call_user_func_array(array($plugin, $hook_id), $args);
                }
            }
        }
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


    private function sg_base(){
        $files = $this->get_files($this->base_dir, '.html');
        return $files;
    }

    private function sg_patterns(){
        $files = $this->get_files($this->pattern_dir, '.html');
        return $files;
    }


}

?>