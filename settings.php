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
 * @copyright  2023 Esdras Caleb
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

global $CFG, $ADMIN;

if (is_siteadmin()) {
    if (!$ADMIN->locate('tool_deletemessage')) {
        $page = new admin_settingpage('tool_deletemessage', get_string('settingspage', 'tool_deletemessage'));
        $default = 0;
        $options = [
            0 => new lang_string('never'),
            2620800 => new lang_string('nummonths', 'moodle', 1),
            7862400 => new lang_string('nummonths', 'moodle', 3),
            15724800 => new lang_string('nummonths', 'moodle', 6),
            23587200 => new lang_string('nummonths', 'moodle', 9),
            31449600 => new lang_string('numyears', 'moodle', 1),
            47174400 => new lang_string('nummonths', 'moodle', 18),
            62899200 => new lang_string('numyears', 'moodle', 2),
        ];

        $page->add(new admin_setting_configselect('tool_deletemessage/deletereadmessages',
            get_string('deleteread', 'tool_deletemessage'),
            get_string('deleteread_desc', 'tool_deletemessage'), $default, $options));
        $page->add(new admin_setting_configselect('tool_deletemessage/deleteallmessages',
            get_string('deleteall', 'tool_deletemessage'),
            get_string('deleteall_desc', 'tool_deletemessage'), $default, $options));

        $page->add(new admin_setting_configcheckbox('tool_deletemessage/harddelete',
            get_string('harddelete', 'tool_deletemessage'),
            get_string('harddelete_desc', 'tool_deletemessage'), $default));

        $page->add(new admin_setting_configcheckbox('tool_deletemessage/cleanmessage',
            get_string('cleanmessage', 'tool_deletemessage'),
            get_string('cleanmessage_desc', 'tool_deletemessage'), $default));

        $page->add(new admin_setting_configcheckbox('tool_deletemessage/deletegroupmessages',
            get_string('deletegroupmessages', 'tool_deletemessage'),
            get_string('deletegroupmessages_desc', 'tool_deletemessage'), $default));

        $page->add(new admin_setting_configcheckbox('tool_deletemessage/deletepersonalmessage',
            get_string('deletepersonalmessage', 'tool_deletemessage'),
            get_string('deletepersonalmessage_desc', 'tool_deletemessage'), $default));

        $ADMIN->add('messaging', $page);

    }
}
