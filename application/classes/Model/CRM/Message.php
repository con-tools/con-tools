<?php

class Model_CRM_Message extends ORM {
	
	protected $_belongs_to = [
			'issue' => [],
			'sender' => [ 'model' => 'user', 'foreign_key' => 'sender_id' ],
			'message' => [ 'model' => 'crm_message', 'foreign_key' => 'in_reply_to' ]
	];
	
	protected $_columns = [
			'id' => [],
			// foreign keys
			'crm_issue_id' => [],
			'sender_id' => [],
			// data fields
			'subject' => [], // SMTP message subject, probably won't be displayed anywhere
			'text' => [],
			'in_reply_to' => [],
			'smtp_message_id' => [],
	];
	
};
