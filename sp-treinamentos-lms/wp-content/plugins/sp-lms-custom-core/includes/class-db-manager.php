<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SP_LMS_DB_Manager {

    /**
     * Create tables on plugin activation
     */
    public static function create_tables( $network_wide ) {
        global $wpdb;

        if ( is_multisite() && $network_wide ) {
            // Get all blog IDs
            $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
            foreach ( $blog_ids as $blog_id ) {
                switch_to_blog( $blog_id );
                self::db_delta();
                restore_current_blog();
            }
        } else {
            self::db_delta();
        }
    }

    /**
     * Handle new site creation in Multisite
     */
    public static function on_new_blog( $new_site_id, $args ) {
        switch_to_blog( $new_site_id );
        self::db_delta();
        restore_current_blog();
    }

    /**
     * Define and create table schemas
     */
    private static function db_delta() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        // 1. Daily Watchtime Control
        $table_watchtime = $wpdb->prefix . 'user_watchtime';
        $sql_watchtime = "CREATE TABLE $table_watchtime (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            course_id bigint(20) NOT NULL,
            date date NOT NULL,
            seconds_watched int(11) DEFAULT 0 NOT NULL,
            PRIMARY KEY  (id),
            KEY user_date (user_id, date)
        ) $charset_collate;";

        // 2. Student Type (Special Users)
        $table_student_type = $wpdb->prefix . 'student_type';
        $sql_student_type = "CREATE TABLE $table_student_type (
            user_id bigint(20) NOT NULL,
            type enum('default','special') DEFAULT 'default' NOT NULL,
            PRIMARY KEY  (user_id)
        ) $charset_collate;";

        // 3. Certificate Models
        $table_cert_model = $wpdb->prefix . 'course_certificate_model';
        $sql_cert_model = "CREATE TABLE $table_cert_model (
            course_id bigint(20) NOT NULL,
            certificate_template_id bigint(20) NOT NULL,
            PRIMARY KEY  (course_id)
        ) $charset_collate;";

        dbDelta( $sql_watchtime );
        dbDelta( $sql_student_type );
        dbDelta( $sql_cert_model );
    }
}
