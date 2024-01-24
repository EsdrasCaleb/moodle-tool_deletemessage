<?php

function hard_delete_message($id)
{
    global $DB;
    $DB->delete_records('message_user_actions', ["messageid"=>$id]);
    $DB->delete_records('messages', ["id"=>$id]);
    $DB->delete_records('message_email_messages',["id"=>$id]);

}