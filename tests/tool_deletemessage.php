<?php
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
     * Test if the hard delection function works
     * @return void
     */
    public function test_deleting() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/admin/tool/deletemessage/locallib.php');
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

        // Send message.
        $messageid = message_send($message);
        $this->assertNotEmpty($DB->get_records('message', ['id' => $messageid]));
        hard_delete_message($messageid);
        $this->assertEmpty($DB->get_records('message', ['id' => $messageid]));
    }

    /**
     * Test taks of delection to not delete all messages
     * @return void
     */
    public function test_taks_isnotdeleting() {
        global $CFG,$DB;
        require_once($CFG->dirroot.'/admin/tool/deletemessage/locallib.php');
        $user_from = $this->getDataGenerator()->create_user();
        $user_to = $this->getDataGenerator()->create_user();
        
        // Message text
        $message_text = "hello";

        // Create message object
        $message = new \core\message\message();
        $message->component = 'core';
        $message->name = 'instantmessage';
        $message->userfrom = $user_from;
        $message->userto = $user_to;
        $message->subject = '';
        $message->fullmessage = $message_text;
        $message->fullmessageformat = FORMAT_PLAIN;
        $message->fullmessagehtml = $message_text;
        $message->smallmessage = $message_text;

        // Send message
        $messageid = message_send($message);
        $cron = new \tool_deletemessage\task\delete();
        $cron->execute();
        $this->assertNotEmpty($DB->get_records('message', ['id' => $messageid]));
        $this->assertNotEmpty($DB->get_records('message'));
    }
}
