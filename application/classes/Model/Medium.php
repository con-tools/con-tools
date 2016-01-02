<?php

class Model_Medium extends ORM {
	
	protected $_table_name = 'media';
	
	protected $_belongs_to = [
			'event' => []
	];
	
	protected $_columns = [
			'id' => [],
			// foreign keys
			'event_id' => [],
			// data fields
			'filename' => [],
			// "content_type" is the valid MIME type for S3 stored files,
			// "application/x-<service>" for external references, such as application/x-youtube
			'content_type' => [],
			'url' => [],
			'thumbnail_url' => [],
	];
}
