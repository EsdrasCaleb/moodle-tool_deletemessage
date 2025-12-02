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
 * @package core_message
 * @category test
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class tool_deletemessage_test extends \advanced_testcase {
    /** @var $messagesink message sink **/
    private $messagesink;

    /**
     * Test set up.
     *
     * This is executed before running any test in this file.
     */
    public function setUp(): void {
        parent::setUp();
        $this->preventResetByRollback(); // Messaging is not compatible with transactions.
        $this->messagesink = $this->redirectMessages();
        $this->resetAfterTest();
    }

    /**
     * Make message to tests
     * @return int message id
     */
    private function make_message(): int {
        global $DB;

        $userfrom = $this->getDataGenerator()->create_user();
        $userto = $this->getDataGenerator()->create_user();

        // Message text.
        $message = "hello";

        if (empty($time)) {
            $time = time();
        }

        if ($userfrom->id == $userto->id) {
            // It's a self conversation.
            $conversation = \core_message\api::get_self_conversation($userfrom->id);
            if (empty($conversation)) {
                $conversation = \core_message\api::create_conversation(
                    \core_message\api::MESSAGE_CONVERSATION_TYPE_SELF,
                    [$userfrom->id]
                );
            }
            $conversationid = $conversation->id;
        } else if (!$conversationid = \core_message\api::get_conversation_between_users([$userfrom->id, $userto->id])) {
            // It's an individual conversation between two different users.
            $conversation = \core_message\api::create_conversation(
                \core_message\api::MESSAGE_CONVERSATION_TYPE_INDIVIDUAL,
                [
                    $userfrom->id,
                    $userto->id,
                ]
            );
            $conversationid = $conversation->id;
        }

        // Ok, send the message.
        $record = new \stdClass();
        $record->useridfrom = $userfrom->id;
        $record->conversationid = $conversationid;
        $record->subject = 'No subject';
        $record->fullmessage = $message;
        $record->smallmessage = $message;
        $record->timecreated = $time;

        return $DB->insert_record('messages', $record);
    }

    /**
     * Test if the hard delection function works
     * @return void
     * @covers \hard_delete_message
     */
    public function test_deleting(): void {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/admin/tool/deletemessage/locallib.php');

        $messageid = $this->make_message();
        $this->assertNotEmpty($DB->get_records('messages', ['id' => $messageid]));
        hard_delete_message($messageid);
        $this->assertEmpty($DB->get_records('messages', ['id' => $messageid]));
    }

    /**
     * Test taks of delection to not delete all messages
     * @return void
     * @covers \tool_deletemessage\task\delete::execute
     */
    public function test_taks_isnotdeleting(): void {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/admin/tool/deletemessage/locallib.php');

        $messageid = $this->make_message();

        $cron = new \tool_deletemessage\task\delete();
        $cron->execute();
        $this->assertNotEmpty($DB->get_records('messages', ['id' => $messageid]));
        $this->assertNotEmpty($DB->get_records('messages'));
    }
}
