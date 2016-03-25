<?php
class Controller_Entities_Tagtypes extends Api_Rest_Controller {
	
	protected function create() {
		if (!$this->convention->isManager($this->user))
			throw new Api_Exception_Unauthorized($this, "Not authorized to delete tag types!");
		$data = $this->input();
		if (!$data->title)
			throw new Api_Exception_InvalidInput($this, "No title specified");
		$title = $data->title;
		$public = $data->fetch('public', true);
		$requirement = $data->fetch('requirement','1');
		$type = Model_Event_Tag_Type::generate($this->convention, $title, $requirement != '*', $requirement != '1');
		foreach ($data->fetch('values',[]) as $value)
			Model_Event_Tag_Value::generate($type, $value);
		return $type->for_json();
	}
	
	protected function retrieve($id) {
		$type = $this->convention->event_tag_types->where('title', '=', $id)->find();
		if ($type->loaded() and ($type->visible or $this->convention->isManager($this->user)))
			return $type->for_json();
		return false;
	}
	
	protected function update($id) {
		if (!$this->convention->isManager($this->user))
			throw new Api_Exception_Unauthorized($this, "Not authorized to delete tag types!");
		$type = $this->convention->event_tag_types->where('title', '=', $id)->find();
		if (!$type->loaded())
			throw new Api_Exception_InvalidInput($this,"Tag Type '$id' not found");
		$data = $this->input();
		// handle value replacement
		foreach ($data->fetch('replace-values', []) as $oldval => $newval) {
			$newvalO = Model_Event_Tag_Value::generate($type, $newval);
			try {
				$oldvalO = Model_Event_Tag_Value::byTitle($type, $oldval);
				if ($oldvalO->pk() == $newvalO->pk())
					throw new Api_Exception_InvalidInput($this, "Replacing the same value is not allowed (old: '$oldval' == new: '$newval')");
				foreach ($oldvalO->getEventTags() as $evt) {
					$evt->event_tag_value = $newvalO;
					$evt->save();
				}
				$oldvalO->delete(); // should be safe now
			} catch (Model_Exception_NotFound $e) {} // everything is fine, nothing to see here, move along now... move along...
		}
		// handle value removal
		foreach ($data->fetch('remove-values',[]) as $value) {
			try {
				$val = Model_Event_Tag_Value::byTitle($type, $value);
				try {
					$val->delete();
				} catch (Database_Exception $e) {
					throw new Api_Exception_InvalidInput($this, "Cannot remove value '$value' for tag type '$id' as existing events " .
							join(', ', array_map(function(Model_Event_Tag $evt){
								return $evt->event_id;
							}, $val->getEventTags()->as_array())) . " use it");
				}
			} catch (Model_Exception_NotFound $e) {} // everything is fine, nothing to see here, move along now... move along...
		}
		// handle value addition
		foreach ($data->fetch('values',[]) as $value)
			Model_Event_Tag_Value::generate($type, $value);
		// handle property change
		if ($data->title)
			$type->title = $data->title;
		if ($data->requirement)
			$type->requirement = $data->requirement;
		if ($data->isset('public'))
			$type->visible = $data->public;
		$type->save();
		return $type->for_json();
	}
	
	protected function delete($id) {
		if (!$this->convention->isManager($this->user))
			throw new Api_Exception_Unauthorized($this, "Not authorized to delete tag types!");
		$type = $this->convention->event_tag_types->where('title', '=', $id)->find();
		if (!$type->loaded())
			return true; // no need to delete, it is already gone
		try {
			$type->delete();
		} catch (Database_Exception $e) {
			if ($this->input()->force) {
				foreach ($type->getEventTags() as $evt) $evt->delete();
				$type->delete();
			} else {
				throw new Api_Exception_InvalidInput($this, "Cannot delete tag type '$id': existing events ".
					join(', ', array_map(function(Model_Event_Tag $evt){ return $evt->event->pk(); }, $type->getEventTags()->as_array())) .
					" use it");
			}
		}
		return true;
	}
	
	protected function catalog() {
		$isadmin = $this->convention->isManager($this->user);
		return ORM::result_for_json(array_filter($this->convention->event_tag_types->find_all()->as_array(),
				function(Model_Event_Tag_Type $type) use ($isadmin){
			return $isadmin or $type->visible;
		}));
	}
	
}
