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
     * @return  string
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
        global $DB, $CFG;
        require_once($CFG->dirroot . '/admin/tool/deletemessage/locallib.php');
        $configs = get_config('tool_deletemessage');
        $individualmessage = \core_message\api::MESSAGE_CONVERSATION_TYPE_INDIVIDUAL;// 1
        $delteaction = \core_message\api::MESSAGE_ACTION_DELETED;// 2
        $viewaction = \core_message\api::MESSAGE_ACTION_READ;// 1
        $users = null;

        $types = [$individualmessage];

        if ($configs->deletegroupmessages > 0) {
            $types[] = \core_message\api::MESSAGE_CONVERSATION_TYPE_GROUP;
        }
        if ($configs->deletepersonalmessage > 0) {
            $types[] = \core_message\api::MESSAGE_CONVERSATION_TYPE_SELF;
        }

        if ($configs->deletereadmessages > 0) {
            $reftime = time() - $configs->deletereadmessages;
            $sql = "SELECT distinct userid from {message_conversation_members}";
            $users = $DB->get_records_sql($sql);
            foreach ($users as $user) {
                $sql = "SELECT DISTINCT m.id as messageid,ua.userid FROM {message_conversations} c
                    JOIN {messages} m on m.conversationid =c.id
					and c.type in (:types) and m.timecreated<:timeref
                    JOIN {message_user_actions} ua on ua.messageid=m.id
					and ua.action=:actiontype and ua.timecreated<:timeref2 and ua.userid=:userref
					";

                $readmessagens = $DB->get_records_sql($sql, ['types' => $individualmessage,
                    'timeref' => $reftime, 'actiontype' => $viewaction,
                    'userref' => $user->userid, 'timeref2' => $reftime,
                ]);
                foreach ($readmessagens as $readmessage) {// Just soft delete if both has saw it will be hard deleted.
                    if (
                        $readmessage && isset($readmessage->useridfrom) &&
                        $DB->record_exists('user', ['id' => $readmessage->useridfrom]) &&
                        $DB->record_exists("messages", ['id' => $readmessage->messageid])
                    ) {
                        \core_message\api::delete_message($readmessage->useridfrom, $readmessage->messageid);
                    }
                }
            }
        }
        if ($configs->deleteallmessages > 0) {
            if (!$users) {
                $sql = "SELECT distinct userid from {message_conversation_members}";
                $users = $DB->get_records_sql($sql);
            }

            foreach ($users as $user) {
                $reftime = time() - $configs->deleteallmessages;
                [$insql, $inparams] = $DB->get_in_or_equal($types, SQL_PARAMS_NAMED, 'typ');
                $sql = "SELECT DISTINCT m.id as messageid, m.useridfrom
        FROM {message_conversations} c
        JOIN {messages} m ON m.conversationid = c.id
        AND c.type $insql
        AND m.timecreated < :timeref
        AND m.useridfrom = :userref
        LEFT JOIN {message_user_actions} uad ON uad.messageid = m.id";
                $params = array_merge($inparams, [
                    'timeref' => $reftime,
                    'userref' => $user->userid,
                ]);
                $readmessages = $DB->get_records_sql($sql, $params);
                foreach ($readmessages as $readmessage) {
                    if ($configs->harddelete) {// If is old it need to be deleted.
                        hard_delete_message($readmessage->messageid);
                    } else if (
                        $readmessage->useridfrom &&
                        $DB->record_exists('user', ['id' => $readmessage->useridfrom]) &&
                        $DB->record_exists("messages", ['id' => $readmessage->messageid])
                    ) {
                        \core_message\api::delete_message($readmessage->useridfrom, $readmessage->messageid);
                    }
                }
            }
        }
        if ($configs->cleanmessage) {
            $sql = "SELECT c.id,count(ua.id) as user_d,count(DISTINCT m.id) as mensagens FROM {message_conversations} c
                    JOIN {messages} m on m.conversationid =c.id
                    join {message_conversation_members} cm on cm.conversationid = c.id
                    JOIN {message_user_actions} ua on ua.messageid=m.id
					and ua.action=:deleteaction
                    GROUP BY c.id
                    HAVING count(DISTINCT ua.userid) >=count(distinct cm.userid)
                        and count(DISTINCT ua.messageid) >= count(DISTINCT m.id)
                    UNION
                    SELECT c.id,0 as user_d,count(DISTINCT m.id) as mensagens FROM {message_conversations} c
                    LEFT JOIN {messages} m on m.conversationid =c.id
                    WHERE m.id is null and c.itemtype is null
                    GROUP BY c.id
                    HAVING count(m.id) = 0
                    ";
            $deletedconversation = $DB->get_records_sql($sql, ['deleteaction' => $delteaction]);
            foreach ($deletedconversation as $conversation) {
                \core_message\api::delete_all_conversation_data($conversation->id);
            }
        }
    }
}
