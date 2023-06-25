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
namespace tool_deletemessage\task;


use core\task\scheduled_task;
use core_message\api;
/**
 * Class delete to execute the task that deletes the messages from the users
 * @author    Esdras Caleb
 * @copyright  2023 Esdras Caleb
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delete extends scheduled_task {
    /**
     * Get the name of the task
     *
     * @return  void
     */
    public function get_name() {
        return get_string('taskname', 'tool_deletemessage');
    }

    /**
     * Execute the taks that delete the conversations that both users has deleted all messages in database
     *
     * @return  void
     */
    public function execute() {
        mtrace(get_string('taskname', 'tool_deletemessage'));
        global $DB;
        $configs = get_config('tool_deletemessage');
        $individualmessage = \core_message\api::MESSAGE_CONVERSATION_TYPE_INDIVIDUAL;// 1
        $delteaction = \core_message\api::MESSAGE_ACTION_DELETED;// 2
        $viewaction = \core_message\api::MESSAGE_ACTION_READ;// 1
        if ($configs->deletereadmessages > 0) {
            $reftime = time() - $configs->deletereadmessages;
            $sql = "SELECT m.id as messageid,ua.userid FROM {message_conversations} c
                    JOIN {messages} m on m.conversationid =c.id
					and c.type={$individualmessage} and m.timecreated<$reftime
                    JOIN {message_user_actions} ua on ua.messageid=m.id
					and ua.action={$viewaction} and au.timecreated<$reftime";
            $readmessagens = $DB->get_records_sql($sql);
            foreach ($readmessagens as $readmessage) {
                \core_message\api::delete_message($readmessage->messageid, $readmessage->userid);
            }
        }
        if ($configs->deleteallmessages > 0) {
            $reftime = time() - $configs->deleteallmessages;
            $sql = "SELECT m.id as messageid,m.useridfrom,m.useridto FROM {message_conversations} c
                    JOIN {messages} m on m.conversationid =c.id
					and c.type={$individualmessage} and m.timecreated<$reftime";
            $readmessagens = $DB->get_records_sql($sql);
            foreach ($readmessagens as $readmessage) {
                \core_message\api::delete_message($readmessage->messageid, $readmessage->useridfrom);
                \core_message\api::delete_message($readmessage->messageid, $readmessage->useridto);
            }
        }

        $sql = "SELECT c.id,count(ua.id) as usuarios_deletado,count(DISTINCT m.id) as mensagens FROM {message_conversations} c
                    JOIN {messages} m on m.conversationid =c.id
					and c.type={$individualmessage}
                    JOIN {message_user_actions} ua on ua.messageid=m.id
					and ua.action={$delteaction}
                    GROUP BY c.id
                    HAVING count(ua.id) >=2*count(DISTINCT m.id)";
        $deletedconversation = $DB->get_records_sql($sql);
        foreach ($deletedconversation as $conversation) {
            \core_message\api::delete_all_conversation_data($conversation->id);
        }
    }
}
