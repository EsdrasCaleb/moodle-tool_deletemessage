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
 * Lang Package
 *
 * @package    tool_deletemessage
 * @author     Esdras Caleb <esdrascaleb@gmail.com>
 * @copyright  2023 Esdras Caleb
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Delete Messages';
$string['taskname'] = "Auto delete deleted messages";
$string['settingspage'] = 'Auto Delete Messages';
$string['deleteread'] = 'Delete read messages';
$string['deleteread_desc'] = 'Read messages can be deleted to save space. How long after a notification is read can it be deleted?';
$string['deleteall'] = 'Delete all messages';
$string['deleteall_desc'] = 'Read and unread messages can be deleted to save space. How long after a notification is created can it be deleted?';
$string['privacy:metadata'] = 'The local aws plugin does not store any personal data. However, it send the IP of a user that had an error to the sentry server configured in it.';
$string['harddelete'] = "Hard Delete Messages";
$string['harddelete_desc'] = "All Deleted messages by this plugin will be deleted from database";
$string['cleanmessage'] = "Clear Conversations";
$string['cleanmessage_desc'] = "When Both user delete all messages between them the messages will be erased from database";
$string['deletegroupmessages'] = "Delete Group Messages";
$string['deletegroupmessages_desc'] = "Include Group Messagens in filter to delete old and readed messages";
$string['deletepersonalmessage'] = "Delete Private Messages";
$string['deletepersonalmessage_desc'] = "Delete messages in the personal conversation, messages send by user to himself if they are in filter";
