<?php
class Controller_Entities_Tagtypes extends Api_Rest_Controller {
	
	protected function create() {}
	
	protected function retrieve($id) {}
	
	protected function update($id) {}
	
	protected function delete($id) {}
	
	protected function catalog() {
		$isadmin = $this->convention->isManager($this->user);
		return array_map(function(Model_Event_Tag_Type $type){
			return $type->for_json();
		}, array_filter($this->convention->event_tag_types->find_all()->as_array(), function(Model_Event_Tag_Type $type) use ($isadmin){
			return $isadmin or $type->visible;
		}));
	}
	
}
