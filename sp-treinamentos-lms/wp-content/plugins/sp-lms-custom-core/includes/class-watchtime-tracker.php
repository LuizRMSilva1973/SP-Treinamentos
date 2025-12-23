<?php

if (!defined('ABSPATH')) {
    exit;
}

class SP_LMS_Watchtime_Tracker
{

    const DAILY_LIMIT_SECONDS = 28800; // 8 hours

    public function __construct()
    {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_sp_lms_log_watchtime', array($this, 'ajax_log_watchtime'));

        // Prevent lesson completion if limit reached
        add_filter('tutor_can_complete_lesson', array($this, 'check_completion_permission'), 10, 2);
    }

    public function enqueue_scripts()
    {
        if (is_singular('courses') || is_singular('lesson')) {
            wp_enqueue_script('sp-lms-player-control', SP_LMS_CORE_URL . 'assets/js/lms-player-control.js', array('jquery'), SP_LMS_CORE_VERSION, true);

            wp_localize_script('sp-lms-player-control', 'sp_lms_vars', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('sp_lms_watchtime_nonce'),
                'user_id' => get_current_user_id(),
                'daily_limit_reached' => $this->has_reached_daily_limit(get_current_user_id()),
                'messages' => array(
                    'limit_reached' => 'Limite diário de estudo atingido (8 horas). Volte amanhã.',
                    'paused_tab' => 'O vídeo foi pausado pois você saiu da aba.'
                )
            ));
        }
    }

    public function ajax_log_watchtime()
    {
        check_ajax_referer('sp_lms_watchtime_nonce', 'security');

        $user_id = get_current_user_id();
        $course_id = intval($_POST['course_id']);
        $seconds = 60; // Fixed increment

        if (!$user_id) {
            wp_send_json_error('User not logged in');
        }

        if ($this->has_reached_daily_limit($user_id)) {
            wp_send_json_error(array('code' => 'limit_reached', 'message' => 'Limite diário atingido.'));
        }

        $this->log_time($user_id, $course_id, $seconds);

        wp_send_json_success(array('status' => 'logged'));
    }

    private function log_time($user_id, $course_id, $seconds)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'user_watchtime';
        $today = date('Y-m-d');

        // Check if entry exists for today
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT id, seconds_watched FROM $table WHERE user_id = %d AND date = %s",
            $user_id,
            $today
        ));

        if ($row) {
            $wpdb->update(
                $table,
                array('seconds_watched' => $row->seconds_watched + $seconds),
                array('id' => $row->id)
            );
        } else {
            $wpdb->insert(
                $table,
                array(
                    'user_id' => $user_id,
                    'course_id' => $course_id,
                    'date' => $today,
                    'seconds_watched' => $seconds
                )
            );
        }
    }

    public function has_reached_daily_limit($user_id)
    {
        if ($this->is_special_user($user_id)) {
            return false;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'user_watchtime';
        $today = date('Y-m-d');

        $total_seconds = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(seconds_watched) FROM $table WHERE user_id = %d AND date = %s",
            $user_id,
            $today
        ));

        return intval($total_seconds) >= self::DAILY_LIMIT_SECONDS;
    }

    private function is_special_user($user_id)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'student_type';
        $type = $wpdb->get_var($wpdb->prepare("SELECT type FROM $table WHERE user_id = %d", $user_id));
        // Note: Default is 'default', so 'special' must be explicitly set
        return 'special' === $type;
    }

    public function check_completion_permission($can_complete, $lesson_id)
    {
        if ($this->has_reached_daily_limit(get_current_user_id())) {
            return false;
        }
        return $can_complete;
    }
}
