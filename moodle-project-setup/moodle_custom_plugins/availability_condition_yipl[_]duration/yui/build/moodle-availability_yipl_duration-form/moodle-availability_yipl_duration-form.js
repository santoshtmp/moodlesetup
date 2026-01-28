YUI.add('moodle-availability_yipl_duration-form', function (Y, NAME) {

/**
 * JavaScript for form editing completion conditions.
 *
 * @module moodle-availability_yipl_duration-form
 */
M.availability_yipl_duration = M.availability_yipl_duration || {};

/**
 * @class M.availability_yipl_duration.form
 * @extends M.core_availability.plugin
 */
M.availability_yipl_duration.form = Y.Object(M.core_availability.plugin);

/**
 * Initialises this plugin.
 *
 * @method initInner
 * @param {Array} cms Array of objects containing cmid => name
 */
M.availability_yipl_duration.form.initInner = function (cms) {
    this.cms = cms;
};

M.availability_yipl_duration.form.getNode = function (json) {
    // Create HTML structure.
    var html = '<span class="col-form-label pr-3"> ' + M.util.get_string('title', 'availability_yipl_duration') + '</span>' +
        ' <span class="availability-group form-group"><label>' +
        '<span class="accesshide">' + M.util.get_string('label_cm', 'availability_yipl_duration') + ' </span>' +
        '<select class="custom-select" name="cm" title="' + M.util.get_string('label_cm', 'availability_yipl_duration') + '">';
    for (var i = 0; i < this.cms.length; i++) {
        var cm = this.cms[i];
        // String has already been escaped using format_string.
        html += '<option value="' + cm.id + '">' + cm.name + '</option>';
    }
    html += '</select></label></span>';
    var html_options = '<option value="0">Select Number </option>';
    for (var i = 1; i < 100; i++) {
        html_options += '<option value="' + i + '">' + i + '</option>';
    }
    html += '<span class="availability-group form-group"> <label><span class="accesshide">' +
        M.util.get_string('label_completion', 'availability_yipl_duration') +
        ' </span><select class="custom-select" name="e" title="' + M.util.get_string('label_completion', 'availability_yipl_duration') + '">' + html_options +
        '</select></label></span>';
    html += '<span class="availability-group form-group"> <label><span class="accesshide">' +
        M.util.get_string('label_completion', 'availability_yipl_duration') +
        ' </span> <select class="custom-select" name="duration" title="' + M.util.get_string('label_completion', 'availability_yipl_duration') + '"> <option value="1">minutes</option> <option value="2">hours</option> <option value="3">days</option> </select> </label> </span>';
    var node = Y.Node.create('<span class="form-inline">' + html + '</label></span>');

    // Set initial values.
    if (json.cm !== undefined &&
        node.one('select[name=cm] > option[value=' + json.cm + ']')) {
        node.one('select[name=cm]').set('value', '' + json.cm);
    }
    if (json.e !== undefined) {
        node.one('select[name=e]').set('value', '' + json.e);
    }
    if (json.e !== undefined) {
        node.one('select[name=duration]').set('value', '' + json.duration);
    }


    // Add event handlers (first time only).
    if (!M.availability_yipl_duration.form.addedEvents) {
        M.availability_yipl_duration.form.addedEvents = true;
        var root = Y.one('.availability-field');
        root.delegate('change', function () {
            // Whichever dropdown changed, just update the form.
            M.core_availability.form.update();
        }, '.availability_yipl_duration select');
    }

    return node;
};

M.availability_yipl_duration.form.fillValue = function (value, node) {
    value.cm = parseInt(node.one('select[name=cm]').get('value'), 10);
    value.e = parseInt(node.one('select[name=e]').get('value'), 10);
    value.duration = node.one('select[name=duration]').get('value');

};

M.availability_yipl_duration.form.fillErrors = function (errors, node) {
    var cmid = parseInt(node.one('select[name=cm]').get('value'), 10);
    if (cmid === 0) {
        errors.push('availability_yipl_duration:error_selectcmid');
    }
};


}, '@VERSION@', {"requires": ["base", "node", "event", "moodle-core_availability-form"]});
