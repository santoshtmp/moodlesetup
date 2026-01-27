
## Changes Which are required
1. Need to have .htaccess file to make clean url for skilllab/pages
2. custom login page
3. API is developed to register and signin user from CSC
4. Login API - skilllab/pages/login.php and all other API are in skilllab/classes/api
5. Additional plugins 
   1. admin/tool/sentry
   2. availability/condition/relativedate
   3. availability/condition/yipl_duration time restriction plugin
   4. mod/book
   5. mod/customcert
   6. question/format/csv
   7. question/format/wordtable
   8. report/benchmark
   9. local/easycustmenu
   10. local/customcleanurl
   11. https://moodle.org/plugins/local_adminer  :: adminer_secret = "csc-lms-@2025-YIPL"


<!-- -------------------------------------------- -->
## Core files changes, which are 
1. logout page : /login/logout.php
        /**
        * The below code should also be kept in core moodle logout page
        * This is to redirect to skill lab custom logout page
        * first check if theme url is present or not and then redirect
        */
        $url = '/login/logout.php';
        $goto_url = '/theme/skilllab/pages/login/csc-lms-logout.php';
        $this_url = $_SERVER['REQUEST_URI'];
        if (str_contains($this_url, $url)) {
            global $USER;
            $filepath = $CFG->dirroot . $goto_url;
            if (file_exists($filepath)) {
                redirect($goto_url . '?sesskey=' . $USER->sesskey);
            }
        }
        /**
        * End Here skill lab logout redirect
        */
<!-- -------Admin Setting changes -------- -->
## Moodle Admin Setting changes
1. Disable Advance features 
    1. Note 
    2. Blog
    3. badges
    4. messaging system
    5. tags 
    6. Enable web services
2. Disable Notificateion system
3. Disable Authenticated user Capability 
    1. Change own password moodle/user:changeownpassword
    2. Edit own user profile moodle/user:editownprofile
    2. moodle/user:manageownblocks
    4. moodle/blog:create
    5. moodle/blog:manageexternal
4. Define custom role Institution [Short name => institution, role_id => 10]
5. Define default country - NP and timezone - Asia/Kathmandu
6. Disable Studnet and institution role capability
    1. View participants moodle/course:viewparticipants	
7. Disable other and enable only manual enrollment method
8. Disable other unwanted question type and allow only following types:
    1. Multiple choice
    2. True/False
    3. Matching
    4. Short answer
9. Manage Theme setting
10. Course Custom Field
    1. Short name => skill_level [options => Basic, Intermediate, Advanced]
    2. Short name => course_duration
    3. Short name => course_type [options => Scholarship, Course, Assessment]
11. user profile custom Fiedl
    1. Short name => institution_name
    2. Short name => institution_domain
12. Default course setting
    1. Number of section = 2
    2. Course end data = uncheck
    3. Number of announcements = 0
13. Default Quiz General Setting
    1. Remove Review Right answer
14. Custom Certificate template setting
15. Navigation Setting
    1. Disable Dashboard for all users
    2. Start page = Home
    3. Show course full names
16. Disable Site security settings
    1. Allow extended characters in usernames
    2. Force users to log in  
    3. Maximum uploaded file size = 2MB
    4. Private files space = 100 KB
17. Enable Web service and make api-user admin and its API token
18. Language customisation
    1. course.php [ Done => Mark as undo ]

<!-- ------------------------------------------------------------------ -->
	

## Development Reference:
1. https://docs.moodle.org/dev/Creating_a_theme_based_on_boost
2. https://docs.moodle.org/dev/Category:Themes
3. https://moodledev.io/docs/guides/templates   https://docs.moodle.org/dev/Templates
4. https://moodledev.io/docs/guides/javascript/modules 
5. https://moodledev.io/docs/apis/plugintypes/format#format-output-classes-and-templates
6. https://moodledev.io/docs/apis/core/dml  https://docs.moodle.org/dev/Data_manipulation_API
7. https://moodledev.io/docs/apis
8. https://moodledev.io/docs/apis/subsystems/external/writing-a-service
9. https://docs.moodle.org/dev/Web_service_API_functions
10. https://docs.moodle.org/dev/Events_API
11. https://moodledev.io/docs/apis/subsystems/task/scheduled https://docs.moodle.org/dev/Task_API 
12. and others from moodle doc

<!-- ------------------------------------------------------------------ -->
