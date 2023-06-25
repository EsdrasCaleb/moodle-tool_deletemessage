<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Version information
 *
 * @package    tool_deletemessage
 * @author     Esdras Caleb <esdrascaleb@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

global $CFG,$ADMIN;

if (is_siteadmin()) {
    if (!$ADMIN->locate('tool_deletemessage')) {
        $page = new admin_settingpage('tool_deletemessage', get_string('settingspage', 'tool_deletemessage'));
        $default = 0;
        $options = array(
            0 => new lang_string('never'),
            DAYSECS => new lang_string('secondstotime86400'),
            WEEKSECS => new lang_string('secondstotime604800'),
            2620800 => new lang_string('nummonths', 'moodle', 1),
            7862400 => new lang_string('nummonths', 'moodle', 3),
            15724800 => new lang_string('nummonths', 'moodle', 6)
        );

        $page->add(new admin_setting_configselect('tool_deletemessage/deletereadmessages', get_string('deleteread', 'tool_deletemessage'),
            get_string('deleteread_desc', 'tool_deletemessage'),$default,$options));
        $page->add(new admin_setting_configselect('tool_deletemessage/deleteallmessages', get_string('deleteall', 'tool_deletemessage'),
            get_string('deleteall_desc', 'tool_deletemessage'),$default,$options));

        $ADMIN->add('messaging', $page);

    }
}