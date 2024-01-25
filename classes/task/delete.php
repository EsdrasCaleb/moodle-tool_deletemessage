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
        require_once($CFG->dirroot.'/admin/tool/deletemessage/locallib.php');
        $configs = get_config('tool_deletemessage');
        $individualmessage = \core_message\api::MESSAGE_CONVERSATION_TYPE_INDIVIDUAL;// 1
        $delteaction = \core_message\api::MESSAGE_ACTION_DELETED;// 2
        $viewaction = \core_message\api::MESSAGE_ACTION_READ;// 1
        $users = null;
        $whereaction = "WHERE uad.id is null";

        if ($configs->harddelete) {
            $whereaction = "";
        }
        if ($configs->deletereadmessages > 0) {
            $reftime = time() - $configs->deletereadmessages;
            $sql = "SELECT distinct userid from {message_conversation_members}";
            $users = $DB->get_records_sql($sql);
            foreach ($users as $user) {
                $sql = "SELECT m.id as messageid,ua.userid FROM {message_conversations} c
                    JOIN {messages} m on m.conversationid =c.id
					and c.type=? and m.timecreated<?
                    JOIN {message_user_actions} ua on ua.messageid=m.id
					and ua.action=? and ua.timecreated<? and ua.userid=?
					LEFT JOIN {message_user_actions} uad on uad.messageid=m.id and uad.action=?
					{$whereaction}";
                $readmessagens = $DB->get_records_sql($sql, [$individualmessage, $reftime, $viewaction, $reftime,
                    $user->userid, $delteaction, ]);
                foreach ($readmessagens as $readmessage) {
                    if ($configs->harddelete) {
                        \core_message\api::delete_message($readmessage->userid, $readmessage->messageid);
                    }
                }
            }
        }
        if ($configs->deleteallmessages > 0) {
            if (!$user) {
                $sql = "SELECT distinct userid from {message_conversation_members}";
                $users = $DB->get_records_sql($sql);
            }
            foreach ($users as $user) {
                $reftime = time() - $configs->deleteallmessages;
                $sql = "SELECT m.id as messageid,m.useridfrom FROM {message_conversations} c
                        JOIN {messages} m on m.conversationid =c.id
                        and c.type=? and m.timecreated<? and m.useridfrom=?
                        LEFT JOIN {message_user_actions} uad on uad.messageid=m.id and uad.action=?
					    {$whereaction}";
                $readmessagens = $DB->get_records_sql($sql, [$individualmessage, $reftime, $user->userid,
                    $delteaction, ]);
                foreach ($readmessagens as $readmessage) {
                    if ($configs->harddelete) {
                        hard_delete_message($readmessage->messageid);
                    } else {
                        \core_message\api::delete_message($readmessage->useridfrom, $readmessage->messageid);
                    }
                }
            }
        }

        $sql = "SELECT c.id,count(ua.id) as usuarios_deletado,count(DISTINCT m.id) as mensagens FROM {message_conversations} c
                    JOIN {messages} m on m.conversationid =c.id
					and c.type=?
                    JOIN {message_user_actions} ua on ua.messageid=m.id
					and ua.action=?
                    GROUP BY c.id
                    HAVING count(ua.id) >=2*count(DISTINCT m.id)";
        $deletedconversation = $DB->get_records_sql($sql, [$individualmessage, $delteaction]);
        foreach ($deletedconversation as $conversation) {
            \core_message\api::delete_all_conversation_data($conversation->id);
        }
    }
}
