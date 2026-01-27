/**
 * 
 * 
 */

var id_customfield_course_type = document.getElementById("id_customfield_course_type");
var title = document.querySelector('#region-main div[role="main"] > h2');
var option_list = document.querySelectorAll('#id_customfield_course_type option');

option_list.forEach(function (item, index) {
    if ((item.textContent).toLowerCase() === "scholarship") {
        id_customfield_course_type.value = index;
        title.innerHTML = "Add a new scholarship";
    }
});
