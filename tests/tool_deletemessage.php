<?php

namespace tool_deletemessage;



class auth_ldap_test extends \advanced_testcase {
    public function test_deleting() {
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
        $this->assertNotEmpty($DB->get_records('message',['id'=>$messageid]));
        hard_delete_message($messageid);
        $this->assertEmpty($DB->get_records('message',['id'=>$messageid]));
    }

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
        $this->assertNotEmpty($DB->get_records('message',['id'=>$messageid]));
        $cron = new \tool_deletemessage\task\delete();
        $cron->execute();
        $this->assertNotEmpty($DB->get_records('message',['id'=>$messageid]));
    }
}