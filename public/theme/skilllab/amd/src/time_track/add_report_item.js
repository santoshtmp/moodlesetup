import $ from 'jquery';

export const init = (e) => {


    // var time_track_link = '<a href="/theme/skilllab/pages/report/time_track.php?course_id=' + e + '">Course time track</a>';
    var time_track_link = '<a href="/time-track-report?course_id=' + e + '">Course time track</a>';

    $('#time-track-item').html(time_track_link);

};