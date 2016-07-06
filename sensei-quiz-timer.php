<?php
/*
 * Plugin Name: Pango Sensei Quiz Timer
 * Version: 1.2.0
 * Plugin URI: http://pango.world
 * Description: Add a timer to your Sensei Quizzes
 * Author: Pango
 * Author URI: http://pango.world
 * Requires at least: 3.5
 * Tested up to: 4.3
 *
 * @package WordPress
 * @author Pango
 * @since 1.0.0
 */

if ( ! defined('ABSPATH')) {
    exit;
}

/**
 * Functions used by plugins
 */
if ( ! class_exists('WooThemes_Sensei_Dependencies')) {
    require_once 'woo-includes/class-woothemes-sensei-dependencies.php';
}
/**
 * Sensei Detection
 */
if ( ! function_exists('is_sensei_active')) {
    function is_sensei_active() {
        return WooThemes_Sensei_Dependencies::sensei_active_check();
    }
}
/**
 * Include plugin class
 */
if (is_sensei_active()) {
    require_once('classes/class-sensei-quiz-timer.php');

    global $sensei_quiz_timer;
    $sensei_quiz_timer = new Sensei_Quiz_Timer(__FILE__);

    require_once('classes/class-sensei-quiz-timer-settings.php');

    global $sensei_quiz_timer_settings;
    $sensei_quiz_timer_settings = new Sensei_Quiz_Timer_Settings(__FILE__);
}
function sensei_timer_load_scripts() {
    wp_enqueue_style('font-awesome-css', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css' , array(), '1.0.0');
}

add_action('wp_enqueue_scripts', 'sensei_timer_load_scripts');
