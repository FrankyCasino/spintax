<?php
/**
 * Plugin Name: Spintax
 * Plugin URI:  https://github.com/FrankyCasino/spintax/
 * Description: Spintax is a dynamic content generation plugin for WordPress, designed to enhance your website's uniqueness and SEO effectiveness.
 * Version:     1.1
 * Author:      Crypto Casino
 * Author URI:  https://cryptocasino.ws
 * Text Domain: spintax
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.6
 * Requires PHP: 7.4
 */

class SpinText {
    private $cache_expiry;

    public function __construct($cache_expiry = 0) {
        $this->cache_expiry = (int) $cache_expiry;
    }

    private function transient_key($text) {
        return sprintf('spintax_cache_%s', md5($text));
    }

    public function spin($text) {
        $text = trim(strval($text));
        $cache_expiry = $this->cache_expiry;
        $transient_key = $this->transient_key($text);

        if ($cache_expiry && ($cached = get_transient($transient_key))) {
            return $cached;
        }

        $spun = $this->do_spin($text);

        if ($cache_expiry) {
            set_transient($transient_key, $spun, $cache_expiry);
        }

        return $spun;
    }

    private function do_spin($text) {
        while (preg_match('/\{([^{}]*)\}|\[([^[\]]*)\]/', $text)) {
            $text = preg_replace_callback(
                '/\{([^{}]*)\}|\[([^[\]]*)\]/',
                function ($matches) {
                    if (!empty($matches[1])) {
                        $parts = explode('|', $matches[1]);
                        return $this->do_spin($parts[array_rand($parts)]);
                    } elseif (!empty($matches[2])) {
                        $parts = explode('|', $matches[2]);
                        shuffle($parts);
                        return implode(' ', $parts);
                    }
                },
                $text
            );
        }
        return $text;
    }


}



function validate_spintax($text) {
    $stack = [];
    $errors = [];
    $length = strlen($text);

    for ($i = 0; $i < $length; $i++) {
        switch ($text[$i]) {
            case '{':
            case '[':
                array_push($stack, ['char' => $text[$i], 'pos' => $i]);
                break;
            case '}':
            case ']':
                if (empty($stack)) {
                    $errors[] = "Extra closing bracket at position $i";
                } else {
                    $last = array_pop($stack);
                    if (($text[$i] == '}' && $last['char'] != '{') ||
                        ($text[$i] == ']' && $last['char'] != '[')) {
                        $errors[] = "Mismatched closing bracket at position $i";
                    }
                }
                break;
        }
    }

    foreach ($stack as $bracket) {
        $errors[] = "Unmatched " . $bracket['char'] . " at position " . $bracket['pos'];
    }

    return (empty($errors) ? true : $errors);
}

function spintax($atts, $content = '') {
    // Убедимся, что содержимое не пустое
    if (empty($content)) {
        return 'No content provided for spintax.';
    }

    // Парсинг атрибутов (если они есть)
    $options = shortcode_atts(['cache' => 86400], $atts);
    $spinner = new SpinText($options['cache']);

    // Валидация Spintax
    $validation_result = validate_spintax($content);
    if ($validation_result !== true) {
        return 'Error in Spintax: ' . implode("; ", $validation_result);
    }

    // Обработка и возврат результата
    return $spinner->spin($content);
}

add_shortcode('spintax', 'spintax');
