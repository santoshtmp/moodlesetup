/**
 * 
 */
var config_block_yipl_type = document.querySelector('select[id^="id_config_block_yipl_type"]');
var config_courselist_id = document.querySelector('div[id^="fitem_id_config_courselist"]');
// initially when page load
if (
    config_block_yipl_type.value == 'course_list' ||
    config_block_yipl_type.value == 'course_info'
) {
    config_courselist_id.style.display = "";
} else {
    config_courselist_id.style.display = "none";
}
// when block_yipl_type value changes
config_block_yipl_type.addEventListener('change', function () {
    if (
        config_block_yipl_type.value == 'course_list' ||
        config_block_yipl_type.value == 'course_info'
    ) {
        config_courselist_id.style.display = "";
        // config_courselist_id.multiple = false;
    } else {
        config_courselist_id.style.display = "none";
    }
});

/**
 * make config_course_fields_layout required field
 */
var label_block_yipl_type_require = document.querySelector('label[id^="id_config_block_yipl_type"]').nextElementSibling.innerHTML;
document.querySelector('label[id^="id_config_course_fields_layout"]').nextElementSibling.innerHTML = label_block_yipl_type_require;
var config_block_yipl_type = document.querySelector('select[id^="id_config_block_yipl_type"]');
var config_course_fields_layout = document.querySelector('select[name="config_course_fields_layout"]');
config_course_fields_layout.nextElementSibling.innerHTML = '- Required.';
var modal_content = config_course_fields_layout.closest('.modal-content');
if (modal_content) {
    var submit_btn = modal_content.querySelector('.modal-footer .btn-primary');
    submit_btn.addEventListener('click', function (e) {
        if (config_block_yipl_type.value == 'course_info' && config_course_fields_layout.value == '') {
            e.preventDefault();
            e.stopPropagation();
            config_course_fields_layout.nextElementSibling.style.display = 'block';
            return '';
        }
    })
}
config_course_fields_layout.addEventListener('change', function () {
    if (config_course_fields_layout.value) {
        config_course_fields_layout.nextElementSibling.style.display = '';
    }
})

/**
 * config_course_fields_order
 */
var selectedOrder = [];

let course_fields_order = document.querySelector("input[name='config_course_fields_order']");
if (course_fields_order.value) {
    selectedOrder = (course_fields_order.value).split(',');
    var fitem_id_config_course_fields = document.querySelector("div[id^='fitem_id_config_course_fields_']");
    rearrange_option_span_display(selectedOrder, course_fields_order, fitem_id_config_course_fields);
}
document.addEventListener("click", function (event) {
    var fitem_id_config_course_fields = event.target.closest("div[id^='fitem_id_config_course_fields_']");
    if (fitem_id_config_course_fields) {
        // options list clicked to add
        if (event.target.tagName === "LI") {
            let value = event.target.getAttribute('data-value');
            if (!selectedOrder.includes(value) && value) {
                selectedOrder.push(value);
            }
            rearrange_option_span_display(selectedOrder, course_fields_order, fitem_id_config_course_fields)
        }
        // option to remove 
        if (event.target.tagName === "SPAN" &&
            event.target.closest(".form-autocomplete-selection")
        ) {
            let value = event.target.getAttribute('data-value');
            selectedOrder = selectedOrder.filter(item => item !== value);
            rearrange_option_span_display(selectedOrder, course_fields_order, fitem_id_config_course_fields)
        }
    }
});



/**
 * config_courselist_order
 */
let order_list_rearrange = document.querySelector("input[name='config_courselist_order']");
if (order_list_rearrange.value) {
    selectedOrder = (order_list_rearrange.value).split(',');
    var fitem_id_config_courselist = document.querySelector("div[id^='fitem_id_config_courselist_']");
    rearrange_option_span_display(selectedOrder, order_list_rearrange, fitem_id_config_courselist);
}

document.addEventListener("click", function (event) {
    var fitem_id_config_courselist = event.target.closest("div[id^='fitem_id_config_courselist_']");
    if (fitem_id_config_courselist) {
        // options list clicked to add
        if (event.target.tagName === "LI") {
            let value = event.target.getAttribute('data-value');
            if (!selectedOrder.includes(value) && value) {
                selectedOrder.push(value);
            }
            rearrange_option_span_display(selectedOrder, order_list_rearrange, fitem_id_config_courselist)
        }
        // option to remove 
        if (event.target.tagName === "SPAN" &&
            event.target.closest(".form-autocomplete-selection")
        ) {
            let value = event.target.getAttribute('data-value');
            selectedOrder = selectedOrder.filter(item => item !== value);
            rearrange_option_span_display(selectedOrder, order_list_rearrange, fitem_id_config_courselist)
        }
    }
});


/**
 * 
 * @param {*} selectedOrder 
 * @param {*} order_list_rearrange 
 * @param {*} fitem_id_config_courselist 
 */
function rearrange_option_span_display(selectedOrder, order_list_rearrange, fitem_id_config_courselist) {
    // 
    if (selectedOrder.length > 0) {
        order_list_rearrange.value = selectedOrder.join(',');
        // 
        setTimeout(function () {
            let container = fitem_id_config_courselist.querySelector(".form-autocomplete-selection");
            if (container) {
                // Convert the spans into an array and sort them based on the defined selectedOrder
                let sortedSpans = Array.from(container.children).sort((a, b) => {
                    let aIndex = selectedOrder.indexOf(a.getAttribute("data-value"));
                    let bIndex = selectedOrder.indexOf(b.getAttribute("data-value"));
                    return aIndex - bIndex;
                });
                // Append the sorted elements back to the container
                sortedSpans.forEach(span => container.appendChild(span));
            }

        }, 500);
    } else {
        order_list_rearrange.value = ''
    }
    // console.log(selectedOrder);
    // console.log(order_list_rearrange.value);
}