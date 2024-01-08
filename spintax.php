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
            array( $this, 'spin_callback' ),
            $text
        );
    }

    private function spin_callback( $matches ) {
        if (!empty($matches[1])) {
            return $this->spin_select($matches[1]);
        } elseif (!empty($matches[2])) {
            return $this->spin_permutate($matches[2]);
        }
    }

    private function spin_select( $text ) {
        $parts = explode('|', $text);
        foreach ($parts as $key => $part) {
            $parts[$key] = $this->spin($part);
        }
        return $parts[array_rand($parts)];
    }

    private function spin_permutate( $text ) {
        $parts = explode('|', $text);
        foreach ($parts as $key => $part) {
            $parts[$key] = $this->spin($part);
        }
        shuffle($parts);
        return implode(' ', $parts);
    }

    private function transient_key( $text ) {
        $key = md5($text);
        return sprintf( self::TRANSIENT_KEY_FORMAT, $key );
    }
}
