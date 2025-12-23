jQuery(document).ready(function ($) {
    if (sp_lms_vars.daily_limit_reached) {
        disablePlayer(sp_lms_vars.messages.limit_reached);
        return;
    }

    let watchInterval;
    let secondsWatched = 0;
    let isTabActive = true;
    let isVideoPlaying = false;
    let courseId = 0; // Should be populated dynamically based on page context or data attributes

    // Try to find course ID from DOM
    if ($('input[name="course_id"]').length > 0) {
        courseId = $('input[name="course_id"]').val();
    } else if (typeof tutor_course_id !== 'undefined') { // Tutor LMS global var check
        courseId = tutor_course_id;
    }

    // Visibility API (Anti-Cheat: Tab Background)
    document.addEventListener("visibilitychange", function () {
        if (document.hidden) {
            isTabActive = false;
            pauseAllVideos(); // Force pause if user leaves tab
        } else {
            isTabActive = true;
        }
    });

    // Heartbeat every 60s
    watchInterval = setInterval(function () {
        if (isTabActive && isVideoPlaying) {
            sendHeartbeat();
        }
    }, 60000);

    function sendHeartbeat() {
        if (!courseId) return;

        $.post(sp_lms_vars.ajax_url, {
            action: 'sp_lms_log_watchtime',
            security: sp_lms_vars.nonce,
            course_id: courseId
        }, function (response) {
            if (!response.success && response.data.code === 'limit_reached') {
                disablePlayer(response.data.message);
                clearInterval(watchInterval);
            }
        });
    }

    function disablePlayer(message) {
        pauseAllVideos();
        // Overlay blocking div
        $('body').append('<div style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.9);z-index:99999;display:flex;justify-content:center;align-items:center;color:white;font-size:24px;text-align:center;">' + message + '</div>');
    }

    // Generic Player Control (Vimeo/YouTube wrappers)
    // This assumes iframes are used. Ideally we use the Vimeo Player SDK.
    function pauseAllVideos() {
        $('iframe').each(function () {
            // PostMessage to pause Vimeo/YouTube
            this.contentWindow.postMessage(JSON.stringify({ method: 'pause' }), '*'); // Vimeo
            this.contentWindow.postMessage(JSON.stringify({ event: 'command', func: 'pauseVideo' }), '*'); // YouTube
        });
        isVideoPlaying = false;
    }

    // Listen for Player Events (Basic Implementation)
    $(window).on('message', function (e) {
        // Vimeo / YouTube events can be complex to standardize without SDKs.
        // This is a simplified listener assuming standard postMessage events.

        // In a real production environment, you should include the Vimeo Player SDK 
        // and YouTube Iframe API scripts and instantiate players properly.
        // For this white-label setup, we assume standard behavior or use a specific selector.

        // HEURISTIC: Assume playing if we don't catch a pause
        // Ideally we hook into Tutor LMS's player events if available.
        // For now, we rely on the user interacting or the SDKs being present.

        // Force "isVideoPlaying = true" for testing if we can't detect perfectly without SDK
        // But the requirement says "based on player event".
        // Let's implement a listener for Vimeo:
        try {
            var data = JSON.parse(e.originalEvent.data);
            if (data.event === 'play') isVideoPlaying = true;
            if (data.event === 'pause') isVideoPlaying = false;
            // Vimeo finish
            if (data.event === 'finish') isVideoPlaying = false;
        } catch (err) { }
    });

    // Provide a way to manually signal play (fallback)
    // Tutor LMS often wraps videos in a .tutor-video-player
    $('.tutor-video-player').on('click', function () {
        isVideoPlaying = true; // Optimistic
    });
});
