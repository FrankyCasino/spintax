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

function spintax( $text, $options = array() ) {
    $options = wp_parse_args( $options, array(
        'cache' => 86400, // Кэширование на 1 день
    ) );
    $spinner = new SpinText( $options['cache'] );

    // Очистка входного текста
    $text = sanitize_text_field( $text );

    // Валидация шаблона Spintax
    $validation_result = validate_spintax($text);
    if ($validation_result !== true) {
        // В случае обнаружения ошибки возвращаем сообщение об ошибке
        return 'Error in Spintax: ' . implode("; ", $validation_result);
    }

    return $spinner->spin( $text );
}

add_shortcode( 'spintax', 'spintax' );

class SpinText {
    const TRANSIENT_KEY_FORMAT = 'spintax_cache_%s';
    private $cache_expiry = 0;

    public function __construct( $cache_expiry = 0 ) {
        $this->cache_expiry = (int) $cache_expiry;
    }

    public function spin( $text ) {
        $text = trim( strval( $text ) );
        $cache_expiry = $this->cache_expiry;
        $transient_key = $this->transient_key( $text );

        if ( $cache_expiry ) {
            $cached = get_transient( $transient_key );
            if ( ! empty( $cached ) ) {
                return $cached;
            }
        }

        $spun = $this->do_spin( $text );

        if ( $cache_expiry ) {
            set_transient( $transient_key, $spun, $cache_expiry );
        }

        return $spun;
    }

    private function do_spin( $text ) {
        return preg_replace_callback(
            '/\{([^{}]*)\}|\[([^[\]]*)\]/',
            array( $this, 'replace' ),
            $text
        );
    }

    private function replace( $matches ) {
        if (!empty($matches[1])) {
            $parts = explode('|', $matches[1]);
            return esc_html($parts[array_rand($parts)]); // Экранирование для безопасности
        } elseif (!empty($matches[2])) {
            $parts = explode('|', $matches[2]);
            shuffle($parts);
            return implode('', array_map('esc_html', $parts)); // Экранирование каждого элемента
        }
    }

    function transient_key( $text ) {
        $key = md5($text);
        return sprintf( self::TRANSIENT_KEY_FORMAT, $key );
    }
}

function validate_spintax( $text ) {
    $stack = [];
    $errors = [];
    $length = strlen($text);

    for ($i = 0; $i < $length; $i++) {
        if ($text[$i] == '{' || $text[$i] == '[') {
            array_push($stack, ['char' => $text[$i], 'pos' => $i]);
        } elseif ($text[$i] == '}' || $text[$i] == ']') {
            if (empty($stack)) {
                $errors[] = "Extra closing bracket at position $i";
            } else {
                $last = array_pop($stack);
                if (($text[$i] == '}' && $last['char'] != '{') ||
                    ($text[$i] == ']' && $last['char'] != '[')) {
                    $errors[] = "Mismatched closing bracket at position $i";
                }
            }
        }
    }

    if (!empty($stack)) {
        foreach ($stack as $bracket) {
            $errors[] = "Unmatched " . $bracket['char'] . " at position " . $bracket['pos'];
        }
    }

    return (empty($errors) ? true : $errors);
}
