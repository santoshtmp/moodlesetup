
Performance Reference URL: 
1. https://docs.moodle.org/402/en/Performance_FAQ
2. https://docs.moodle.org/402/en/Performance_recommendations
3. https://docs.moodle.org/402/en/Large_installations

<!--  ------------------------------------------  -->

Moodle is built on PHP and primarily uses web service APIs to interact with external systems or applications. The performance of these APIs depends on the server's hardware capabilities, including CPU, RAM, and network speed. As the server's resources increase, the capacity to handle concurrent API requests also improves.
There is no api limit other than just the server resources

<!--  ------------------------------------------  -->

List points
1. Moodle site may only handle as few as 10-20 concurrent users per GB of memory. 
2. The optimization order preference is usually: primary storage (more RAM), secondary storage (faster hard disks/improved hard disk configuration), processor (more and faster)
3. increase the amount of RAM on your web server (e.g. 4GB or more)
4. processor capability i.e. dual or dual core processors.
5. Unix-based OS

<!--  ------------------------------------------  -->

Moodle RAM:
1. Small Installations (up to 100 users): For a small Moodle installation with up to 100 users and light course content, you can start with a server configuration of around 2 to 4 GB of RAM. This should be sufficient to handle basic usage and a limited number of concurrent users.
2. Medium Installations (100 to 500 users): For a medium-sized Moodle site with up to 500 users and moderately complex courses, consider a server with 8 to 16 GB of RAM. This should handle increased traffic and activity on the platform.
3. Large Installations (500+ users): For larger Moodle installations with 500 or more concurrent users or complex courses, you may need 16 GB of RAM or more. In some cases, you might even require 32 GB or higher, especially if you have a significant number of active users and resource-intensive activities.

<!--  ------------------------------------------  -->


