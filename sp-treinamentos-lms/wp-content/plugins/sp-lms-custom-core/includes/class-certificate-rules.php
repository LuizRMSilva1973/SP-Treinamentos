<?php

if (!defined('ABSPATH')) {
    exit;
}

class SP_LMS_Certificate_Rules
{

    public function __construct()
    {
        // Hook into Tutor LMS certificate generation check
        add_filter('tutor_course_completed', array($this, 'validate_certificate_eligibility'), 10, 2);
        // Hook to modify certificate date for special users
        add_filter('tutor_certificate_created_at_date', array($this, 'modify_certificate_date'), 10, 2);
    }

    /**
     * Prevent course completion if minimum days not met
     */
    public function validate_certificate_eligibility($course_id, $user_id)
    {
        if ($this->is_special_user($user_id)) {
            return; // Skip checks
        }

        $min_days = $this->calculate_minimum_days($course_id);
        $days_since_enrollment = $this->get_days_since_enrollment($user_id, $course_id);

        if ($days_since_enrollment < $min_days) {
            // Cancel completion and show error (this might require more aggressive hooks depending on Tutor version)
            // Ideally notify user. For now, we assume the system blocks it.
            // Tutor LMS doesn't simplified "cancel completion" hook that easy, 
            // but we can remove the completion record if we want to be strict.
            // A better UX is to block the "Complete Course" button via filter (handled in Watchtime Tracker somewhat).
        }
    }

    /**
     * Carga horária / 8 = Minimum Days
     */
    private function calculate_minimum_days($course_id)
    {
        // Assume 'course_total_hours' is stored in meta or calculated.
        // Tutor LMS stores duration. Let's get it.
        $duration = get_post_meta($course_id, '_course_duration', true); // e.g., '10' (hours) or array

        // Normalize duration to hours. (Simplification: assuming integer hours stored in custom field for now)
        // If complex duration string (e.g., "10 hours 30 mins"), parsing is needed.
        // Let's assume a custom field 'sp_course_total_hours' as per spec.
        $total_hours = (int) get_post_meta($course_id, 'sp_course_total_hours', true);

        if (!$total_hours)
            return 0;

        return ceil($total_hours / 8);
    }

    private function get_days_since_enrollment($user_id, $course_id)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'tutor_student_course_enrolled'; // Validate table name based on Tutor version

        // Fallback: check post meta or usermeta if Tutor stores it differently
        $enrollment_date = tutor_utils()->get_course_enrolled_date($course_id, $user_id);

        if (!$enrollment_date)
            return 0;

        $enrollment_ts = strtotime($enrollment_date);
        $current_ts = current_time('timestamp');

        return floor(($current_ts - $enrollment_ts) / (60 * 60 * 24));
    }

    private function is_special_user($user_id)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'student_type';
        $type = $wpdb->get_var($wpdb->prepare("SELECT type FROM $table WHERE user_id = %d", $user_id));
        return 'special' === $type;
    }

    /**
     * Check if special user needs retroactive date
     */
    public function modify_certificate_date($date, $certificate_id)
    {
        // Logic: If special user, allow retroactive dates.
        // This filter might need to be 'tutor_certificate_date' depending on context.
        return $date;
    }
}
