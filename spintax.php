<?php
/**
 * Plugin Name: Spintax
 * Plugin URI:  https://github.com/FrankyCasino/spintax/
 * Description: Spintax is a dynamic content generation plugin for WordPress, designed to enhance your website's uniqueness and SEO effectiveness.
 * Version:     1.0
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
            $parts = $this->permutate(explode('|', $matches[2]));
            return implode(' ', array_map('esc_html', $parts)); // Экранирование каждого элемента и объединение с пробелом
        }
    }

    // Функция для перестановок
    private function permutate($array) {
        $result = array();
        $recurse = function($array, $start_i = 0) use (&$result, &$recurse) {
            if ($start_i === count($array)-1) {
                array_push($result, $array);
            }

            for ($i = $start_i; $i < count($array); $i++) {
                // Меняем элементы местами
                $temp = $array[$i];
                $array[$i] = $array[$start_i];
                $array[$start_i] = $temp;

                // Рекурсивный вызов
                $recurse($array, $start_i + 1);

                // Возвращаем элементы обратно
                $array[$start_i] = $array[$i];
                $array[$i] = $temp;
            }
        };

        $recurse($array);

        // Возвращаем случайный вариант перестановки
        return $result[array_rand($result)];
    }

    function transient_key( $text ) {
        $key = md5($text);
        return sprintf( self::TRANSIENT_KEY_FORMAT, $key );
    }
}

