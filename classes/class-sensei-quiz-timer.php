<?php

if ( ! defined('ABSPATH')) {
    exit;
}

class Sensei_Quiz_Timer {
    private $dir;
    private $file;
    private $assets_dir;
    private $assets_url;
    private $order_page_slug;
    public $taxonomy;


    public function __construct($file)
    {
        $this->file = $file;
        $this->dir = dirname($this->file);
        $this->assets_dir = trailingslashit($this->dir).'assets';
        $this->assets_url = esc_url(trailingslashit(plugins_url('/assets/', $this->file)));
        $this->taxonomy = 'timer';
        $this->order_page_slug = 'quiz-timer';

        // Check if user enabled quiz timer


        // Enque CSS and JS scripts
        add_action('wp_head', array($this, 'enqueue_quiz_timer_scripts'), 10);


        // Hook in quiz timer to quiz questions page
        add_action('sensei_single_quiz_questions_before', array($this, 'sensei_quiz_timer_before'), 11);
        add_action('sensei_single_quiz_questions_after', array($this, 'sensei_quiz_timer_after'), 11);

        // Add ajax calls to be accessed
        add_action('wp_ajax_quiz_start', array($this, 'quiz_start'));
        add_action('wp_ajax_nopriv_quiz_start', array($this, 'quiz_start'));
        add_action('wp_ajax_quiz_remain', array($this, 'quiz_remain'));
        add_action('wp_ajax_nopriv_quiz_remain', array($this, 'quiz_remain'));
        add_action('wp_ajax_quiz_time', array($this, 'quiz_time'));
        add_action('wp_ajax_nopriv_quiz_time', array($this, 'quiz_time'));
        add_action('wp_ajax_quiz_end', array($this, 'quiz_end'));
        add_action('wp_ajax_nopriv_quiz_end', array($this, 'quiz_end'));

        // Set ajax url variable
        add_action('wp_head', array($this, 'pluginname_ajaxurl'));

        // Initiate session
        add_action('init', array($this, 'register_session'));




    }


    /**
     * Load admin files
     * @return void
     */
    public function enqueue_quiz_timer_scripts() {

            wp_enqueue_style('quiz-timer-css', $this->assets_url.'css/sensei-quiz-timer.css', '1.0.0');
            wp_register_script('quiz-timer-js', $this->assets_url.'js/sensei-quiz-timer.js', array(),
                '1.1',
                true);
            wp_localize_script( 'ajax-script', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
            wp_enqueue_script('quiz-timer-js');

    }

    // Set up condition and notice to hide quiz and show start button
    public function sensei_quiz_timer_before(){
        global $post, $current_user;

        $quiz_enable = Sensei()->settings->settings[ 'sensei_quiz_timer'];
        if ($quiz_enable == true) {

            //setup quiz grade
            $user_quiz_grade = '';
            $lesson_id = (int)get_post_meta($post->ID, '_quiz_lesson', true);
            $user_lesson_status = WooThemes_Sensei_Utils::user_lesson_status($lesson_id, $current_user->ID);

            if (!empty($user_lesson_status)) {
                $user_quiz_grade = get_comment_meta($user_lesson_status->comment_ID, 'grade', true);
            }
            $quiz_limit = get_post_meta($post->ID, 'pango-qt_limit', true);
            // Lesson Quiz Meta

            if (($quiz_limit != '') && ('' == $user_quiz_grade)) {
                unset($_SESSION['quizLimit']);
                $_SESSION['quizLimit'] = $quiz_limit;

                // Alert to show user quiz time limit
                echo '<div class="sensei-message alert">';
                echo __("This quiz has a time limit of " . $quiz_limit . " minutes", "woothemes-sensei");
                echo '</div>';
                if (!isset($_SESSION['quizStart'])) {
                    echo '<button id="start-quiz">Start quiz</button>';
                } else {
                    echo '<button id="start-quiz">Resume quiz</button>';
                }


                // Element to hold timer
                echo '<div id="timerbox"></div>';

                // Element to hide quiz questions
                echo '<div id="quiz-form">';

            }
        }



    } // end ensei_quiz_timer_before

    // End of div to hide quiz
    public function sensei_quiz_timer_after(){
        $quiz_enable = Sensei()->settings->settings[ 'sensei_quiz_timer'];
        if ($quiz_enable == true) {
            echo '</div>';
        }



    } // end ensei_quiz_timer_after


    // Start quiz and set session
    public function quiz_start() {
        // quiz-start.php
        if (!isset($_SESSION)) {
            session_start();

        }
        if (!isset($_SESSION['quizStart'])) {
            unset($_SESSION['quizStart']);
            $_SESSION['quizStart'] = time();
        }
        echo json_encode($_SESSION['quizStart']);

    }

    // Determine and display remaining time
    public function quiz_time() {

        if (!isset($_SESSION)) {
            session_start();
        }
        $start_time = $_SESSION['quizStart'];
        $now = time();
        $end_time = $start_time + ($_SESSION['quizLimit']*60);
        $time_left = $end_time - $now;
        $_SESSION['quizRemaining'] = $time_left;
        if ($time_left >= 0) {

           echo '<div id="time">Time Remaining: '.gmdate('H:i:s',$time_left).'</div>';
        }
        die();

    }

    // Collect remaining time
    public function quiz_remain() {

        if (!isset($_SESSION)) {
            session_start();
        }
        if (isset($_SESSION['quizRemaining'])) {
            echo json_encode($_SESSION['quizRemaining']);
        }
        die();

    }

    // Make sure to unset session incase they restart quiz
    public function quiz_end() {

        if (!isset($_SESSION)) {
            session_start();
        }
        unset($_SESSION['quizStart']);
        die();

    }


    // set ajax url variable
    public function pluginname_ajaxurl() {
        echo '<script type="text/javascript">
    var ajaxurl = "'.admin_url("admin-ajax.php").'"
        </script>';

    }

    // Make sure session is registered
    function register_session(){
        if( !session_id() )
            session_start();
    }


}