<?php
/*
Plugin Name: Social Planner Calendar
Description: Social Planner Calendar is a simple WordPress plugin that allows you to intuitively view all scheduled posts with the popular Social Planner plugin.
Version: 1.0.1
Author: Lautaro Linquimán
Author URI: https://github.com/Ymil/
Project URI: https://github.com/Ymil/social-planner-calendar
*/

function social_planner_calendar_get_cron_events()
{
    $cron_events = get_option('cron');
    $events = array();
    foreach ($cron_events as $time => $hooks) {
        foreach ($hooks as $hook => $hook_events) {
            if ($hook == "social_planner_event") {
                $args = $hook_events[array_keys($hook_events)[0]]["args"];
                $post_id = $args[1];
                $event = array(
                    "start" => wp_date('Y-m-d\TH:i:s', $time),
                    "title" => get_the_title($post_id),
                    "url" => get_edit_post_link($post_id, "&"),
                    "image" => get_the_post_thumbnail_url($post_id, 'thumbnail')
                );

                $events[] = $event;
            }
        }
    }
    return $events;
}

function social_planner_calendar_display_calendar($cron_events)
{
    $cron_events = social_planner_calendar_get_cron_events();
    wp_enqueue_script('fullcalendar-js', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js', array('jquery'), '6.1.8', true);

?>
    <style>
        .fc-event {
            flex-direction: column;
            white-space: initial;
            text-align: center;
        }

        .fc-daygrid-event-dot {
            display: none;
        }

        .fc-daygrid-event-harness {
            margin: 3px;
            border: 1px solid black;
            border-radius: 10px;
        }

        .fc-daygrid-event-harness img,
        .fc-list-event-title img {
            max-width: 60px;
            border-radius: 10px;
        }

        .fc-event-time {
            width: 100%;
            border-bottom: 1px solid black;
        }

        .fc-list-event-title {
            display: flex;
            align-items: center;
            justify-content: space-around;
        }

        #cron_calendar_container {
            margin: 10px;
        }
    </style>

    <div id="cron_calendar_container">
        <div id="cron-calendar"></div>
        <script>
            jQuery(document).ready(function($) {
                var events = <?php echo json_encode($cron_events); ?>;
                var calendarEl = document.getElementById('cron-calendar');
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    events: events,
                    initialView: 'dayGridWeek',
                    headerToolbar: {
                        left: 'prev,next',
                        center: 'title',
                        right: 'listMonth,dayGridWeek,dayGridMonth'
                    },
                    eventDidMount: function(info) {
                        var element = $(info.el);
                        console.log(element);
                        var image = info.event.extendedProps.image;
                        if (image) {
                            var imageElement = $('<img>');
                            imageElement.attr('src', image);
                            imageElement.attr('class', 'event-img');
                            if ($(element).find(".fc-list-event-title").length > 0) {
                                $(element).find(".fc-list-event-title").prepend(imageElement);
                            } else {
                                $(element).prepend(imageElement);
                            }
                        }
                    }
                });
                calendar.render();
            });
        </script>
    </div>
<?php
}

function social_planner_calendar_add_calendar_page()
{
    add_menu_page(
        'Social Planner Calendar',
        'Social Planner Calendar',
        'manage_options',
        'cron-calendar',
        'social_planner_calendar_display_calendar',
        'dashicons-calendar-alt',
        75
    );
}
add_action('admin_menu', 'social_planner_calendar_add_calendar_page');
