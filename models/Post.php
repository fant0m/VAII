<?php namespace models;

class Post extends Model
{
    protected $attributes = ['type', 'privacy', 'title', 'text'];
    protected $fillable = ['type', 'privacy', 'title', 'text'];
    protected $visible = ['id', 'user_id', 'type', 'privacy', 'title', 'text', 'created_at', 'updated_at'];

    public function user() {
    	return $this->belongsTo('models\User');
    }

    public function icon() {
    	switch ($this->type) {
    		case 0:
    			$icon = 'globe';
    		break;
    		case 1:
    			$icon = 'film';
    		break;
    		case 2:
    			$icon = 'tv';
    		break;
    		case 3:
    			$icon = 'gamepad';
    		break;
    		case 4:
    			$icon = 'user';
    		break;
    		case 5:
    			$icon = 'calendar';
    		break;
    	}

    	return '<span class="fa fa-'.$icon.'"></span>';
    }
}