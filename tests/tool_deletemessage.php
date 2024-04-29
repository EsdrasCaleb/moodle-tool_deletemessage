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
 * Delete Task.
 *
 * @package   tool_deletemessage
 * @author    Esdras Caleb
 * @copyright  2023 Esdras Caleb
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_deletemessage;


/**
 * Class test if this plugin is deleting things it should not delete
 * @author    Esdras Caleb
 * @copyright  2023 Esdras Caleb
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_deletemessage_test extends \advanced_testcase {

    /**
     * Make message to tests
     * @return int message id
     */
    private function make_message() {
        $userfrom = $this->getDataGenerator()->create_user();
        $userto = $this->getDataGenerator()->create_user();

        // Message text.
        $messagetext = "hello";

        // Create message object.
        $message = new \core\message\message();
        $message->component = 'core';
        $message->name = 'instantmessage';
        $message->userfrom = $userfrom;
        $message->userto = $userto;
        $message->subject = '';
        $message->fullmessage = $messagetext;
        $message->fullmessageformat = FORMAT_PLAIN;
        $message->fullmessagehtml = $messagetext;
        $message->smallmessage = $messagetext;

        return message_send($message);
    }

    /**
     * Test if the hard delection function works
     * @return void
     */
    public function test_deleting() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/admin/tool/deletemessage/locallib.php');

        $messageid = $this->make_message();
        $this->assertNotEmpty($DB->get_records('message', ['id' => $messageid]));
        hard_delete_message($messageid);
        $this->assertEmpty($DB->get_records('message', ['id' => $messageid]));
    }

    /**
     * Test taks of delection to not delete all messages
     * @return void
     */
    public function test_taks_isnotdeleting() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/admin/tool/deletemessage/locallib.php');

        $messageid = $this->make_message();

        $cron = new \tool_deletemessage\task\delete();
        $cron->execute();
        $this->assertNotEmpty($DB->get_records('message', ['id' => $messageid]));
        $this->assertNotEmpty($DB->get_records('message'));
    }
}
